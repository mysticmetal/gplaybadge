<?php
/**
 * User: massimilianocannarozzo
 * Date: 29/08/14
 * Time: 23:32
 */

namespace GPlayInfo;

use Guzzle\Http\Exception\ClientErrorResponseException;
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
        $packageid = $this->app['request']->get('id');
        $lang = $this->app['request']->get('lang');

        if (!isset($packageid)) {
            //TODO Return an image with message
            $this->app->abort(500, 'Invalid package id');
            return null;
        }

        //TODO Generate 304 response if image is still valid!

        $wsResponse = null;
        try {
            $wsResponse = $this->app['guzzle']->get($this->app['ws.url'] . "?id={$packageid}&lang={$lang}", [
                $this->app['ws.auth.header.name'] => $this->app['ws.auth.header.value']
            ])->send();
        } catch (ClientErrorResponseException $e) {
            //TODO Return an image with message
            $this->app->abort($e->getResponse()->getStatusCode());
            return null;
        }

        $imgResponse = $this->app['guzzle']->get($wsResponse->json()['icon'])->send();

        return new Response($imgResponse->getBody(), $imgResponse->getStatusCode(), [
            'Cache-Control' => $this->app['debug'] ? 'no-cache' : $wsResponse->getCacheControl(),
            'Expires' => $this->app['debug'] ? '0' : $wsResponse->getExpires(),
            'Last-Modified' => $wsResponse->getLastModified(),
            'Content-Type' => $imgResponse->getContentType()
        ]);
    }
}