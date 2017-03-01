<?php

class ControllerBase extends \Phalcon\Mvc\Controller
{
    public function initialize()
    {
        $title = Options::findFirst('t_key = "site_title"')->t_val;
        $this->tag->setTitle($title);
        $this->CTags->setPageTitle($title);
    }
	
}