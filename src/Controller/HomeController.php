<?php
/**
 * User: massimilianocannarozzo
 * Date: 05/10/14
 * Time: 20:29
 */

namespace GPlayInfo\Controller;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;

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
        array_splice($topApps, 5);

        return (new Response($this->app['twig']->render('home.twig', [
            'top_apps' => $topApps,
            'ua' => getenv('UA'),
        ])))
            ->setExpires(new \DateTime('now +1 day'))
            ->setMaxAge(24 * 60 * 60)
            ->setSharedMaxAge(24 * 60 * 60);
    }
}
