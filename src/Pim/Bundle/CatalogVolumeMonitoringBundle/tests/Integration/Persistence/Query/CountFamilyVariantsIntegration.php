<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Integration\Persistence\Query;

use PHPUnit\Framework\Assert;
use Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Integration\Persistence\QueryTestCase;

class CountFamilyVariantsIntegration extends QueryTestCase
{
    public function testGetCountOfFamilyVariants()
    {
        $query = $this->get('pim_volume_monitoring.persistence.query.count_family_variants');
        $this->createFamilyWithVariant(4);
        $this->createFamilyWithVariant(2);

        $volume = $query->fetch();

        Assert::assertEquals(6, $volume->getVolume());
        Assert::assertEquals('count_family_variants', $volume->getVolumeName());
        Assert::assertEquals(false, $volume->hasWarning());
    }

    /**
     * @param int $numberOfFamilyVariants
     */
    private function createFamilyWithVariant(int $numberOfFamilyVariants): void
    {
        $axisAttribute = $this->createAttribute([
            'code'     => 'new_attribute_' . rand(),
            'type'     => 'pim_catalog_boolean',
            'group'    => 'other'
        ]);

        $family = $this->createFamily(['code' => 'new_family_' . rand()]);
        $family->addAttribute($axisAttribute);
        $errors = $this->get('validator')->validate($family);
        Assert::assertCount(0, $errors);

        $this->get('pim_catalog.saver.family')->save($family);

        $i = 0;
        while ($i < $numberOfFamilyVariants) {
            $this->createFamilyVariant([
                'code'     => 'new_family_variant_' . rand(),
                'variant_attribute_sets' => [
                    [
                        'axes' => [$axisAttribute->getCode()],
                        'attributes' => [],
                        'level'=> 1,
                    ]
                ],
                'family' => $family->getCode()
            ]);
            $i++;
        }
    }
}
