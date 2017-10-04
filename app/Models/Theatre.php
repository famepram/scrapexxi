<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Theatre extends Model {
    
    protected $table = 'theatre';
    
    public function getDetailURL(){
    	$pathURL 	= 'http://www.21cineplex.com/theater/';
    	$page		= $this->slug.','.$this->ori_id.','.$this->code.'.htm';
    	$url 		= $pathURL.$page;
    	return $url;
    }
}