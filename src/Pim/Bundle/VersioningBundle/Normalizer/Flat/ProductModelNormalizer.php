<?php

declare(strict_types=1);

namespace Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Pim\Component\Catalog\Model\AssociationInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;

/**
 * A normalizer to transform a product model entity into a flat array
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelNormalizer extends SerializerAwareNormalizer implements NormalizerInterface
{
    /** @staticvar string */
    private const FIELD_CATEGORY = 'categories';

    /** @staticvar string */
    private const FIELD_FAMILY_VARIANT = 'family_variant';

    /** @staticvar string */
    private const FIELD_CODE = 'code';

    private const FIELD_PARENT = 'parent';

    /** @staticvar string */
    private const ITEM_SEPARATOR = ',';

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array()): array
    {
        $context = $this->resolveContext($context);

        $results = [];
        $familyVariant = $object->getFamilyVariant();
        $results[self::FIELD_FAMILY_VARIANT] = null === $familyVariant ? null : $familyVariant->getCode();
        $results[self::FIELD_CODE] = $object->getCode();
        $results[self::FIELD_CATEGORY] = implode(self::ITEM_SEPARATOR, $object->getCategoryCodes());
        $results[self::FIELD_PARENT] = $this->normalizeParent($object->getParent());
        $results = array_merge($results, $this->normalizeAssociations($object->getAssociations()));
        $results = array_replace($results, $this->normalizeValues($object, $format, $context));

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ProductModelInterface && in_array($format, ['flat']);
    }

    /**
     * Normalizes a product parent.
     *
     * @param ProductModelInterface $parent
     *
     * @return string
     */
    private function normalizeParent(ProductModelInterface $parent = null): string
    {
        return $parent ? $parent->getCode() : '';
    }

    /**
     * Normalize values
     *
     * @param ProductModelInterface $productModel
     * @param string|null      $format
     * @param array            $context
     *
     * @return array
     */
    private function normalizeValues(ProductModelInterface $productModel, $format = null, array $context = []): array
    {
        $values = $productModel->getValuesForVariation();

        $normalizedValues = [];
        foreach ($values as $value) {
            $normalizedValues = array_replace(
                $normalizedValues,
                $this->serializer->normalize($value, $format, $context)
            );
        }
        ksort($normalizedValues);

        return $normalizedValues;
    }

    /**
     * Merge default format option with context
     *
     * @param array $context
     *
     * @return array
     */
    private function resolveContext(array $context): array
    {
        return array_merge(
            [
                'scopeCode'     => null,
                'localeCodes'   => [],
                'metric_format' => 'multiple_fields',
                'filter_types'  => ['pim.transform.product_value.flat']
            ],
            $context
        );
    }

    /**
     * Normalize associations
     *
     * @param AssociationInterface[] $associations
     *
     * @return array
     */
    protected function normalizeAssociations($associations = [])
    {
        $results = [];
        foreach ($associations as $association) {
            $columnPrefix = $association->getAssociationType()->getCode();

            $groups = [];
            foreach ($association->getGroups() as $group) {
                $groups[] = $group->getCode();
            }

            $products = [];
            foreach ($association->getProducts() as $product) {
                $products[] = $product->getIdentifier();
            }

            $productModels = [];
            foreach ($association->getProductModels() as $productModel) {
                $productModels[] = $productModel->getCode();
            }

            $results[$columnPrefix . '-groups'] = implode(',', $groups);
            $results[$columnPrefix . '-products'] = implode(',', $products);
            $results[$columnPrefix . '-product_models'] = implode(',', $productModels);
        }

        return $results;
    }
}
