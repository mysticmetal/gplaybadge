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
    private $FONT_FILE;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->FONT_FILE = __DIR__ . '/../res/fonts/RobotoCondensed-Light.ttf';
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
            } catch (RequestException $e) {
                //TODO Return an image with message
                $this->app->abort($e->getResponse()->getStatusCode(), $e->getResponse()->getMessage());
                return null;
            }

            $jsonResponse = $wsResponse->json();
            $imgResponse = $guzzle->get($jsonResponse['icon']);

            $textStyle = function ($font) {
                $font->file($this->FONT_FILE);
                $font->color('#0099CC');
            };

            $img = Image::canvas(450, 130, '#FFFFFF');
            $img->text($jsonResponse['name'] . ' ' . $jsonResponse['versionName'], 80, 20, $textStyle);
            $img->text('by ' . $jsonResponse['author'], 80, 40, $textStyle);
            $img->text($jsonResponse['rating']['display'], 80, 60, $textStyle);
            $img->text('(' . $jsonResponse['rating']['count'] . ' ratings)', 100, 60, $textStyle);
            $img->text($jsonResponse['numDownloads'] . ' downloads', 80, 80, $textStyle);
            $img->text('published ' . $jsonResponse['datePublished'], 80, 100, $textStyle);
            $img->insert(Image::make($imgResponse->getBody()->__toString())->resize(64, 64), 'top-left', 10, 10);
            $img->text('gplay.ws', 410, 125);

            $response->setContent($img->encode('png'));
            $response->headers->set('Content-Type', 'image/png');
        }
        
        return $response;
    }
}