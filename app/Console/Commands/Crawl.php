<?php

namespace App\Console\Commands;

use DOMDocument;
use DOMXPath;
use Illuminate\Console\Command;
use Symfony\Component\DomCrawler\Crawler;
use Weidner\Goutte\GoutteFacade;

class Crawl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:blog';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function handle()
    {
        $pageUrl = 'https://dantri.com.vn/giao-duc/vu-lo-de-thi-sinh-8-thi-sinh-duoc-mom-de-can-xu-ly-the-nao-20230620004656478.htm';
        $arr_articles_head = [];
        $arr_article_related =  [];
        $arr_article_recommend = [];
        $base_url = 'https://dantri.com.vn';

        $crawler = GoutteFacade::request('GET', $pageUrl);

        $crawl_arr = $crawler->filter('body');
        if ($crawl_arr->count() === 0) {
            $this->error('No matching elements found on the page. Check if the HTML structure has changed.');
            return;
        }

        $top_articles = $crawler->filter('.sidebar .article-lot .article-item');

        foreach ($top_articles as $top_article) {
            if ($top_article instanceof \DOMElement) {
                $node = new Crawler($top_article);
            }

            $title = $node->filter('.article-title')->text();
            $image = optional($node->filter('.article-thumb img')->first())->attr('data-src');
            $linkHref = $node->filter('.article-thumb a')->attr('href');

            $arr_articles_head[] = [
                'title' => $title,
                'thumbnail_image' => $image,
                'url' => $base_url . $linkHref
            ];
        }

        $related_articles_related = $crawler->filter('.article-related .article-item');

        foreach ($related_articles_related as $related_article) {
            if ($related_article instanceof \DOMElement) {
                $node = new Crawler($related_article);
            }

            $title = $node->filter('.article-title')->text();
            $description = $node->filter('.article-excerpt a')->text();
            $image = optional($node->filter('.article-thumb img')->first())->attr('data-src');
            $linkHref = $node->filter('.article-thumb a')->attr('href');

            $arr_article_related[] = [
                'title' => $title,
                'description' => $description,
                'thumbnail_image' => $image,
                'url' => $base_url . $linkHref
            ];
        }

        $crawl_recommend = $crawler->filter('.article-care');
        $recommend_articles_items = $crawl_recommend->filter('.article-related .article-item');

        foreach ($recommend_articles_items as $recommend_articles_item) {
            if ($recommend_articles_item instanceof \DOMElement) {
                $node = new Crawler($recommend_articles_item);
            }

            $title = $node->filter('.article-title')->text();
            $description = $node->filter('.article-excerpt a')->text();
            $image = optional($node->filter('.article-thumb img')->first())->attr('data-src');
            $linkHref = $node->filter('.article-thumb a')->attr('href');

            $arr_article_recommend[] = [
                'title' => $title,
                'description' => $description,
                'thumbnail_image' => $image,
                'url' => $base_url . $linkHref
            ];
        }

        // Printing the arrays
        echo "Articles Head:\n";
        print_r($arr_articles_head);

        echo "\n\nRelated Articles:\n";
        print_r($arr_article_related);

        echo "\n\nRecommended Articles:\n";
        print_r($arr_article_recommend);
    }



    protected function crawlData(string $type, $crawler)
    {
        $result = $crawler->filter($type)->first();

        return $result ? $result->text() : '';
    }

    protected function crawlData_html(string $type, $crawler)
    {
        $nodeList = $crawler->filter($type);
        if ($nodeList->count() === 0) {
            return '';
        }

        $result = $nodeList->first();
        return $result ? $result->html() : '';
    }
}
