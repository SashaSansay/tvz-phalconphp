<?php

class ManageController extends ControllerBase
{

    private function makeNotIndexAction(){
        $this->makeAction();
        if (!$this->session->has('admin-logged')) {
            $this->response->redirect('/manage');
            $this->response->send();
            return false;
        }
    }

    private function makeAction(){
        if (!$this->session->has('admin-logged')) {
            $this->view->setVar("auth", array("status" => "0"));
        }
        else{
            $auth = array(
                "status" => "1"
            );

            $this->view->setVar("auth", $auth);
        }
    }

    public function loginAction(){
        if( hash_pbkdf2('sha256',$_GET['pass'], $this->config->options->salt, 100) != $this->config->options->admin_pass){
            $this->view->setVar("error", true);
            return $this->response->redirect('/manage/?error=true');
        }


        $this->session->set('admin-logged', true);
        return $this->response->redirect('/manage');
    }

    public function logoutAction(){
        $this->session->remove('admin-logged');
        return $this->response->redirect('/manage');
    }

    public function indexAction(){
        $this->makeAction();
        $films = Film::find([
            'order' => 'views_count desc',
            'limit' => 15
        ]);
        $series = Series::find([
            'order' => 'views_count desc',
            'limit' => 15
        ]);

        $this->view->setVar('films',$films);
        $this->view->setVar('series',$series);
/*
        foreach(Serial::find() as $item){
            $item->setSlug();
            $item->save();
        }
        foreach(Film::find() as $item){
            $item->setSlug();
            $item->save();
        }*/
    }

    public function getcatAction(){
        $this->makeNotIndexAction();
        $cats = file_get_contents('http://api.themoviedb.org/3/genre/tv/list?api_key='.$this->config->options->api_key.'&language=RU_ru');
        $cats = json_decode($cats);
//        echo '<pre>';
//        var_dump($cats);
//        echo '</pre>';
        foreach($cats->genres as $cat){
            $c = new Category();
            $c->tmdb_id = $cat->id;
            $c->title = $cat->name;

            $c->save();
        }
    }

    public function getserAction(){
        $this->makeNotIndexAction();
        $serials = Serial::find(
            array(
                'conditions' => 'id <> 3'
            )
        );
        $conf = file_get_contents('http://api.themoviedb.org/3/configuration?api_key='.$this->config->options->api_key);
        $conf = json_decode($conf);

        foreach($serials as $serial){
            $ser = file_get_contents('http://api.themoviedb.org/3/tv/'.$serial->tmdb_id.'?api_key='.$this->config->options->api_key.'&language=RU_ru');
            $ser = json_decode($ser);

            foreach($ser->genres as $genre){
                $cat = Category::findFirst(array(
                    'conditions' => 'tmdb_id = '.$genre->id
                ));

                if(!$cat){
                    $cat = new Category();
                    $cat->tmdb_id = $genre->id;
                    $cat->title = $genre->name;
                    $cat->save();
                }

                $cat_ser = new CategorySerial();
                $cat_ser->category_id = $cat->id;
                $cat_ser->serial_id = $serial->id;

                $cat_ser->save();
            }

            $date = DateTime::createFromFormat('Y-m-d',$ser->first_air_date);

            $serial->year = intval($date->format('Y'));
            $serial->stars = $ser->vote_average;
            $serial->image_back = $conf->images->secure_base_url.'original'.$ser->backdrop_path;
            var_dump($serial->update());
        }
    }


