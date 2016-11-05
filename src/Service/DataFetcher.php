<?php
/**
 * Created by PhpStorm.
 * User: massimilianocannarozzo
 * Date: 05/11/16
 * Time: 20:34
 */

namespace GPlayInfo\Service;

use Silex\Application;

class DataFetcher
{
    protected $app;
    protected $guzzle;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->guzzle = $this->app['service.guzzle'];
    }

    public function fetch($packageId, $lang)
    {
        $appDetail = json_decode($this->guzzle->get('/applicationDetails', [
            'query' => [
                'id' => $packageId,
                'lang' => $lang
            ]
        ])->getBody()->getContents(), true);

        if (strlen($appDetail['name']) > 33) {
            $appDetail['name'] = substr($appDetail['name'], 0, 30) . '...';
        }

        if (strlen($appDetail['author']) > 33) {
            $appDetail['author'] = substr($appDetail['author'], 0, 30) . '...';
        }

        return $appDetail;
    }
}
