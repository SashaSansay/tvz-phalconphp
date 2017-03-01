<?php

use Phalcon\Mvc\Model;

class Film extends Model{
    public $id;
    public $label;
    public $tmdb_id;
    public $title;
    public $title_en;
    public $image;
    public $image_back;
    public $rating;
    public $stars;
    public $description;
    public $views_count;
    public $commercial_id;
    public $publish;
    public $video_src;
    public $release_date;

    public function initialize()
    {
        $this->hasMany('id','CategoryFilm','film_id');
    }

    public function setSlug()
    {
        $text = $this->title_en;
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
//        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);

        if (empty($text)) {
            return 'serial'.$this->id;
        }

        $this->label = $text;
    }

    public function getRating(){
        switch($this->rating){
            case 'TV-G' :
            case 'TV-Y' : return '+0'; break;
            case 'TV-Y7' : return '+7'; break;
            case 'TV-PG' :
            case 'TV-14' : return '+14'; break;
            case 'TV-MA' : return '+17'; break;
            case '16' : return '+16'; break;
            case '18' : return '+18'; break;
            default : return '+14'; break;
        }
    }


    public function beforeDelete()
    {
        foreach(CategoryFilm::find('film_id = '.$this->id) as $cf){
            $cf->delete();
        }
    }

    public function getImageThumb(){
        return '/i/f/'.$this->label.'/1';
    }

    public function getImageBackThumb(){
        return '/i/f/'.$this->label.'/2';
    }
}