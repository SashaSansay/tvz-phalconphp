<?php

class IndexController extends ControllerBase
{
    public function indexAction(){

        $this->tag->appendTitle(' | '. Options::findFirst('t_key = "site_description"')->t_val);
        $this->CTags->appendPageTitle(' | '. Options::findFirst('t_key = "site_description"')->t_val);

        $last_series = Series::find(
            array(
                'order' => 'id DESC',
                'group' => 'serial_id',
                'limit' => 30
            )
        );
        $this->view->setVar('last_series',$last_series);

        $popular_serials = Serial::find(
            array(
                'order' => 'stars DESC',
                'limit' => 30
            )
        );
        $this->view->setVar('popular_serials',$popular_serials);

        $films = Film::find(
            array(
                'limit' => 30
            )
        );

        $this->view->setVar('films', $films);

        $promo = Options::findFirst('t_key = "promo_serial"');
        if($promo){
            $promo = Serial::findFirst($promo->t_val);

            $this->CTags->setImage($promo->image_back);
        }
        $this->view->setVar('promo',$promo);
    }

    public function show404Action(){
        $this->response->setStatusCode(404, "Not Found");
    }
}