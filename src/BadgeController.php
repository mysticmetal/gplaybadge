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

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function badgeAction()
    {
        $packageid = $this->app['request']->get('id');
        $lang = $this->app['request']->get('lang');

        if (!isset($packageid)) {
            $this->app->abort(500, 'Invalid package id');
            return null;
        }

        if (!$this->app['debug']) {
            $res = null;
            try {
                $res = $this->app['guzzle']->get($this->app['ws.url'] . "?id={$packageid}&lang={$lang}", [
                    $this->app['ws.auth.header.name'] => $this->app['ws.auth.header.value']
                ])->send()->json();
            } catch (ClientErrorResponseException $e) {
                $this->app->abort($e->getResponse()->getStatusCode());
                return null;
            }

            $res = $this->app['guzzle']->get($res['icon'])->send();
//TODO Add Cache-Control header se !debug
            return new Response($res->getBody(), $res->getStatusCode(), ['content-type' => $res->getContentType()]);
        }

        return $this->app->sendFile(__DIR__ . '/../web/badge.png');
    }
}