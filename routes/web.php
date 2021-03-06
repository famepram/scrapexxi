<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('scrape')->group(function() {
    Route::get('city', 'ScrapeController@scrapeCity')->name('scrape.city');
    Route::get('theatre', 'ScrapeController@scrapeTheatre')->name('scrape.theatre');
    Route::get('city/code', 'ScrapeController@scrapCityCode')->name('scrape.city.code');
    Route::get('movie', 'ScrapeController@scrapMovie')->name('scrape.movie');
    Route::get('htm', 'ScrapeController@scrapHTM')->name('scrape.htm');
});
