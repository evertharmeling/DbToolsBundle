<?php

declare(strict_types=1);

namespace MakinaCorpus\DbToolsBundle\Tests\Functional\Anonymizer\Core;

use MakinaCorpus\DbToolsBundle\Anonymization\Anonymizator;
use MakinaCorpus\DbToolsBundle\Anonymization\Anonymizer\AnonymizerRegistry;
use MakinaCorpus\DbToolsBundle\Anonymization\Anonymizer\Options;
use MakinaCorpus\DbToolsBundle\Anonymization\Config\AnonymizationConfig;
use MakinaCorpus\DbToolsBundle\Anonymization\Config\AnonymizerConfig;
use MakinaCorpus\DbToolsBundle\Test\FunctionalTestCase;

class DateAnonymizerTest extends FunctionalTestCase
{
    /** @before */
    protected function createTestData(): void
    {
        $this->createOrReplaceTable(
            'table_test',
            [
                'id' => 'integer',
                'data' => 'date',
            ],
            [
                [
                    'id' => '1',
                    'data' => '2022-02-23',
                ],
                [
                    'id' => '2',
                    'data' => '2023-02-23',
                ],
                [
                    'id' => '3',
                    'data' => '2024-02-23',
                ],
                [
                    'id' => '4',
                ],
            ],
        );
    }

    public function testAnonymize(): void
    {
        $config = new AnonymizationConfig();
        $config->add(new AnonymizerConfig(
            'table_test',
            'data',
            'date',
            new Options([
                'min' => $min = '2022-02-20',
                'max' => $max = '2022-02-23',
            ])
        ));

        $anonymizator = new Anonymizator(
            $this->getConnection(),
            new AnonymizerRegistry(),
            $config
        );

        $this->assertSame(
            '2022-02-23',
            $this->getConnection()->executeQuery('select data from table_test where id = 1')->fetchOne(),
        );

        foreach ($anonymizator->anonymize() as $message) {
        }

        $datas = $this->getConnection()->executeQuery('select data from table_test order by id asc')->fetchFirstColumn();

        $min = new \DateTimeImmutable($min);
        $max = new \DateTimeImmutable($max);

        $data =$datas[0];
        $this->assertNotNull($data);
        $this->assertNotSame(\DateTimeImmutable::createFromFormat('Y-m-d!', '2022-02-23'), $data);
        $this->assertTrue($data >= $min && $data <= $max);

        $data = (float) $datas[1];
        $this->assertNotNull($data);
        $this->assertNotSame(\DateTimeImmutable::createFromFormat('Y-m-d!', '2023-02-23'), $data);
        $this->assertTrue($data >= $min && $data <= $max);

        $data = (float) $datas[2];
        $this->assertNotNull($data);
        $this->assertNotSame(\DateTimeImmutable::createFromFormat('Y-m-d!', '2024-02-23'), $data);
        $this->assertTrue($data >= $min && $data <= $max);

        $this->assertNull($datas[3]);

        $this->assertCount(4, \array_unique($datas), 'All generated values are different.');
    }
}
