<?php

declare (strict_types=1);

namespace MakinaCorpus\DbToolsBundle\Stats;

use MakinaCorpus\DbToolsBundle\Helper\Format;

class StatValue
{
    const TAG_CODE = 'code';
    const TAG_INFO = 'info';
    const TAG_MAINTENANCE = 'maint';
    const TAG_READ = 'read';
    const TAG_WRITE = 'write';

    const UNIT_BOOL = 'bool';
    const UNIT_BYTE = 'byte';
    const UNIT_CODE = 'code';
    const UNIT_DATETIME = 'datetime';
    const UNIT_MSEC = 'msec';
    const UNIT_NAME = 'name';
    const UNIT_UNIT = 'unit';

    public function __construct(
        public string $name,
        public null|bool|float|int|string|\DateTimeImmutable $value,
        public string $unit = self::UNIT_UNIT,
        public array $tags = [],
        public ?string $description = null,
    ) {}

    public function unitToString(): ?string
    {
        return match ($this->unit) {
            self::UNIT_BOOL => 'yes/no',
            self::UNIT_BYTE => 'size',
            self::UNIT_CODE => 'code',
            self::UNIT_DATETIME => 'date',
            self::UNIT_MSEC => 'duration',
            self::UNIT_NAME => 'name',
            self::UNIT_UNIT => 'count',
            default => null,
        };
    }

    public function alignLeft(): bool
    {
        return match ($this->unit) {
            self::UNIT_NAME => true,
            self::UNIT_CODE => true,
            default => false,
        };
    }

    public function toString(): string
    {
        if (null === $this->value) {
            return '';
        }

        if ($this->value instanceof \DateTimeInterface) {
            return $this->value->format('Y:m:d H:i:s');
        }

        return match ($this->unit) {
            self::UNIT_BOOL => $this->value ? "Yes" : "No",
            self::UNIT_BYTE => Format::memory($this->value),
            self::UNIT_MSEC => Format::time($this->value),
            self::UNIT_UNIT => \number_format($this->value, 0, ',', ' '),
            default => (string) $this->value,
        };
    }
}
