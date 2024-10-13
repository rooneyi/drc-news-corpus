<?php

declare(strict_types=1);

namespace App\Aggregator\Domain\ValueObject;

use DateTime;
use Webmozart\Assert\Assert;

/**
 * Class DateRange.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
final readonly class DateRange implements \Stringable
{
    private function __construct(
        public int $start,
        public int $end
    ) {
        Assert::notEq($this->start, 0);
        Assert::notEq($this->end, 0);
        Assert::greaterThanEq($end, $start);
    }

    #[\Override]
    public function __toString(): string
    {
        return sprintf('%d:%d', $this->start, $this->end);
    }

    public static function from(string $interval, string $format = 'Y-m-d', string $separator = ':'): self
    {
        if ($separator === '') {
            throw new \InvalidArgumentException('Separator cannot be empty');
        }

        [$startDate, $endDate] = explode($separator, $interval);

        /** @var DateTime $start */
        $start = DateTime::createFromFormat($format, $startDate);

        /** @var DateTime $end */
        $end = DateTime::createFromFormat($format, $endDate);

        return new self((int) $start->format('U'), (int) $end->format('U'));
    }

    public static function backward(\DateTimeImmutable $date, int $years = 100): self
    {
        $start = $date->modify(sprintf('-%d years', $years));
        $end = $date;

        return new self((int) $start->format('U'), (int) $end->format('U'));
    }

    public static function forward(\DateTimeImmutable $date, int $years = 100): self
    {
        $start = $date;
        $end = $date->modify(sprintf('+%d years', $years));

        return new self((int) $start->format('U'), (int) $end->format('U'));
    }

    public function format(string $format = 'Y-m-d'): string
    {
        return sprintf('%s:%s', date($format, $this->start), date($format, $this->end));
    }

    public function inRange(int $date): bool
    {
        return $date >= $this->start && $date <= $this->end;
    }
}
