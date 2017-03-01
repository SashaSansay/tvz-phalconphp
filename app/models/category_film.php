<?php

use Phalcon\Mvc\Model;

class CategoryFilm extends Model{
    public $film_id;
    public $category_id;

    public function initialize(){
        $this->belongsTo('category_id','Category','id');
        $this->belongsTo('film_id','Film','id');
    }
}