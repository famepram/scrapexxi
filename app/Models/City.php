<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model {
    protected $table = 'city';

    public function generateNPURL(){
    	$urldomain 	= 'http://www.21cineplex.com/nowplaying/';
    	$page		= $this->name.','.$this->ori_id.','.$this->code.'.htm';
    	return $urldomain.$page;
    }
}