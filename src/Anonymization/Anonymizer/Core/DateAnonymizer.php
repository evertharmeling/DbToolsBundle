<?php

declare(strict_types=1);

namespace MakinaCorpus\DbToolsBundle\Anonymization\Anonymizer\Core;

use MakinaCorpus\DbToolsBundle\Anonymization\Anonymizer\AbstractAnonymizer;
use MakinaCorpus\DbToolsBundle\Attribute\AsAnonymizer;
use MakinaCorpus\QueryBuilder\Query\Update;

#[AsAnonymizer(
    name: 'date',
    pack: 'core',
    description: <<<TXT
    Anonymize with a random float between two bounds.
    Options are 'min' and 'max'.
    TXT
)]
class DateAnonymizer extends AbstractAnonymizer
{
    /**
     * @inheritdoc
     */
    public function anonymize(Update $update): void
    {
        if (!($this->options->has('min') && $this->options->has('max'))) {
            throw new \InvalidArgumentException("You should provide 2 options (min and max) with this anonymizer");
        }

        $min = (new \DateTimeImmutable($this->options->get('min')))->getTimestamp();
        $max = (new \DateTimeImmutable($this->options->get('max')))->getTimestamp();

        $expr = $update->expression();

        $randomIntExpr = $this->getRandomIntExpression($max, $min);
        $update->set(
            $this->columnName,
            $this->getSetIfNotNullExpression(
                $expr->cast($randomIntExpr, 'date')
            ),
        );
    }
}