    public function getoneserAction($id){

        $this->makeNotIndexAction();
        $echo = array();

        $conf = file_get_contents('http://api.themoviedb.org/3/configuration?api_key='.$this->config->options->api_key);
        $conf = json_decode($conf);

        $ser = file_get_contents('http://api.themoviedb.org/3/tv/'.$id.'?api_key='.$this->config->options->api_key.'&language=RU_ru');
        if($ser) {
            $ser = json_decode($ser);
        }

//        var_dump($ser);


        $echo['cats'] = array();

        foreach($ser->genres as $genre){
            $echo['cats'][] = array(
                'tmdb_id' => $genre->id,
                'title' => $genre->name
            );
        }

        $date = DateTime::createFromFormat('Y-m-d',$ser->first_air_date);

        $echo['year'] = intval($date->format('Y'));
        $echo['stars'] = $ser->vote_average;
        $echo['image_back'] = $conf->images->secure_base_url.'original'.$ser->backdrop_path;
        $echo['image'] = $conf->images->secure_base_url.'w342'.$ser->poster_path;
        $echo['title_en'] = $ser->original_name;
        $echo['title'] = $ser->name;
        $echo['description'] = $ser->overview;
//        $echo['release_date'] = $ser->release_date;

        $this->view->setRenderLevel(
            Phalcon\MVC\View::LEVEL_NO_RENDER
        );

        echo json_encode($echo);
    }

    public function getfilmAction(){
        $this->makeNotIndexAction();
        $serials = Film::find();
        $conf = file_get_contents('http://api.themoviedb.org/3/configuration?api_key='.$this->config->options->api_key);
        $conf = json_decode($conf);
//        echo '<pre>';
//            var_dump($conf);
//        echo '</pre>';

        foreach($serials as $serial){
            $ser = file_get_contents('http://api.themoviedb.org/3/movie/'.$serial->tmdb_id.'?api_key='.$this->config->options->api_key.'&language=RU_ru');
            if($ser){
                $ser = json_decode($ser);
                echo '<pre>';
//            var_dump($ser);
                echo '</pre>';

                foreach($ser->genres as $genre){
                    $cat = Category::findFirst(array(
                        'conditions' => 'tmdb_id = '.$genre->id
                    ));

                    if(!$cat){
                        $cat = new Category();
                        $cat->tmdb_id = $genre->id;
                        $cat->title = $genre->name;
                        echo 'cat not exists. add new';
                        var_dump($cat->save());
                    }

                    $cat_ser = new CategoryFilm();
                    $cat_ser->category_id = $cat->id;
                    $cat_ser->film_id = $serial->id;

                    $cat_ser->save();
                }

                $date = DateTime::createFromFormat('Y-m-d',$ser->release_date);

                $serial->year = intval($date->format('Y'));
                $serial->stars = $ser->vote_average;
                $serial->image_back = $conf->images->secure_base_url.'original'.$ser->backdrop_path;
                $serial->image = $conf->images->secure_base_url.'w342'.$ser->poster_path;
                $serial->title_en = $ser->original_title;
                $serial->description = $ser->overview;
                $serial->release_date = $ser->release_date;
                echo 'saving serial: ';
                var_dump($serial->update());
            }
        }
    }

    public function getonefilmAction($id){
        $this->makeNotIndexAction();
        $echo = array();

        $conf = file_get_contents('http://api.themoviedb.org/3/configuration?api_key='.$this->config->options->api_key);
        $conf = json_decode($conf);

        $ser = file_get_contents('http://api.themoviedb.org/3/movie/'.$id.'?api_key='.$this->config->options->api_key.'&language=RU_ru');
        if($ser) {
            $ser = json_decode($ser);
        }


        $echo['cats'] = array();

        foreach($ser->genres as $genre){
            $echo['cats'][] = array(
                'tmdb_id' => $genre->id,
                'title' => $genre->name
            );
        }

        $date = DateTime::createFromFormat('Y-m-d',$ser->release_date);

        $echo['year'] = intval($date->format('Y'));
        $echo['stars'] = $ser->vote_average;
        $echo['image_back'] = $conf->images->secure_base_url.'original'.$ser->backdrop_path;
        $echo['image'] = $conf->images->secure_base_url.'w342'.$ser->poster_path;
        $echo['title_en'] = $ser->original_title;
        $echo['title'] = $ser->title;
        $echo['description'] = $ser->overview;
        $echo['release_date'] = $ser->release_date;

        $this->view->setRenderLevel(
            Phalcon\MVC\View::LEVEL_NO_RENDER
        );

        echo json_encode($echo);
    }

