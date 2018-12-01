<?php

namespace Pilipinews\Website\Sunstar;

use Pilipinews\Common\Crawler as DomCrawler;
use Pilipinews\Common\Interfaces\CrawlerInterface;

/**
 * Sunstar News Crawler
 *
 * @package Pilipinews
 * @author  Rougin Gutib <rougingutib@gmail.com>
 */
class Crawler implements CrawlerInterface
{
    /**
     * @var string[]
     */
    protected $pages = array(
        'https://www.sunstar.com.ph/morearticles/Manila/Local-news',
        'https://www.sunstar.com.ph/morearticles/Cebu/Local-news',
        'https://www.sunstar.com.ph/morearticles/Davao/Local-news',
    );

    /**
     * Returns an array of articles to scrape.
     *
     * @return string[]
     */
    public function crawl()
    {
        $result = array();

        foreach ((array) $this->pages as $page)
        {
            $items = $this->items((string) $page);

            $result = array_merge($result, $items);
        }

        return $result;
    }

    /**
     * Returns an array of articles to scrape.
     *
     * @param  string $link
     * @return string[]
     */
    protected function items($link)
    {
        $crawler = new DomCrawler(Client::request($link));

        $pattern = '.search-inner > .outer-content';

        $news = $crawler->filter((string) $pattern);

        $items = $news->each(function (DomCrawler $node)
        {
            $pattern = '.inner-content > .title > a';

            $result = $node->filter((string) $pattern);

            return $result->first()->attr('href');
        });

        return array_reverse($items);
    }
}
