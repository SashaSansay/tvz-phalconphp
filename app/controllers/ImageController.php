<?php
use Phalcon\Filter;

class ImageController extends ControllerBase
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
    public function indexAction($type = false, $label = false, $image = false){
        $filter = new Filter();

        $type = $filter->sanitize($type,'string');
        $label = $filter->sanitize($label,'string');
        $image = intval($filter->sanitize($image,'int')); // 1 - image; 2 - image_back

        if(!$type || !$image || !$label){
            $this->redirect404();
        }

        if(!in_array ($image,[1,2])){
            $this->redirect404();
        }

        switch($type){
            case 'f' : $item = Film::findFirst([
                "conditions" => "label = ?1",
                "bind"       => [
                    1 => $label,
                ]
            ]);
            $type = 'film';
            break;
            case 's' :
            default : $item = Serial::findFirst([
                "conditions" => "label = ?1",
                "bind"       => [
                    1 => $label,
                ]
            ]);
            $type = 'serial';
            break;
        }

        if(!$item){
            $this->redirect404();
        }


        $frontCache = new \Phalcon\Cache\Frontend\Base64(array(
            "lifetime" => 60*60*24*7
        ));

        $cache = new \Phalcon\Cache\Backend\Mongo($frontCache, array(
            'server' => "mongodb://localhost",
            'db' => 'caches',
            'collection' => 'images'
        ));

        //thumb - 250 150

        $cacheKey = $type.'.'.$item->id.'.'.$image.'.jpg.cache';
        $img    = $cache->get($cacheKey);

        if($img === null || $this->request->getQuery('update') === "tvzupdate"){
            switch($image){
                case 1 : $img_ = $item->image; break;
                case 2 :
                default : $img_ = $item->image_back; break;
            }
            $handle = fopen($img_, 'rb');

            $img = new Imagick();
            $img->readImageFile($handle);

//            $img = new Phalcon\Image\Adapter\Imagick($img_);
            switch($image){
                case 1 : $img->thumbnailImage(600,600,true); break;
                case 2 : $img->thumbnailImage(1200, 600,true); break;
            }

            $cache->save($cacheKey, $img->getImageBlob());
        }


        header('Content-Type: image/jpeg');
        echo $img;
    }

    public function previewAction($id = false){
        $filter = new Filter();

        $id = intval($filter->sanitize($id,'int'));

        if(!$id){
            $this->redirect404();
        }

        $series = Series::findFirst($id);

        if(!$series){
            $this->redirect404();
        }

        include __DIR__.'/../libraries/vendor/autoload.php';

        header('Content-Type: image/gif');

        $frontCache = new \Phalcon\Cache\Frontend\Base64(array(
            "lifetime" => 60*60*24*3
        ));

        $cache = new \Phalcon\Cache\Backend\Mongo($frontCache, array(
            'server' => "mongodb://localhost",
            'db' => 'caches',
            'collection' => 'images'
        ));

        //thumb - 250 150
        $cacheKey = 'preview.'.$series->id.'.png.cache';

        try{

            $img = $cache->get($cacheKey);
            if($img === null){
                $ffmpeg = FFMpeg\FFMpeg::create([
                    'ffmpeg.binaries' => exec('which ffmpeg'),
                    'ffprobe.binaries' => exec('which ffprobe'),
                ]);
                $series->video_src = str_replace('https','http',$series->video_src);
                $video = $ffmpeg->open($series->video_src);
                $frame = $video->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(120));
                $frame->save(__DIR__.'/../cache/series-'.$series->id.'.jpg');
//                $video
//                    ->gif(FFMpeg\Coordinate\TimeCode::fromSeconds(120), new FFMpeg\Coordinate\Dimension(640, 480), 3)
//                    ->save(__DIR__.'/../cache/series-'.$series->id.'.gif');

                $handle = fopen(__DIR__.'/../cache/series-'.$series->id.'.jpg', 'rb');
                $img = new Imagick();
                $img->readImageFile($handle);
                $img->thumbnailImage(600,600,true);

                $cache->save($cacheKey, $img->getImageBlob());
            }
        }catch (Exception $ex){
//            var_dump($ex->getTraceAsString());
            $img = file_get_contents($series->getSerial()->image);
            $cache->save($cacheKey, $img);
        }
        finally{
            echo $img;
        }
    }
}