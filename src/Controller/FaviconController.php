<?php
/**
 * User: massimilianocannarozzo
 * Date: 05/11/16
 * Time: 20:29
 */

namespace GPlayInfo\Controller;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;

class FaviconController
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function faviconAction()
    {
        $topApps = [];
        $guzzle = $this->app['service.guzzle'];

        try {
            $topApps = json_decode($guzzle->get('/topFreeApps')->getBody()->getContents(), true);
        } catch (ClientException $e) {
            $this->app['monolog']->addError($e->getMessage());
            $this->app->abort(500);
        } catch (RequestException $e) {
            $this->app['monolog']->addError($e->getMessage());
            $this->app->abort(500);
        }

        shuffle($topApps);

        return $this->app->redirect($topApps[0]['image']);
    }
}
