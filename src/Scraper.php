<?php

namespace Pilipinews\Website\Sunstar;

use Pilipinews\Common\Article;
use Pilipinews\Common\Crawler as DomCrawler;
use Pilipinews\Common\Interfaces\ScraperInterface;
use Pilipinews\Common\Scraper as AbstractScraper;

/**
 * Sunstar News Scraper
 *
 * @package Pilipinews
 * @author  Rougin Gutib <rougingutib@gmail.com>
 */
class Scraper extends AbstractScraper implements ScraperInterface
{
    /**
     * @var array
     */
    protected $elements = array('.article-header', '.articleBody');

    /**
     * @var string[]
     */
    protected $removables = array('.subSection', '.titleArticle', '.pagingWrap', 'script', '#fb-root');

    /**
     * @var string[]
     */
    protected $texts = array("PHOTO: https://www.sunstar.com.ph/\n", 'Please refresh page for updates.');
    /**
     * Returns the contents of an article.
     *
     * @param  string $link
     * @return \Pilipinews\Common\Article
     */
    public function scrape($link)
    {
        $this->prepare((string) strtolower($link));

        $title = $this->title('title', ' - SUNSTAR');

        $this->remove((array) $this->removables);

        $this->crawler = $this->carousel($this->crawler);

        $body = $this->body((array) $this->elements);

        $body = $this->video($this->image($body));

        $html = $this->html($body, $this->texts);

        return new Article($title, (string) $html);
    }

    /**
     * Returns the article content based on a given element.
     *
     * @param  string|string[] $element
     * @return \Pilipinews\Common\Crawler
     */
    protected function body($elements)
    {
        is_string($elements) && $elements = array($elements);

        foreach ((array) $elements as $key => $element) {
            $body = $this->crawler->filter($element)->last()->html();

            $body = (string) trim(preg_replace('/\s+/', ' ', $body));

            $elements[$key] = str_replace('  ', ' ', (string) $body);
        }

        return new DomCrawler(implode('<br><br><br>', $elements));
    }

    /**
     * Converts carousel elements to readable string.
     *
     * @param  \Pilipinews\Common\Crawler $crawler
     * @return \Pilipinews\Common\Crawler
     */
    protected function carousel(DomCrawler $crawler)
    {
        $callback = function (DomCrawler $crawler) {
            $texts = $crawler->filter('.img-caption');

            $function = function ($result, $index) use ($texts) {
                $text = $texts->eq($index)->text();

                $image = $result->attr('src') . ' - ' . $text;

                return '<p>PHOTO: ' . $image . '</p>';
            };

            $items = $crawler->filter('img');

            $image = $items->each($function);

            return implode("<br><br>", $image);
        };

        return $this->replace($crawler, '.owl-carousel', $callback);
    }

    /**
     * Converts image elements to readable string.
     *
     * @param  \Pilipinews\Common\Crawler $crawler
     * @return \Pilipinews\Common\Crawler
     */
    protected function image(DomCrawler $crawler)
    {
        $callback = function (DomCrawler $crawler) {
            $break = (string) '<br><br><br>';

            $result = $crawler->filter('img')->first();

            $image = $result->attr('src') . $break;

            return (string) $break . 'PHOTO: ' . $image;
        };

        return $this->replace($crawler, '.imgArticle', $callback);
    }

    /**
     * Initializes the crawler instance.
     *
     * @param  string $link
     * @return void
     */
    protected function prepare($link)
    {
        $this->crawler = new DomCrawler(Client::request($link));
    }

    /**
     * Converts video elements to readable string.
     *
     * @param  \Pilipinews\Common\Crawler $crawler
     * @return \Pilipinews\Common\Crawler
     */
    protected function video(DomCrawler $crawler)
    {
        $callback = function (DomCrawler $crawler) {
            $link = trim($crawler->attr('data-href'));

            $break = '<br><br><br>';

            return $break . 'VIDEO: ' . $link . $break;
        };

        return $this->replace($crawler, '.fb-video', $callback);
    }
}
