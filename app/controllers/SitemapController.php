<?php

class SitemapController extends ControllerBase
{
    protected $domain = 'http://tvz.im';

    public function indexAction(){
        $response = new Phalcon\Http\Response();

        $expireDate = new \DateTime();
        $expireDate->modify('+1 day');

        $response->setExpires($expireDate);

        $response->setHeader('Content-Type', "application/xml; charset=UTF-8");

        $sitemap = new \DOMDocument("1.0", "UTF-8");

        $urlset = $sitemap->createElement('urlset');
        $urlset->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $urlset->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $urlset->setAttribute('xsi:schemaLocation', 'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd');

        $series = Series::find(["order" => "id DESC","limit"=>100]);
        $films = Film::find(["order" => "id DESC","limit"=>100]);
        $serials = Serial::find(["order" => "id DESC","limit"=>100]);

        $modifiedAt = new \DateTime();
        $modifiedAt->setTimezone(new \DateTimeZone('UTC'));

        $comment = $sitemap->createComment(' Last update of sitemap ' . date("Y-m-d H:i:s").' ');

        $urlset->appendChild($comment);

        foreach ($series as $seriess) {

            $url = $sitemap->createElement('url');

            $link = '/serial/'.$seriess->getSerial()->label.'/'.$seriess->season.'/'.$seriess->series_num;

            $href = $this->domain.$link;
            $url->appendChild($sitemap->createElement('loc', $href));
            $url->appendChild($sitemap->createElement('changefreq', 'daily')); //Hourly, daily, weekly etc.
            $url->appendChild($sitemap->createElement('priority', '0.5'));     //1, 0.7, 0.5 ...

            $urlset->appendChild($url);
        }

        foreach ($films as $film) {

            $url = $sitemap->createElement('url');

            $link = '/film/'.$film->label;

            $href = $this->domain.$link;
            $url->appendChild($sitemap->createElement('loc', $href));
            $url->appendChild($sitemap->createElement('changefreq', 'daily')); //Hourly, daily, weekly etc.
            $url->appendChild($sitemap->createElement('priority', '0.8'));     //1, 0.7, 0.5 ...

            $urlset->appendChild($url);
        }

        foreach ($serials as $serial) {

            $url = $sitemap->createElement('url');

            $link = '/serial/'.$serial->label;

            $href = $this->domain.$link;
            $url->appendChild($sitemap->createElement('loc', $href));
            $url->appendChild($sitemap->createElement('changefreq', 'daily')); //Hourly, daily, weekly etc.
            $url->appendChild($sitemap->createElement('priority', '1'));     //1, 0.7, 0.5 ...

            $urlset->appendChild($url);
        }

        $sitemap->appendChild($urlset);

        $response->setContent($sitemap->saveXML());
        return $response;
    }

}