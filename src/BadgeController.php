<?php
/**
 * User: massimilianocannarozzo
 * Date: 29/08/14
 * Time: 23:32
 */

namespace GPlayInfo;

use GuzzleHttp\Exception\RequestException;
use Intervention\Image\ImageManagerStatic as Image;
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;

class BadgeController
{
    protected $app;
    private $fontFilePath;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->$fontFilePath = __DIR__ . '/../res/fonts/RobotoCondensed-Light.ttf';
    }

    public function badgeAction()
    {
        $request = $this->app['request'];
        $guzzle = $this->app['guzzle'];

        $packageid = $request->get('id');
        $lang = $this->app['request']->get('lang');

        if (!isset($packageid)) {
            $this->app->abort(400, 'Missing package id');
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

            $textStyle = function ($font) {
                $font->file($this->$fontFilePath);
                $font->color('#0099CC');
            };

            $img = Image::canvas(450, 130, '#FFFFFF');

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

                $jsonResponse = $wsResponse->json();
                $imgResponse = $guzzle->get($jsonResponse['icon']);

                $img->text($jsonResponse['name'], 80, 20, $textStyle);
                $img->text('by ' . $jsonResponse['author'], 80, 40, $textStyle);
                $img->text($jsonResponse['rating']['display'] . '/5.0', 80, 60, $textStyle);
                $img->text('(' . $jsonResponse['rating']['count'] . ' ratings)', 120, 60, $textStyle);
                $img->text($jsonResponse['numDownloads'] . ' downloads', 80, 80, $textStyle);
                $img->text('published ' . $jsonResponse['datePublished'], 80, 100, $textStyle);
                $img->insert(Image::make($imgResponse->getBody()->__toString())->resize(64, 64), 'top-left', 10, 10);
                $img->text('gplay.ws', 410, 125);

            } catch (RequestException $e) {
                if($e->getCode() == 404){
                    $img->text($e->getResponse()->json()['error'], 80, 20, $textStyle);
                } else {
                    $this->app->abort($e->getCode(), 'Error generating image');
                    return false;
                }
            }

            $response->setContent($img->encode('png'));
            $response->headers->set('Content-Type', 'image/png');
        }
        
        return $response;
    }
}