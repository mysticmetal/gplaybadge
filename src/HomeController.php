<?php
/**
 * User: massimilianocannarozzo
 * Date: 05/10/14
 * Time: 20:29
 */

namespace GPlayInfo;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Silex\Application;

class HomeController
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function homeAction()
    {
        $topApps = [];
        $guzzle = $this->app['guzzle_ws'];

        try {
            $topApps = $guzzle->get('/topFreeApps')->json();
        } catch (ClientException $e) {
            print_r($e->getMessage());
        } catch (RequestException $e) {
            print_r($e->getMessage());
        }

        array_splice($topApps, 10);
        shuffle($topApps);

        return $this->app['twig']->render('home.twig', [
            'top_apps' => $topApps
        ]);
    }
} 