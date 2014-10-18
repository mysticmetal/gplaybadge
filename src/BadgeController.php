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
    private $fontColor;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->fontFilePath = __DIR__ . '/../res/fonts/Roboto-Regular.ttf';
        $this->fontColor = '#2196F3';
    }

    public function badgeAction()
    {
        $request = $this->app['request'];
        $guzzle = $this->app['guzzle_ws'];
        $utc = new \DateTimeZone('UTC');

        $packageid = $request->query->get('id');
        $lang = $request->query->get('lang');

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
            $response->setExpires(new \DateTime('now', $utc));
        } else {
            $now = new \DateTime('now', $utc);
            $eod = new \DateTime('23:59', $utc);

            $diff = $eod->diff($now);
            $age = $diff->s + ($diff->i + ($diff->h + $diff->d * 24) * 60) * 60;

            $response->setMaxAge($age);
            $response->setSharedMaxAge($age);
            $response->setExpires($eod);
            $response->setLastModified(new \DateTime('midnight', $utc));
        }

        if ($response->isNotModified($request)) {
            $response->setNotModified();
        } else {
            //Se devo scaricare l'img
            $wsResponse = null;

            $textStyle = function ($font) {
                $font->file($this->fontFilePath);
                $font->color($this->fontColor);
            };

            $img = Image::canvas(450, 130, '#FFFFFF');

            $query = [
                'id' => $packageid,
            ];

            if (isset($lang)) {
                $query['lang'] = $lang;
            }

            try {
                $jsonResponse = $guzzle->get('/applicationDetails', [
                    'query' => $query
                ])->json();
                $imgResponse = $guzzle->get($jsonResponse['icon']);

                $img->text("{$jsonResponse['name']} {$jsonResponse['versionName']}", 80, 20, $textStyle);
                $img->text("by {$jsonResponse['author']}", 80, 40, $textStyle);
                $img->text("{$jsonResponse['rating']['display']}/5.0 (" . number_format($jsonResponse['rating']['count']) . " ratings)", 80, 60, $textStyle);
                $img->text("{$jsonResponse['numDownloads']} downloads", 80, 80, $textStyle);
                $img->text("Published {$jsonResponse['datePublished']}", 80, 100, $textStyle);
                $img->insert(Image::make($imgResponse->getBody()->__toString())->resize(64, 64), 'top-left', 10, 10);
                $img->text("gplay.ws", 410, 125); //TODO Customize this text style

            } catch (RequestException $e) {
                $this->app->abort($e->getCode(), 'Error generating image');
                return false;
            }

            $response->setContent($img->encode('png'));
            $response->headers->set('Content-Type', 'image/png');
        }
        
        return $response;
    }
}