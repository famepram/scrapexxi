<?php 
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Goutte\Client;



class ScrapeController extends Controller
{
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
            echo "ID : ".$node->attr('value')."-----------".$node->text()."<br />";
        });

        //return view('user.profile', ['user' => User::findOrFail($id)]);
    }
}