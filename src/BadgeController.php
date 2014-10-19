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

const FONT_COLOR_HEAD = '#000000';
const FONT_COLOR_FIELD = '#666666';
const FONT_COLOR_PRICE = '#FFFFFF';
const FONT_SIZE_HEAD = 16;
const FONT_SIZE_FIELD = 14;
const FONT_SIZE_WATERMARK = 9;
const ICON_SIZE = 64;
const MARGIN = 10;
const FONT_FILE_PATH = __DIR__ . '/../res/fonts/Roboto-Regular.ttf';
const BACKGROUND_FILE_PATH = __DIR__ . '/../res/images/badge_bg.png';

class BadgeController
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @return Response
     */
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

            /** @var $font \Intervention\Image\AbstractFont */
            $textStyleHead = function ($font) {
                $font->file(FONT_FILE_PATH);
                $font->color(FONT_COLOR_HEAD);
                $font->size(FONT_SIZE_HEAD);
                $font->align('left');
                $font->valign('top');
            };

            /** @var $font \Intervention\Image\AbstractFont */
            $textStyleField = function ($font) {
                $font->file(FONT_FILE_PATH);
                $font->color(FONT_COLOR_FIELD);
                $font->size(FONT_SIZE_FIELD);
                $font->align('left');
                $font->valign('top');
            };

            /** @var $font \Intervention\Image\AbstractFont */
            $textStyleWatermark = function ($font) {
                $font->file(FONT_FILE_PATH);
                $font->color(FONT_COLOR_FIELD);
                $font->size(FONT_SIZE_WATERMARK);
                $font->align('right');
                $font->valign('bottom');
            };

            /** @var $font \Intervention\Image\AbstractFont */
            $textStylePrice = function ($font) {
                $font->file(FONT_FILE_PATH);
                $font->color(FONT_COLOR_PRICE);
                $font->size(FONT_SIZE_FIELD);
                $font->align('center');
                $font->valign('top');
            };

            /** @var $img \Intervention\Image\Image */
            $img = Image::make(BACKGROUND_FILE_PATH);

            $img->text($request->getHost(), $img->getWidth() - 1.5 * MARGIN,
                $img->getHeight() - 1.5 * MARGIN, $textStyleWatermark);

            try {
                $appDetail = $guzzle->get('/applicationDetails', [
                    'query' => [
                        'id' => $packageid,
                        'lang' => $lang
                    ]
                ])->json();

                if (strlen($appDetail['name']) > 35) {
                    $appDetail['name'] = substr($appDetail['name'], 0, 30) . '...';
                }

                $img->insert(Image::make($appDetail['icon'])->resize(ICON_SIZE, ICON_SIZE), 'top-left',
                    1.2 * MARGIN, 1.5 * MARGIN);

                $img->text($appDetail['name'], ICON_SIZE + 2 * MARGIN, 1.5 * MARGIN, $textStyleHead);

                $img->text("by {$appDetail['author']}\n" .
                    $appDetail['rating']['display'] . "/5.0 " .
                    "(" . number_format($appDetail['rating']['count']) . " ratings)\n" .
                    $appDetail['numDownloads'] . " downloads\n" .
                    "Last updated " . strtolower($appDetail['datePublished']),
                    ICON_SIZE + 2 * MARGIN, 35, $textStyleField);

                $img->text($appDetail['price'] > 0 ?
                        $appDetail['currency'] . " " . number_format($appDetail['price'], 2) : 'FREE',
                    $img->getWidth() - 50, 2 * MARGIN, $textStylePrice);

            } catch (RequestException $e) {
                $this->app->abort($e->getCode(), 'Error getting image data');
                return false;
            } catch (\Exception $e) {
                $img->text('Error generating image', ICON_SIZE + 2 * MARGIN,
                    $img->getHeight() - 1.5 * MARGIN, $textStyleField);
                $response->setStatusCode(500);
            }

            $response->setContent($img->encode('png'));
            $response->headers->set('Content-Type', 'image/png');
        }
        
        return $response;
    }
}