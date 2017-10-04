<?php 
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Movie;
use App\Models\Schedule;
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
        $lis        = $crawler->filter('#makan');
        dd($lis->count());
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
        $theatres = Theatre::all();
        foreach ($theatres as  $thea) {
            $client     = new Client();
            $crawler    = $client->request('GET', $thea->getDetailURL());
            $crawler
            ->filter('#makan > table')
            ->each(function ($node,$i) use($thea){
                $node->filter('tr')->each(function($node, $i){
                    if($i > 0){
                        $this->updateNewMovie($node);
                        $this->updateSchedule($node,$thea->ori_id);
                    }
                });
            });
        } 
    }

    private function updateNewMovie($node){
        $tdanchor       = $node->filter('td > a')->eq(0);
        $title          = $node->filter('td')->text();
        $tdlink         = $tdanchor->link()->getUri();
        $page_url       = parse_url($tdlink,PHP_URL_PATH);
        $exp_pg_url     = explode(',', $page_url);
        $slug           = substr($exp_pg_url[0], 1);
        $ori_id         = $exp_pg_url[1];
        $code           = $exp_pg_url[2];

        $strrate        = $node->filter('td')->eq(2)->filter('span')->attr('title');                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       
        $rate           = $this->getRating($strrate);

        $count          = Movie::where('ori_id', $ori_id)->count();
        if(empty($count)){
            $movie              = new Movie;
            $movie->ori_id      = $ori_id;
            $movie->title       = $title;
            $movie->code        = str_replace(".htm", "", $code);
            $movie->slug        = $slug;
            $movie->rate_id     = $rate;
            $movie->cnpxlink    = $tdlink;

            $movie->synopsis            = '';
            $movie->mtix_code           = '';
            $movie->category            = '';
            $movie->producer            = '';
            $movie->director            = '';
            $movie->author              = '';
            $movie->production_house    = '';
            $movie->casts               = '';
            $movie->cover               = '';
            $movie->trailer_link        = '';
            //dd($movie);
            $this->updateDetail($movie);


            $movie->save();
        }
    }

    public function updateDetail($movie){
        $client         = new Client();
        $crawler        = $client->request('GET', $movie->cnpxlink);
        $contentdiv     = $crawler->filter('#content');

        $img            = $contentdiv->filter('img')->eq(0)->attr('src');
        $movie->cover   = $img;
        $durasi         = $contentdiv->filter('span.duration')->text();
        $wraptext       = $contentdiv->filter('.col-m_392')->eq(0);
        $movinfo        = $contentdiv->filter('.movinfo')->text();
        
        $wraptextarr    = $this->convertWraptext($wraptext);
        $movie->duration    = (int) str_replace('minutes', '', $wraptextarr[2]);
        $movie->mtix_code   = substr(strstr($wraptextarr[3], ':'),1);
        $movie->synopsis    = $wraptextarr[9];

        $jf                 = explode('         ', $wraptextarr[4]);
        $movie->category    =  trim(str_replace('Jenis Film : ', '', $jf[0]));
        $movie->producer    =  trim(str_replace('Jenis Film : ', '', $jf[2]));
        $movie->director    =  trim(str_replace('Sutradara    : ', '', $wraptextarr[5]));
        $movie->author      =  trim(str_replace('Penulis    : ', '', $wraptextarr[6]));
        $movie->production_house      =  trim(str_replace('Produksi    :  ', '', $wraptextarr[7]));
        dd($wraptextarr);


    }

    private function convertWraptext($wraptext){
        $return = array();
        $wraptextarr    = preg_split('/\n|\r\n?/', $wraptext->text());
        foreach ($wraptextarr as  $value) {
            $v = trim($value);
            if(!empty($v)){
                $return[] = $v;
            }
        }
        return $return;
    }

    public function updateSchedule($node,$theatre_id){
        $where          = ['movie_id'=> $movie_ori_id, 'theatre_id'=>$theatre_id, 'date'=> date('Y-m-d')];
        $count          = Schedule::where($where)->count();
        if(empty($count)){
            $tdanchor       = $node->filter('td > a')->eq(0);
            $page_url       = parse_url($tdlink,PHP_URL_PATH);
            $exp_pg_url     = explode(',', $page_url);
            $movie_ori_id   = $exp_pg_url[1];

            $strShowTime    = $node->filter('td')->eq(1)->text();
            $expShowTime    = explode(' ', $strShowTime);
            if(!empty($expShowTime)){
                foreach ($expShowTime as $showtime) {
                    $schedule   = new Schedule;
                    $schedule->ori_id       = 0;
                    $schedule->theatre_id   = $theatre_id;
                    $schedule->movie_id     = $movie_ori_id;
                    $schedule->date         = date('Y-m-d');
                    $schedule->showtime     = $showtime;
                    $schedule->save();
                }
            }
        }
    }
    
    private function getRating($rate){
        switch (strtolower($rate)) {
            case 'semua umur':
                return 1;
            case 'remaja':
                return 2;  
            case 'dewasa 17+':
                return 3;              
            default:
                return 1;
        }
    }
    



    private function doCURL($city_ori_id){
        $ch =  curl_init();
        $url        = 'http://www.21cineplex.com/page/ajax-movie-list.php';
        curl_setopt( $ch, CURLOPT_URL, $url );
        $useragent  = 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36';
        curl_setopt( $ch, CURLOPT_USERAGENT, $useragent );

        $headers = array();
        $headers[] = 'Accept: */*';
        $headers[] = 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8';
        $headers[] = 'Host: www.21cineplex.com';
        $headers[] = 'Origin: http://www.21cineplex.com';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $cookie = '__gads=ID=723392b129440c0a:T=1506571393:S=ALNI_MZIi7nwVyjzaJejDdVVOSL_7Tf-mg; __atuvc=1%7C39%2C8%7C40; fullsite=1; PHPSESSID=60f6bb33bfdd41055c544de393e3e7ab; scks_home=1; __utmt=1; __utmt_UA-1473696-2=1; __utma=117930442.1168680518.1506571324.1507024605.1507083253.9; __utmb=117930442.2.10.1507083253; __utmc=117930442; __utmz=117930442.1506672181.2.2.utmcsr=google|utmccn=(organic)|utmcmd=organic|utmctr=(not%20provided); kota=77';
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        
        
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);

        $data = array('cid' => $city_ori_id,'st'=>1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt( $ch, CURLOPT_REFERER, 'http://www.21cineplex.com/' );
        
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT,5 );
        curl_setopt($ch, CURLOPT_VERBOSE, true);


        $result = curl_exec( $ch );
        $info = curl_getinfo($ch);

        curl_close( $ch );
        //var_dump($info);
        die(var_dump($result));
    }
}