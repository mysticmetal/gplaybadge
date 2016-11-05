<?php

/**
 * Created by PhpStorm.
 * User: massimilianocannarozzo
 * Date: 05/11/16
 * Time: 20:27
 */

namespace GPlayInfo\Service;

use Intervention\Image\ImageManager;
use Silex\Application;

const FONT_COLOR_HEAD = '#000000';
const FONT_COLOR_FIELD = '#666666';
const FONT_COLOR_PRICE = '#FFFFFF';
const FONT_SIZE_HEAD = 18;
const FONT_SIZE_FIELD = 14;
const FONT_SIZE_WATERMARK = 9;
const ICON_SIZE = 64;
const MARGIN = 10;
const FONT_FILE_PATH = __DIR__ . '/../res/fonts/Roboto-Regular.ttf';
const BACKGROUND_FILE_PATH = __DIR__ . '/../res/images/badge_bg.png';

class BadgeGenerator
{
    protected $app;
    protected $textStyleHead;
    protected $textStyleField;
    protected $textStyleWatermark;
    protected $textStylePrice;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->dataFetcher = $this->app['service.fetcher'];
        $this->imageManager = new ImageManager(['driver' => 'imagick']);

        $this->textStyleHead = function ($font) {
            /** @var \Intervention\Image\AbstractFont $font */
            $font->file(FONT_FILE_PATH);
            $font->color(FONT_COLOR_HEAD);
            $font->size(FONT_SIZE_HEAD);
            $font->align('left');
            $font->valign('top');
        };

        $this->textStyleField = function ($font) {
            /** @var \Intervention\Image\AbstractFont $font */
            $font->file(FONT_FILE_PATH);
            $font->color(FONT_COLOR_FIELD);
            $font->size(FONT_SIZE_FIELD);
            $font->align('left');
            $font->valign('top');
        };

        $this->textStyleWatermark = function ($font) {
            /** @var \Intervention\Image\AbstractFont $font */
            $font->file(FONT_FILE_PATH);
            $font->color(FONT_COLOR_FIELD);
            $font->size(FONT_SIZE_WATERMARK);
            $font->align('right');
            $font->valign('bottom');
        };

        $this->textStylePrice = function ($font) {
            /** @var \Intervention\Image\AbstractFont $font */
            $font->file(FONT_FILE_PATH);
            $font->color(FONT_COLOR_PRICE);
            $font->size(FONT_SIZE_FIELD);
            $font->align('center');
            $font->valign('top');
        };
    }

    public function generate($packageId, $lang, $hostname)
    {
        $appDetail = $this->dataFetcher->fetch($packageId, $lang);

        /** @var \Intervention\Image\Image $img */
        $img = $this->imageManager->make(BACKGROUND_FILE_PATH);

        $img->text(
            $hostname,
            $img->getWidth() - 1.5 * MARGIN,
            $img->getHeight() - 1.5 * MARGIN,
            $this->textStyleWatermark
        );

        $img->insert(
            $this->imageManager->make($appDetail['icon'])->resize(ICON_SIZE, ICON_SIZE),
            'top-left',
            1.2 * MARGIN,
            1.5 * MARGIN
        );

        $img->text($appDetail['name'], ICON_SIZE + 2 * MARGIN, 1.5 * MARGIN, $this->textStyleHead);

        $img->text(
            "by {$appDetail['author']}\n" .
            $appDetail['rating']['display'] . "/5.0 " .
            "(" . number_format($appDetail['rating']['count']) . " ratings)\n" .
            $appDetail['numDownloads'] . " downloads\n" .
            "Last updated " . strtolower($appDetail['datePublished']),
            ICON_SIZE + 2 * MARGIN,
            40,
            $this->textStyleField
        );

        $img->text(
            $appDetail['price'] > 0 ? $appDetail['currency'] . " " . number_format($appDetail['price'], 2) : 'FREE',
            $img->getWidth() - 50,
            2 * MARGIN,
            $this->textStylePrice
        );

        return $img;
    }
}
