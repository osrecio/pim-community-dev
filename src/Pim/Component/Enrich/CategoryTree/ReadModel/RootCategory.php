<?php

declare(strict_types=1);

namespace Pim\Component\Enrich\CategoryTree\ReadModel;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RootCategory
{
    /** @var integer */
    private $id;

    /** @var string */
    private $code;

    /** @var string */
    private $label;

    /** @var int */
    private $numberProductsInCategory;

    /** @var bool */
    private $selected;

    /**
     * @param integer $id
     * @param string  $code
     * @param string  $label
     * @param integer $numberProductsInCategory
     * @param bool    $selected
     */
    public function __construct(
        int $id,
        string $code,
        string $label,
        int $numberProductsInCategory,
        bool $selected
    ) {
        $this->id = $id;
        $this->code = $code;
        $this->label = $label;
        $this->numberProductsInCategory = $numberProductsInCategory;
        $this->selected = $selected;
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
     * @return int
     */
    public function getNumberProductsInCategory(): int
    {
        return $this->numberProductsInCategory;
    }

    /**
     * @return bool
     */
    public function isSelected(): bool
    {
        return $this->selected;
    }
}
