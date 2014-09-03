<?php
/**
 * User: massimilianocannarozzo
 * Date: 29/08/14
 * Time: 23:32
 */

namespace GPlayInfo;

use Guzzle\Http\Exception\ClientErrorResponseException;
use Guzzle\Http\Exception\CurlException;
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;

class BadgeController
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function badgeAction()
    {
        $request = $this->app['request'];
        $guzzle = $this->app['guzzle'];

        $packageid = $request->get('id');
        $lang = $this->app['request']->get('lang');

        if (!isset($packageid)) {
            //TODO Return an image with message
            $this->app->abort(500, 'Invalid package id');
            return null;
        }

        $response = new Response();

        if ($this->app['debug']) {
            $response->headers->addCacheControlDirective('no-cache');
            $response->setPrivate();
            $response->setMaxAge(0);
            $response->setSharedMaxAge(0);
            $response->setExpires(new \DateTime('now', new \DateTimeZone('UTC')));
        } else {
            $now = new \DateTime('now', new \DateTimeZone('UTC'));
            $eod = new \DateTime('23:59', new \DateTimeZone('UTC'));

            $diff = $eod->diff($now);
            $age = $diff->s + ($diff->i + ($diff->h + $diff->d * 24) * 60) * 60;

            $response->setMaxAge($age);
            $response->setSharedMaxAge($age);
            $response->setExpires($eod);
            $response->setLastModified(new \DateTime('midnight', new \DateTimeZone('UTC')));
        }

        if ($response->isNotModified($request)) {
            $response->setNotModified();
        } else {
            //Se devo scaricare l'img
            $wsResponse = null;
            try {
                $wsResponse = $guzzle->get($this->app['ws.url'], [
                    'headers' => [
                        $this->app['ws.auth.header.name'] => $this->app['ws.auth.header.value']
                    ],
                    'query' => [
                        'id' => $packageid,
                        'lang' => $lang
                    ]
                ]);
            } catch (ClientErrorResponseException $e) {
                //TODO Return an image with message
                $this->app->abort($e->getResponse()->getStatusCode(), $e->getResponse()->getMessage());
                return null;
            } catch (CurlException $e) {
                //TODO Return an image with message
                $this->app->abort($e->getResponse()->getStatusCode(), $e->getResponse()->getMessage());
                return null;
            }

            $imgResponse = $guzzle->get($wsResponse->json()['icon'])->send();
            //TODO Image editing
            $response->setContent($imgResponse->getBody());
            $response->headers->set('Content-Type', $imgResponse->getContentType());
            $imgResponse = $guzzle->get($wsResponse->json()['icon']);

        }
        
        return $response;
    }
}