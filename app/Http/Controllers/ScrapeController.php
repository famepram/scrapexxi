<?php 
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Theatre;
use Goutte\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client as GuzzleClient;




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
            $city->code     = '';
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

    public function scrapMovie(){
        $cities = City::all();
        $url    = 'http://www.21cineplex.com/page/ajax-movie-list.php';
        $headers = [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36',
                'Accept'     => '*/*',
                'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
                'Origin'     => 'http://www.21cineplex.com',
                'Host'       => 'www.21cineplex.com',
                'Referer'    => 'http://www.21cineplex.com/',
                'Cookie'     => 'PHPSESSID=f5bb0ac257013967cb939c78d80349a0; fullsite=1; __gads=ID=32a77cbea177b0db:T=1506956456:S=ALNI_Ma1H7FYk2u3mIAdIq7xd9_NUnExmA; scks_th=2; scks_npx=1; scks_home=1; __atuvc=5%7C40; __atuvs=59d3ba8292acd26e001; kota=26; __utma=117930442.583593974.1506956450.1506958426.1507045903.3; __utmb=117930442.52.10.1507045903; __utmc=117930442; __utmz=117930442.1506956450.1.1.utmcsr=(direct)|utmccn=(direct)|utmcmd=(none)',   
                'X-Requested-With' => 'XMLHttpRequest'
            ]
        ];
        foreach ($cities as  $city) {
            $client = new GuzzleClient();
            $result = $client->post($url, [
                'form_params' => [
                    'scid' => $city->ori_id,
                    'st'=>1

                ]
            ]);
            // echo $result->getHeader('content-type');
            // echo '<br />';
            // 'application/json; charset=utf8'
            $d =  $result->getBody();
            dd($d);
            //echo '<br />';
            //echo '----------------------------------------------------------------------';


            // $url        = $city->generateNPURL();
            // dd($url);
            // $client     = new Client();
            // $crawler    = $client->request('GET', $url);
            // $list       = $crawler->filter('#mvlist');
            // dd($list->count());
        }
    }
}