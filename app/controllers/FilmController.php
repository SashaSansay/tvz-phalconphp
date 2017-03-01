<?php
use Phalcon\Filter;

class FilmController extends ControllerBase
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

    public function indexAction($label){
        $filter = new Filter();

        $label = $filter->sanitize($label,'string');

        if(!$label){
            return $this->redirect404();
        }

        $film = Film::findFirst('label = "'.$label.'"');

        if(!$label){
            return $this->redirect404();
        }

        $this->tag->appendTitle(' | Смотреть фильм: '.$film->title);

        $this->CTags->appendPageTitle(' | Смотреть фильм: '.$film->title);

        $this->CTags->setDescription('Смотреть '.$film->title.' онлайн.

        '.$film->description);

        $this->CTags->setImage($film->image_back);

        $commercial = false;
        if($film->commercial_id){
            $commercial = Commercial::findFirst($film->commercial_id);
        }

        $this->view->setVar('film',$film);
        $this->view->setVar('commercial',$commercial);

        $film->views_count += 1;
        $film->update();

    }
}