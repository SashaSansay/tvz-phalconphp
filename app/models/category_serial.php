<?php

use Phalcon\Mvc\Model;

class CategorySerial extends Model{
    public $serial_id;
    public $category_id;

    public function initialize(){
        $this->belongsTo('category_id','Category','id');
        $this->belongsTo('serial_id','Serial','id');
    }
}