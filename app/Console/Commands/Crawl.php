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

        $httpClient = new \GuzzleHttp\Client();
        $response = $httpClient->get($pageUrl);
        $htmlString = (string) $response->getBody();

        $doc = new DOMDocument();
        // Suppressing errors for loading HTML
        libxml_use_internal_errors(true);
        $doc->loadHTML($htmlString);
        $xpath = new DOMXPath($doc);

        // XPath query for top articles
        $topArticlesXPath = $xpath->query('//main/div[2]/div[2]/div[2]/article/article');
        foreach ($topArticlesXPath as $topArticleXPath) {
            $title = $xpath->evaluate('string(.//h3[@class="article-title"])', $topArticleXPath);
            $image = $xpath->evaluate('string(.//div[@class="article-thumb"]/img/@data-src)', $topArticleXPath);
            $linkHref = $xpath->evaluate('string(.//div[@class="article-thumb"]/a/@href)', $topArticleXPath);

            $arr_articles_head[] = [
                'title' => $title,
                'thumbnail_image' => $image,
                'url' => $base_url . $linkHref
            ];
        }

        // XPath query for related articles
        $relatedArticlesXPath = $xpath->query('/html/body/main/div[2]/div[2]/div[1]/aside/article');

        foreach ($relatedArticlesXPath as $relatedArticleXPath) {
            $title = $xpath->evaluate('string(.//h3[@class="article-title"])', $relatedArticleXPath);
            $description = $xpath->evaluate('string(.//p[@class="article-excerpt"]/a)', $relatedArticleXPath);
            $image = $xpath->evaluate('string(.//div[@class="article-thumb"]/img/@data-src)', $relatedArticleXPath);
            $linkHref = $xpath->evaluate('string(.//div[@class="article-thumb"]/a/@href)', $relatedArticleXPath);

            $arr_article_related[] = [
                'title' => $title,
                'description' => $description,
                'thumbnail_image' => $image,
                'url' => $base_url . $linkHref
            ];
        }

        // XPath query for recommended articles
        $recommendArticlesXPath = $xpath->query('//main/div[2]/div[2]/div[1]/div[7]/div/div/article');

        foreach ($recommendArticlesXPath as $recommendArticleXPath) {
            $title = $xpath->evaluate('string(.//h3[@class="article-title"])', $recommendArticleXPath);
            $description = $xpath->evaluate('string(.//p[@class="article-excerpt"]/a)', $recommendArticleXPath);
            $image = $xpath->evaluate('string(.//div[@class="article-thumb"]/img/@data-src)', $recommendArticleXPath);
            $linkHref = $xpath->evaluate('string(.//div[@class="article-thumb"]/a/@href)', $recommendArticleXPath);

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
}
