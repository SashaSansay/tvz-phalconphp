<?php

use Phalcon\Mvc\Model;

class Series extends Model{
    public $id;
    public $views_count;
    public $publish;
    public $title;
    public $video_src;
    public $commercial_id;
    public $serial_id;
    public $season;
    public $series_num;

    public function initialize(){
        $this->belongsTo('serial_id','Serial','id');
        $this->belongsTo('commercial_id','Commercial','id');
    }

    public function getImageThumb(){
        return '/i/p/'.$this->id;
    }

}