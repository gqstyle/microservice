<?php

namespace App\Http\Controllers;

use App\News;
use App\RssSetting;
use App\RssUpdate;
use Awjudd\FeedReader\FeedReader;
use Illuminate\Http\Request;
use Mockery\Exception;

class FeedController extends Controller
{
    public function index(){

        $settings = RssSetting::where('active', 1)->get();
        foreach ($settings->toArray() as $item) {
            $links[] = $item['link'];
        }

        $feed = new FeedReader();

        $feed = $feed->read($links,'default', $links);
//        $feed = $feed->read(array('https://tengrinews.kz/news.rss','https://www.nur.kz/rss/yandex.rss'));

        $feed->set_item_limit(10);
        $feed->enable_order_by_date(true);

        $feed->handle_content_type();
        $feed->init();

        $feed->urls = $links;

        $arr = array();

        foreach ($feed->get_items(0, 0) as $key=> $item) {
            if($item->get_enclosure()->link == ''){
                $dom = new \DOMDocument();
                if($dom->loadHTML($item->get_content()!==null)){

                    if($dom->getElementsByTagName('img')->item(0)===null){
                        $image = '';
                    }else{
                        $image = $dom->getElementsByTagName('img')->item(0)->getAttribute('src');
                    }
                }
            }else{
                $image = $item->get_enclosure()->link;
            }

//            echo '<pre>';
//            print_r($item);
//            echo '</pre>';

            $arr[] = [
              'title'=>$item->get_title(),
              'link'=>$item->get_link(),
              'image'=> $image,
              'date'=>$item->get_date(),
              'body'=>$item->get_content(),
            ];
        }


//
//        echo '<pre>';
////        print_r($arr);
//        echo '</pre>';

        $arr = json_encode($arr, JSON_UNESCAPED_UNICODE);
        $news = new News();
        $news->json = $arr;
        $news->save();
    }

    public function getNews(){
        $arr = News::all()->last();
//        $arr = json_decode($arr->json);
//        return json_decode($arr->json);
        echo '<pre>';
        print_r(json_decode($arr->json));
        echo '</pre>';
    }

}
