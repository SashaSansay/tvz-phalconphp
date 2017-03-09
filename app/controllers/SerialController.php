<?php
use Phalcon\Filter;
use Phalcon\Mvc\Model\Query;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;

class SerialController extends ControllerBase
{
    private function redirect404(){

        $this->dispatcher->forward(
            [
                "controller" => "index",
                "action"     => "show404"
            ]
        );

        return true;
    }

    public function indexAction($label,$season,$num){
        $filter = new Filter();

        $label = $filter->sanitize($label,'string');
        $season = $filter->sanitize($season,'int');
        $num = $filter->sanitize($num,'int');

        if(!$label || !$season || !$num){
            return $this->redirect404();
        }

        $serial = Serial::findFirst('label = "'.$label.'"');

        if(!$serial){
            return $this->redirect404();
        }

        $series = Series::findFirst([
            'conditions' => 'season = '.$season.' AND series_num = '.$num.' AND serial_id = '.$serial->id
        ]);

        if(!$series || !$serial){
            return $this->redirect404();
        }

        $this->tag->appendTitle(' | Смотреть сериал: '.$serial->title.' '.$series->season.' сезон '.$series->series_num.' серия онлайн');
        $this->CTags->appendPageTitle(' | Смотреть сериал: '.$serial->title.' '.$series->season.' сезон '.$series->series_num.' серия онлайн');

        $this->CTags->setDescription('Смотреть '.$serial->title.' '.$series->season.' сезон '.$series->series_num.' серия онлайн.

        '.$series->getSerial()->description );

        $this->CTags->setImage($serial->image_back);

        $seasons = Series::find(array(
            'conditions' => 'serial_id = '.$serial->id,
            'group' => 'season',
            'distinct' => 'season'
        ));

        $serial_series=array();

        foreach($seasons as $season){
            $seriess = Series::find(array(
                'conditions' => 'serial_id = '.$serial->id.' AND season='.$season->season,
                'order' => 'id desc'
            ));
            $serial_series[$season->season] = $seriess;
        }

        $commercial = false;

        if($series->commercial_id){
            $commercial = Commercial::findFirst($series->commercial_id);
        }

        $this->view->setVar('commercial',$commercial);
        $this->view->setVar('serial',$serial);
        $this->view->setVar('series',$series);
        $this->view->setVar('seasons',$seasons);
        $this->view->setVar('serial_series',$serial_series);

        $series->views_count += 1;
        $series->update();
    }

    public function serialAction($label = false){
        $filter = new Filter();

        $label = $filter->sanitize($label,'string');

        if(!$label){
            $this->redirect404();
        }

        $serial = Serial::findFirst('label = "'.$label.'"');

        if(!$serial){
            return $this->redirect404();
        }

        $this->view->setVar('serial',$serial);

        $seasons = Series::find(array(
            'conditions' => 'serial_id = '.$serial->id,
            'group' => 'season',
            'distinct' => 'season'
        ));

        $serial_series=array();

        foreach($seasons as $season){
            $seriess = Series::find(array(
                'conditions' => 'serial_id = '.$serial->id.' AND season='.$season->season,
                'order' => 'id desc'
            ));
            $serial_series[$season->season] = $seriess;
        }

        $this->tag->appendTitle(' | Смотреть сериал: '.$serial->title);
        $this->CTags->appendPageTitle(' | Смотреть сериал: '.$serial->title);
        $this->CTags->setDescription('Смотреть '.$serial->title.' онлайн. '.$serial->description);
        $this->CTags->setImage('https://tvz.im'.$serial->getImageBackThumb());

        $this->view->setVar('seasons',$seasons);
        $this->view->setVar('serial_series',$serial_series);

    }

    public function listAction(){
        /*$ex = $this->db->query('select DISTINCT substring(title,1,1) as ch from serial ORDER by ch ASC');
        $ex = $ex->fetchAll($ex);

        $serials = [];

        foreach($ex as $ch){
            $i = Serial::find(
                "title LIKE '".$ch[0]."%'"
            );

            $serials[$ch[0]] = $i;
        }

        $ex = $this->db->query('select DISTINCT substring(title,1,1) as ch from film ORDER by ch ASC');
        $ex = $ex->fetchAll($ex);

        $films = [];

        foreach($ex as $ch){
            $items = Film::find([
                "title LIKE '".$ch[0]."%'"
            ]);

            $films[$ch[0]] = $items;
        }*/

        $serials = Serial::find();
        $films = Film::find();
        $cats = Category::find();

        $this->tag->appendTitle(' | Список сериалов');
        $this->CTags->appendPageTitle(' | Список сериалов');
        $this->CTags->setDescription('Список сериалов TVZ.im. Смотреть сериалы онлайн без регистрации в HD качестве.');

        $this->view->setVar('serials',$serials);
        $this->view->setVar('films',$films);
        $this->view->setVar('cats',$cats);
    }
}