    public function categoryAction($id = false, $action = false){
        $this->makeNotIndexAction();
        $single = false;

        if($action=='update'){
            $title = $this->request->getPost('title','string');
            $tmdb_id = $this->request->getPost('tmdb_id','int');

            if($id == 'add'){
                $item = new Category();
            }else{
                $item = Category::findFirst($id);
            }
            if(!$item ){
                $item = new Category();
            }
            $item->title = $title;
            $item->tmdb_id = $tmdb_id;

            $item->save();

            $this->response->redirect('/manage/category/'.$id);
            $this->response->send();
            return false;
        }

        if($action == 'delete'){
            $item = Category::findFirst($id);

            $item->delete();

            $this->response->redirect('/manage/category');
            $this->response->send();
            return false;
        }

        if($id !== false){
            $single = true;
            if($id == 'add'){
                $cat = new Category();
            }else{
                $cat = Category::findFirst($id);
            }

            $this->view->setVar('cat',$cat);
        }

        $this->view->setVar('single',$single);

        $cats = Category::find();
        $this->view->setVar('cats',$cats);
    }

    public function commercialAction($id = false, $action = false){
        $this->makeNotIndexAction();
        $single = false;

        if($action=='update'){
            $title = $this->request->getPost('title','string');
            $video_src = $this->request->getPost('video_src','string');
            $link = $this->request->getPost('link','string');

            if($id == 'add'){
                $item = new Commercial();
            }else{
                $item = Commercial::findFirst($id);
            }
            if(!$item ){
                $item = new Category();
            }
            $item->title = $title;
            $item->link = $link;
            $item->video_src = $video_src;

            $item->save();

            $this->response->redirect('/manage/commercial/'.$id);
            $this->response->send();
            return false;
        }

        if($action == 'delete'){
            $item = Commercial::findFirst($id);

            $item->delete();

            $this->response->redirect('/manage/commercial');
            $this->response->send();
            return false;
        }

        if($id !== false){
            $single = true;
            if($id == 'add'){
                $item = new Commercial();
            }else{
                $item = Commercial::findFirst($id);
            }
            $this->view->setVar('commercial',$item);
        }

        $this->view->setVar('single',$single);

        $commercials = Commercial::find();
        $this->view->setVar('commercials',$commercials);
    }

    public function filmAction($id = false, $action = false){
        $this->makeNotIndexAction();
        $single = false;

        if($action=='update'){
            if($id == 'add'){
                $item = new Film();
            }else{
                $item = Film::findFirst($id);
            }
            if(!$item ){
                $item = new Film();
            }
            foreach($item as $key => $value){
                $val = $this->request->getPost($key);
                if(!is_null($val)){
                    $item->$key = $val;
                }
                if($key == 'commercial_id' && $val == ''){
                    $item->$key = null;
                }
            }

            $item->save();

            foreach($item->getCategoryFilm() as $cs){
                $cs->delete();
            }

            foreach ($this->request->getPost('category') as $ccc) {
                $cf = new CategoryFilm();
                $cf->film_id = $item->id;
                $cf->category_id = intval($ccc);
                $cf->save();
            }

            $this->response->redirect('/manage/film/'.$item->id);
            $this->response->send();
            return false;
        }

        if($action == 'delete'){
            $item = Film::findFirst($id);

            $item->delete();

            $this->response->redirect('/manage/film');
            $this->response->send();
            return false;
        }

        if($id !== false){
            $single = true;
            if($id == 'add'){
                $item = new Film();
            }else{
                $item = Film::findFirst($id);
            }
            $this->view->setVar('film',$item);

            $commercials = Commercial::find();
            $this->view->setVar('commercials',$commercials);

            $cats_ = array();
            $cats = Category::find();
            $catsFilm = $item->getCategoryFilm();
            foreach($cats as $cat){
                $selected = false;

                foreach($catsFilm as $catFilm){
                    if($catFilm->category_id == $cat->id){
                        $selected = true;
                    }
                }

                $cats_[] = array(
                    'selected' => $selected,
                    'id' => $cat->id,
                    'tmdb_id' => $cat->tmdb_id,
                    'title' => $cat->title
                );
            }
            $this->view->setVar('cats',$cats_);

        }

        $this->view->setVar('single',$single);

        $items = Film::find();
        $this->view->setVar('films',$items);
    }

