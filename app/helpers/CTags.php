<?php

use Phalcon\Tag;

class CTags extends Tag
{
    private static $descriptionText = '';
    private static $imageLink = '';
    private static $pageTitle = '';

    /**
     * @return string
     */
    public static function getPageTitle()
    {
        return self::$pageTitle;
    }

    /**
     * @param string $appended
     */
    public static function appendPageTitle($appended)
    {
        self::$pageTitle = self::$pageTitle.$appended;
    }

    /**
     * @param string $pageTitle
     */
    public static function setPageTitle($pageTitle)
    {
        self::$pageTitle = $pageTitle;
    }

    public static function setDescription($txt){
        self::$descriptionText = $txt;
    }

    public static function setImage($txt){
        self::$imageLink = $txt;
    }

    static public function getDescription(){
        if(self::$descriptionText == ''){
            self::$descriptionText = Options::findFirst('t_key = "site_description"')->t_val;
        }
        $code = '<meta name="description" content="'.self::$descriptionText.'">';
        $code.= '<meta property="og:description" content="'.self::$descriptionText.'">';
        return $code;
    }

    static public function getImage(){
//        if(self::$imageLink == ''){
//            self::$imageLink = Options::findFirst('t_key = "site_description"')->t_val;
//        }
        $code = '<meta property="og:image" content="'.self::$imageLink.'">';
        $code.= '<meta property="image" content="'.self::$imageLink.'">';
        return $code;
    }
}