<?php

use Phalcon\Mvc\Model;

class Category extends Model{
    public $id;
    public $tmdb_id;
    public $title;

    public function initialize()
    {
        $this->hasMany('id','CategorySerial','category_id');
    }
}