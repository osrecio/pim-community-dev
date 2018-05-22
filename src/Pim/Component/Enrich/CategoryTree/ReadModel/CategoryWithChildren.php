<?php

declare(strict_types=1);

namespace Pim\Component\Enrich\CategoryTree\ReadModel;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryWithChildren
{
    /** @var int */
    private $id;

    /** @var string */
    private $code;

    /** @var string */
    private $label;

    /** @var array */
    private $childrenCategoryCodes;

    /**
     * @param int    $id
     * @param string $code
     * @param string $label
     * @param array  $childrenCategoryCodes
     */
    public function __construct(int $id, string $code, string $label, array $childrenCategoryCodes)
    {
        $this->id = $id;
        $this->code = $code;
        $this->label = $label;
        $this->childrenCategoryCodes = $childrenCategoryCodes;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return array
     */
    public function getChildrenCategoryCodes(): array
    {
        return $this->childrenCategoryCodes;
    }
}
