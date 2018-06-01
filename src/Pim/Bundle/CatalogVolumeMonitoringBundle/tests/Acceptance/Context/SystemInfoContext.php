<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Acceptance\Context;

use Behat\Behat\Context\Context;
use Pim\Bundle\AnalyticsBundle\DataCollector\DBDataCollector;
use Webmozart\Assert\Assert;

final class SystemInfoContext implements Context
{
    /** @var array */
    private $collector = [];

    /** @var DBDataCollector */
    private $dbDataCollector;

    /**
     * @param DBDataCollector $dbDataCollector
     */
    public function __construct(DBDataCollector $dbDataCollector)
    {
        $this->dbDataCollector = $dbDataCollector;
    }

    /**
     * @When the administrator user asks for the system information
     */
    public function theAdministratorUserAsksForTheSystemInformation(): void
    {
        $this->collector = $this->dbDataCollector->collect();
    }

    /**
     * @Then the system information returns that the number of channels is :numberOfChannels
     *
     * @param int $numberOfChannels
     */
    public function theSystemInformationReturnsThatTheNumberOfChannelsIs(int $numberOfChannels): void
    {
        $collectedInfo = $this->getCollectedInformation();

        Assert::eq($numberOfChannels, $collectedInfo['nb_channels']);
    }

    /**
     * @Then the system information returns that the number of locales is :numberOfLocales
     *
     * @param int $numberOfLocales
     */
    public function theSystemInformationReturnsThatTheNumberOfLocalesIs(int $numberOfLocales): void
    {
        $collectedInfo = $this->getCollectedInformation();

        Assert::eq($numberOfLocales, $collectedInfo['nb_locales']);
    }

    /**
     * @Then the system information returns that the number of products is :numberOfProducts
     *
     * @param int $numberOfProducts
     */
    public function theSystemInformationReturnsThatTheNumberOfProductsIs(int $numberOfProducts): void
    {
        $collectedInfo = $this->getCollectedInformation();

        Assert::eq($numberOfProducts, $collectedInfo['nb_products']);
    }

    /**
     * @Then the system information returns that the number of product models is :numberOfProductModes
     *
     * @param int $numberOfProductModels
     */
    public function theSystemInformationReturnsThatTheNumberOfProductModesIs(int $numberOfProductModels): void
    {
        $collectedInfo = $this->getCollectedInformation();

        Assert::eq($numberOfProductModels, $collectedInfo['nb_product_models']);
    }

    /**
     * @Then the system information returns that the number of variant products is :numberOfVariantProducts
     *
     * @param int $numberOfVariantProducts
     */
    public function theSystemInformationReturnsThatTheNumberOfVariantProductsIs(int $numberOfVariantProducts): void
    {
        $collectedInfo = $this->getCollectedInformation();

        Assert::eq($numberOfVariantProducts, $collectedInfo['nb_variant_products']);
    }

    /**
     * @Then the system information returns that the number of family variants is :numberOfFamilyVariants
     *
     * @param int $numberOfFamilyVariants
     */
    public function theSystemInformationReturnsThatTheNumberOfFamilyVariantsIs(int $numberOfFamilyVariants): void
    {
        $collectedInfo = $this->getCollectedInformation();

        Assert::eq($numberOfFamilyVariants, $collectedInfo['nb_family_variants']);
    }

    /**
     * @Then the system information returns that the number of families is :numberOfFamilies
     *
     * @param int $numberOfFamilies
     */
    public function theSystemInformationReturnsThatTheNumberOfFamiliesIs(int $numberOfFamilies): void
    {
        $collectedInfo = $this->getCollectedInformation();

        Assert::eq($numberOfFamilies, $collectedInfo['nb_families']);
    }

    /**
     * @Then the system information returns that the number of users is :numberOfUsers
     *
     * @param int $numberOfUsers
     */
    public function theSystemInformationReturnsThatTheNumberOfUsersIs(int $numberOfUsers): void
    {
        $collectedInfo = $this->getCollectedInformation();

        Assert::eq($numberOfUsers, $collectedInfo['nb_users']);
    }

    /**
     * @Then the system information returns that the number of categories is :numberOfCategories
     *
     * @param int $numberOfCategories
     */
    public function theSystemInformationReturnsThatTheNumberOfCategoriesIs(int $numberOfCategories): void
    {
        $collectedInfo = $this->getCollectedInformation();

        Assert::eq($numberOfCategories, $collectedInfo['nb_categories']);
    }

    /**
     * @Then the system information returns that the number of category trees is :numberOfCategoryTrees
     *
     * @param int $numberOfCategoryTrees
     */
    public function theSystemInformationReturnsThatTheNumberOfCategoryTreesIs(int $numberOfCategoryTrees): void
    {
        $collectedInfo = $this->getCollectedInformation();

        Assert::eq($numberOfCategoryTrees, $collectedInfo['nb_category_trees']);
    }

    /**
     * @Then the system information returns that the maximum of category in one category is :maxCategoryInOneCategory
     *
     * @param int $maxCategoryInOneCategory
     */
    public function theSystemInformationReturnsThatTheMaxOfCategoryInOneCategoryIs(int $maxCategoryInOneCategory): void
    {
        $collectedInfo = $this->getCollectedInformation();

        Assert::eq($maxCategoryInOneCategory, $collectedInfo['max_category_in_one_category']);
    }

    /**
     * @Then the system information returns that the maximum of category levels is :maxCategoryLevels
     *
     * @param int $maxCategoryLevels
     */
    public function theSystemInformationReturnsThatTheMaxOfCategoryLevelsIs(int $maxCategoryLevels): void
    {
        $collectedInfo = $this->getCollectedInformation();

        Assert::eq($maxCategoryLevels, $collectedInfo['max_category_levels']);
    }

    /**
     * @Then the system information returns that the number of product values is :numberOfProductValues
     *
     * @param int $numberOfProductValues
     */
    public function theSystemInformationReturnsThatTheNumberOfProductValuesIs(int $numberOfProductValues): void
    {
        $collectedInfo = $this->getCollectedInformation();

        Assert::eq($numberOfProductValues, $collectedInfo['nb_product_values']);
    }

    /**
     * @Then the system information returns that the average of product values by product is :numberOfProductValues
     *
     * @param int $avgOfProductValuesByProduct
     */
    public function theSystemInformationReturnsThatTheAverageOfProductValuesByProductIs(int $avgOfProductValuesByProduct): void
    {
        $collectedInfo = $this->getCollectedInformation();

        Assert::eq($avgOfProductValuesByProduct, $collectedInfo['avg_product_values_by_product']);
    }

    /**
     * @return array
     */
    public function getCollectedInformation(): array
    {
        return $this->collector;
    }
}
