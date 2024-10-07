<?php

declare(strict_types=1);

namespace App\Aggregator\Infrastructure\Crawler\Source\Data;

use App\Aggregator\Domain\ValueObject\DateRange;
use App\Aggregator\Domain\ValueObject\FetchConfig;
use App\Aggregator\Domain\ValueObject\PageRange;
use App\Aggregator\Infrastructure\Crawler\Source\Source;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class Politique7sur7Service.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
final class SeptSurSeptCd extends Source
{
    public const string URL = 'https://7sur7.cd';

    public const string ID = '7sur7.cd';

    private string $category;

    /**
     * @throws \Throwable
     */
    #[\Override]
    public function fetch(FetchConfig $config): void
    {
        $this->initialize($config->filename);
        $this->category = $config->category ?? 'politique';
        $page = $config->page ?? PageRange::from(
            sprintf('0:%d', $this->getLastPage(self::URL . "/index.php/category/{$this->category}"))
        );

        for ($i = $page->start; $i < $page->end; $i++) {
            try {
                $crawler = $this->crawle(self::URL . "/index.php/category/{$this->category}?page={$i}", $i);
                $articles = $crawler->filter('.view-content')->children('.row.views-row');
            } catch (\Throwable) {
                continue;
            }

            $articles->each(fn (Crawler $node) => $this->fetchOne($node->html(), $config->date));
        }

        $this->completed();
    }

    /**
     * @throws \Throwable
     */
    #[\Override]
    public function fetchOne(string $html, ?DateRange $interval = null): void
    {
        $node = new Crawler($html);

        try {
            $date = $node->filter('.views-field-created')->text();
            $timestamp = $this->dateNormalizer->createTimeStamp(
                date: $date,
                pattern: '/\w{3} (\d{2})\/(\d{2})\/(\d{4}) - (\d{2}:\d{2})/',
                replacement: '$3-$2-$1 $4'
            );
            $title = $node->filter('.views-field-title a')->text();
            $link = $node->filter('.views-field-title a')->attr('href');

            if ($interval === null || $interval->inRange((int) $timestamp)) {
                try {
                    $body = $this->crawle(self::URL . "/{$link}")->filter('.field.field--name-body')->text();
                } catch (\Throwable) {
                    $body = '';
                }

                $this->save($title, $link, $this->category, $body, $timestamp);
            } else {
                $this->skip($interval, $timestamp, $title, $date);
            }
        } catch (\Throwable $e) {
            $this->logger->error("> {$e->getMessage()} [Failed] ❌");
            return;
        }
    }
}