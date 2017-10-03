<?php 
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Theatre;
use Goutte\Client;




class ScrapeController extends Controller {
    /**
     * Show the profile for the given user.
     *
     * @param  int  $id
     * @return Response
     */
    public function scrapeCity(){
        $client = new Client();
        $crawler = $client->request('GET', 'http://www.21cineplex.com/theaters');
        $crawler->filter('select#sl_city > option')->each(function ($node) {
            $city = new City;
            $city->ori_id   = $node->attr('value');
            $city->name     = strtolower($node->text());
            $city->save();
            echo "ID : ".$city->ori_id."-----------".$city->name."<br />";
        });
    }


    public function scrapeTheatre(){
        $url        = 'http://www.21cineplex.com/theaters';
        $client     = new Client();
        $crawler    = $client->request('GET', $url);
        $crawler
        ->filter('table#tb_theater tr')
        ->each(function ($node,$i){
            $city_id = $node->attr('data-city');
            $tds = $node->filter('td');
            if($tds->count() == 2){
                $left           = $tds->eq(0);
                $right          = $tds->eq(1);
                $cnpx_anchor    = $left->filter('a');
                $address        = $cnpx_anchor->attr('rel');
                $cnpx_link      = $cnpx_anchor->link()->getUri();
                $page_url       = parse_url($cnpx_link,PHP_URL_PATH);
                $exp_pg_url     = explode(',', $page_url);
                $slug           = $exp_pg_url[0];
                $ori_id         = $exp_pg_url[1];
                $code           = $exp_pg_url[2];
                $name           = $left->text();
                $phone          = $right->text();

                $theatre            = new Theatre;
                $theatre->city_id   = $city_id;
                $theatre->ori_id    = $ori_id;
                $theatre->code      = str_replace(".htm", "", $code);

                $theatre->slug      = str_replace("/theater/", "", $slug);
                $theatre->name      = $name;
                $theatre->cnpx_link = $cnpx_link;
                $theatre->phone     = $phone;
                $theatre->address   = $address;
                $theatre->lat       = 0;
                $theatre->lng       = 0;
                $theatre->save();

                $city       = City::where('ori_id', $city_id)->first();
                if(empty($city->code)){
                    $city_code          = substr($theatre->code, 0, 3);
                    $city->code         = $city_code;
                    $city->save();
                }
            }
        });

    }

    public function scrapCityCode(){
        $url = 'http://www.21cineplex.com/nowplaying';
        $client     = new Client();
        $crawler    = $client->request('GET', $url);
        $lis        = $crawler->filter('.col-content ul.white')->first()->filter('li');
        //dd($lis->count());
        $lis->each(function($node,$i){
            // $link       = $node->filter('a')->link()->getUri();
            // $path       = str_replace(".htm", "",end(explode('/', rtrim($link, '/'))));
            // $exp_path   = explode(',', $path);
            // $ori_id     = $exp_path[1];
            // $code       = $exp_path[2];

            // $city       = City::where('ori_id', $ori_id)->first();
            // $city->code = $code;
            //$city->save();
            echo $node->text().'------------<br />';

        });
    }
}