    public function serialAction($id = false, $action = false){
        $this->makeNotIndexAction();
        $single = false;

        if($action=='update'){
            if($id == 'add'){
                $item = new Serial();
            }else{
                $item = Serial::findFirst($id);
            }
            if(!$item ){
                $item = new Serial();
            }
            foreach($item as $key => $value){
                $val = $this->request->getPost($key);
                if(!is_null($val)){
                    $item->$key = $val;
                }
            }
            $item->save();

            foreach($item->getCategorySerial() as $cs){
                $cs->delete();
            }

            foreach ($this->request->getPost('category') as $ccc) {
                $cf = new CategorySerial();
                $cf->serial_id = $item->id;
                $cf->category_id = intval($ccc);
                $cf->save();
            }

            $this->response->redirect('/manage/serial/'.$item->id);
            $this->response->send();
            return false;
        }

        if($action == 'delete'){
            $item = Serial::findFirst($id);

            $item->delete();

            $this->response->redirect('/manage/serial');
            $this->response->send();
            return false;
        }

        if($id !== false){
            $single = true;
            if($id == 'add'){
                $item = new Serial();
            }else{
                $item = Serial::findFirst($id);
            }
            $this->view->setVar('serial',$item);

            $cats_ = array();
            $cats = Category::find();
            $catsFilm = $item->getCategorySerial();
            foreach($cats as $cat){
                $selected = false;

                foreach($catsFilm as $catFilm){
                    if($catFilm->category_id == $cat->id){
                        $selected = true;
                    }
                }

                $cats_[] = array(
                    'selected' => $selected,
                    'id' => $cat->id,
                    'tmdb_id' => $cat->tmdb_id,
                    'title' => $cat->title
                );
            }
            $this->view->setVar('cats',$cats_);
        }

        $this->view->setVar('single',$single);

        $items = Serial::find();
        $this->view->setVar('serials',$items);
    }

    public function seriesAction($id = false, $action = false){
        $this->makeNotIndexAction();
        $single = false;

        if($action=='update'){
            if($id == 'add'){
                $item = new Series();
            }else{
                $item = Series::findFirst($id);
            }
            if(!$item ){
                $item = new Series();
            }
            foreach($item as $key => $value){
                $val = $this->request->getPost($key);
                if(!is_null($val)){
                    $item->$key = $val;
                }
                if($key == 'commercial_id' && $val == ''){
                    $item->$key = null;
                }
            }
            $item->save();

            $this->response->redirect('/manage/series/'.$item->id);
            $this->response->send();
            return false;
        }

        if($action == 'delete'){
            $item = Series::findFirst($id);

            $item->delete();

            $this->response->redirect('/manage/series');
            $this->response->send();
            return false;
        }

        if($id !== false){
            $single = true;
            if($id == 'add'){
                $item = new Series();
            }else{
                $item = Series::findFirst($id);
            }
            $this->view->setVar('series',$item);

            $commercials = Commercial::find();
            $this->view->setVar('commercials',$commercials);

            $serials = Serial::find();
            $this->view->setVar('serials',$serials);
        }

        $this->view->setVar('single',$single);

        $items = Series::find();
        $this->view->setVar('seriess',$items);
    }
}