<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\EnrichBundle\Event\CategoryEvents;
use Pim\Bundle\EnrichBundle\Flash\Message;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Enrich\CategoryTree\ListCategories;
use Pim\Component\Enrich\CategoryTree\ListChildrenCategoriesParameters;
use Pim\Component\Enrich\CategoryTree\ListRootCategoriesParameters;
use Pim\Component\Enrich\CategoryTree\Normalizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Category Tree Controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryTreeController extends Controller
{
    /** @staticvar string */
    const CONTEXT_MANAGE = 'manage';

    /** @staticvar string */
    const CONTEXT_VIEW = 'view';

    /** @staticvar string */
    const CONTEXT_ASSOCIATE = 'associate';

    /** @var ListCategories */
    protected $listCategories;

    /** @var Normalizer\RootCategory */
    protected $rootCategoryNormalizer;

    /** @var Normalizer\CategoryWithChildren */
    protected $categoryWithChildrenNormalizer;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var UserContext */
    protected $userContext;

    /** @var SaverInterface */
    protected $categorySaver;

    /** @var RemoverInterface */
    protected $categoryRemover;

    /** @var SimpleFactoryInterface */
    protected $categoryFactory;

    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /** @var array */
    protected $rawConfiguration;

    /** @var SecurityFacade */
    protected $securityFacade;

    /**
     * Constructor
     *
     * @param ListCategories                  $listCategories
     * @param Normalizer\RootCategory         $rootcategoryNormalizer
     * @param Normalizer\CategoryWithChildren $categoryWithChildrenNormalizer
     * @param EventDispatcherInterface        $eventDispatcher
     * @param UserContext                     $userContext
     * @param SaverInterface                  $categorySaver
     * @param RemoverInterface                $categoryRemover
     * @param SimpleFactoryInterface          $categoryFactory
     * @param CategoryRepositoryInterface     $categoryRepository
     * @param SecurityFacade                  $securityFacade
     * @param array                           $rawConfiguration
     */
    public function __construct(
        ListCategories $listCategories,
        Normalizer\RootCategory $rootCategoryNormalizer,
        Normalizer\CategoryWithChildren $categoryWithChildrenNormalizer,
        EventDispatcherInterface $eventDispatcher,
        UserContext $userContext,
        SaverInterface $categorySaver,
        RemoverInterface $categoryRemover,
        SimpleFactoryInterface $categoryFactory,
        CategoryRepositoryInterface $categoryRepository,
        SecurityFacade $securityFacade,
        array $rawConfiguration
    ) {
        $this->listCategories = $listCategories;
        $this->rootCategoryNormalizer = $rootCategoryNormalizer;
        $this->categoryWithChildrenNormalizer = $categoryWithChildrenNormalizer;
        $this->eventDispatcher = $eventDispatcher;
        $this->userContext = $userContext;
        $this->categorySaver = $categorySaver;
        $this->categoryRemover = $categoryRemover;
        $this->categoryFactory = $categoryFactory;
        $this->categoryRepository = $categoryRepository;
        $this->securityFacade = $securityFacade;

        $resolver = new OptionsResolver();
        $this->configure($resolver);

        $this->rawConfiguration = $resolver->resolve($rawConfiguration);
    }

    /**
     * The select_node_id request parameter
     * allows to send back the tree where the node belongs with a selected  attribute
     *
     * @param Request $request
     *
     * @throws AccessDeniedException
     *
     * @return array
     */
    public function listTreeAction(Request $request)
    {
        if (false === $this->securityFacade->isGranted($this->buildAclName('category_list'))) {
            throw new AccessDeniedException();
        }

        $parameters = new ListRootCategoriesParameters(
            $request->query->getInt('select_node_id', -1),
            $request->query->getBoolean('with_items_count', true),
            $request->query->getBoolean('include_sub', false)
        );
        $rootCategories = $this->listCategories->listRootCategories($parameters);
        $normalizedData = $this->rootCategoryNormalizer->normalizeList($rootCategories);

        return new JsonResponse($normalizedData);
    }

    /**
     * Move a node
     *
     * @param Request $request
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function moveNodeAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        if (false === $this->securityFacade->isGranted($this->buildAclName('category_edit'))) {
            throw new AccessDeniedException();
        }

        $category = $this->findCategory($request->get('id'));
        $parent = $this->findCategory($request->get('parent'));
        $category->setParent($parent);

        $prevSiblingId = $request->get('prev_sibling');
        $prevSibling = null;

        if (!empty($prevSiblingId)) {
            $prevSibling = $this->categoryRepository->find($prevSiblingId);
        }

        if (is_object($prevSibling)) {
            $this->categoryRepository->persistAsNextSiblingOf($category, $prevSibling);
        } else {
            $this->categoryRepository->persistAsFirstChildOf($category, $parent);
        }

        $this->categorySaver->save($category, ['flush' => true]);

        return new JsonResponse(['status' => 1]);
    }

    /**
     * List children of a category.
     * The parent category is provided via its id ('id' request parameter).
     * The node category to select is given by 'select_node_id' request parameter.
     *
     * If the node to select is not a direct child of the parent category, the tree
     * is expanded until the selected node is found amongs the children
     *
     * @param Request $request
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function childrenAction(Request $request)
    {
        if (false === $this->securityFacade->isGranted($this->buildAclName('category_list'))) {
            throw new AccessDeniedException();
        }
        $context = $request->query->get('context');

        if (self::CONTEXT_VIEW === $context || self::CONTEXT_MANAGE === $context) {
            $parameters = new ListChildrenCategoriesParameters(
                $request->query->getInt('id', -1),
                $request->query->getInt('select_node_id', -1),
                $request->query->getBoolean('with_items_count', true),
                $request->query->getBoolean('include_sub', false)
            );

            $categories = $this->listCategories->listChildrenCategories($parameters);
            $normalizedData = $this->categoryWithChildrenNormalizer->normalizeList($categories);

            return new JsonResponse($normalizedData);
        }

        // TODO: do a query for that
        if (self::CONTEXT_MANAGE === $context) {
            return new JsonResponse([]);
        }

        // TODO: do a query for that
        if (self::CONTEXT_ASSOCIATE === $context && true === $request->query->getBoolean('include_parent')) {
            return new JsonResponse([]);
        }

        // TODO: do another query for that
        if (self::CONTEXT_ASSOCIATE === $context && false === $request->query->getBoolean('include_parent')) {
            return new JsonResponse([]);
        }

        //try {
        //    $parent = $this->findCategory($request->get('id'));
        //} catch (\Exception $e) {
        //    $parent = $this->userContext->getUserProductCategoryTree();
        //}
        //
        //$selectNodeId = $request->get('select_node_id', -1);
        //
        //try {
        //    $selectNode = $this->findCategory($selectNodeId);
        //
        //    if (!$this->categoryRepository->isAncestor($parent, $selectNode)) {
        //        $selectNode = null;
        //    }
        //} catch (NotFoundHttpException $e) {
        //    $selectNode = null;
        //}
        //
        //$categories = $this->getChildrenCategories($request, $selectNode, $parent);
        //
        //if (null === $selectNode) {
        //    $view = 'PimEnrichBundle:CategoryTree:children.json.twig';
        //} else {
        //    $view = 'PimEnrichBundle:CategoryTree:children-tree.json.twig';
        //}
        //
        //$withItemsCount = (bool) $request->get('with_items_count', false);
        //$includeParent = $request->query->getBoolean('include_parent', false);
        //$includeSub = (bool) $request->get('include_sub', false);
        //
        //return $this->render(
        //    $view,
        //    [
        //        'categories'     => $categories,
        //        'parent'         => ($includeParent) ? $parent : null,
        //        'include_sub'    => $includeSub,
        //        'item_count'     => $withItemsCount,
        //        'select_node'    => $selectNode,
        //        'related_entity' => $this->rawConfiguration['related_entity']
        //    ],
        //    new JsonResponse()
        //);
    }

    /**
     * @Template()
     *
     * @throws AccessDeniedException
     *
     * @return array
     */
    public function indexAction()
    {
        if (false === $this->securityFacade->isGranted($this->buildAclName('category_list'))) {
            throw new AccessDeniedException();
        }

        return [
            'related_entity' => $this->rawConfiguration['related_entity'],
            'route'          => $this->rawConfiguration['route'],
            'acl'            => $this->rawConfiguration['acl'],
        ];
    }

    /**
     * Create a tree or category
     *
     * @param Request $request
     * @param int     $parent
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function createAction(Request $request, $parent = null)
    {
        if (false === $this->securityFacade->isGranted($this->buildAclName('category_create'))) {
            throw new AccessDeniedException();
        }

        $category = $this->categoryFactory->create();
        if ($parent) {
            $parent = $this->findCategory($parent);
            $category->setParent($parent);
        }

        $category->setCode($request->get('label'));
        $this->eventDispatcher->dispatch(CategoryEvents::PRE_CREATE, new GenericEvent($category));
        $form = $this->createForm($this->rawConfiguration['form_type'], $category, $this->getFormOptions($category));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->categorySaver->save($category);
                $message = new Message(sprintf('flash.%s.created', $category->getParent() ? 'category' : 'tree'));
                $this->addFlash('success', $message);
                $this->eventDispatcher->dispatch(CategoryEvents::POST_CREATE, new GenericEvent($category));

                return new JsonResponse(
                    [
                        'route'  => $this->buildRouteName('categorytree_edit'),
                        'params' => ['id' => $category->getId()]
                    ]
                );
            }
        }

        return $this->render(
            sprintf('PimEnrichBundle:CategoryTree:%s.html.twig', $request->get('content', 'edit')),
            [
                'form'           => $form->createView(),
                'related_entity' => $this->rawConfiguration['related_entity'],
                'acl'            => $this->rawConfiguration['acl'],
                'route'          => $this->rawConfiguration['route'],
            ]
        );
    }

    /**
     * Edit tree action
     *
     * @param Request $request
     * @param int     $id
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function editAction(Request $request, $id)
    {
        if (false === $this->securityFacade->isGranted($this->buildAclName('category_edit'))) {
            throw new AccessDeniedException();
        }

        $category = $this->findCategory($id);
        $this->eventDispatcher->dispatch(CategoryEvents::PRE_EDIT, new GenericEvent($category));
        $form = $this->createForm($this->rawConfiguration['form_type'], $category, $this->getFormOptions($category));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->categorySaver->save($category);
                $message = new Message(sprintf('flash.%s.updated', $category->getParent() ? 'category' : 'tree'));
                $this->addFlash('success', $message);
                $this->eventDispatcher->dispatch(CategoryEvents::POST_EDIT, new GenericEvent($category));
            }
        }

        return $this->render(
            sprintf('PimEnrichBundle:CategoryTree:%s.html.twig', $request->get('content', 'edit')),
            [
                'form'           => $form->createView(),
                'related_entity' => $this->rawConfiguration['related_entity'],
                'acl'            => $this->rawConfiguration['acl'],
                'route'          => $this->rawConfiguration['route'],
            ]
        );
    }

    /**
     * Remove category tree
     *
     * @param int $id
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function removeAction(Request $request, $id)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        if (false === $this->securityFacade->isGranted($this->buildAclName('category_remove'))) {
            throw new AccessDeniedException();
        }

        $category = $this->findCategory($id);
        $parent = $category->getParent();
        $params = (null !== $parent) ? ['node' => $parent->getId()] : [];

        $this->categoryRemover->remove($category);

        return new Response('', 204);
    }

    /**
     * Find a category from its id
     *
     * @param int $categoryId
     *
     * @throws NotFoundHttpException
     *
     * @return CategoryInterface
     */
    protected function findCategory($categoryId)
    {
        $category = $this->categoryRepository->find($categoryId);

        if (!$category) {
            throw $this->createNotFoundException('Category not found');
        }

        return $category;
    }

    /**
     * Gets the options for the form
     *
     * @param CategoryInterface $category
     *
     * @return array
     */
    protected function getFormOptions(CategoryInterface $category)
    {
        return [];
    }

    /**
     * @param Request                $request
     * @param CategoryInterface|null $selectNode
     *
     * @return array|ArrayCollection
     */
    protected function getChildrenCategories(Request $request, $selectNode, $parent)
    {
        if (null !== $selectNode) {
            $categories = $this->categoryRepository->getChildrenTreeByParentId($parent->getId(), $selectNode->getId());
        } else {
            $categories = $this->categoryRepository->getChildrenByParentId($parent->getId());
        }

        return $categories;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function buildAclName($name)
    {
        return $this->rawConfiguration['acl'] . '_' . $name;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function buildRouteName($name)
    {
        return $this->rawConfiguration['route'] . '_' . $name;
    }

    /**
     * @param OptionsResolver $resolver
     */
    protected function configure(OptionsResolver $resolver)
    {
        $resolver->setRequired(['related_entity', 'form_type', 'acl', 'route']);
    }
}
