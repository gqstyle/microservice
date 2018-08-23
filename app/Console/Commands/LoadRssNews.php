<?php

namespace App\Console\Commands;

use App\News;
use Awjudd\FeedReader\FeedReader;
use Illuminate\Console\Command;

class LoadRssNews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'LoadRssNews:loadnews';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Load rss news';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $feed = new FeedReader();
        $feed = $feed->read(array('https://tengrinews.kz/news.rss','https://www.nur.kz/rss/yandex.rss', 'http://24.kz/ru/news?format=feed', 'https://kapital.kz/feed', 'https://www.gazeta.ru/export/rss/tech_news.xml', 'https://www.sports.ru/rss/all_news.xml'));
        $feed->set_item_limit(10);
        $feed->enable_order_by_date(true);
        $feed->handle_content_type();
        $feed->init();
        foreach ($feed->get_items(0, 50) as $key=> $item) {
            if($item->get_enclosure()->link == ''){
                $dom = new \DOMDocument();
                if($dom->loadHTML($item->get_description()!==null)){

                    if($dom->getElementsByTagName('img')->item(0)===null){
                        $image = '';
                    }else{
                        $image = $dom->getElementsByTagName('img')->item(0)->getAttribute('src');
                    }
                }
            }else{
                $image = $item->get_enclosure()->link;
            }
            $arr[] = [
                'title'=>$item->get_title(),
                'link'=>$item->get_link(),
                'image'=> $image,
                'date'=>$item->get_date(),
                'body'=>$item->get_description(),
            ];
        }

        $arr = json_encode($arr, JSON_UNESCAPED_UNICODE);
        $news = new News();
        $news->json = $arr;
        $news->save();
    }
}
