<?php

use Guzzle\Http\Client;
use Monolog\Handler\ErrorLogHandler;
use Silex\Application;
use \GPlayInfo\BadgeController;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\TwigServiceProvider;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Application();
$app['debug'] = false;

$app->register(new TwigServiceProvider(), [
    'twig.path' => __DIR__ . '/../res/views'
]);

$app->register(new MonologServiceProvider(), [
    'monolog.logfile' => '/dev/null',
    'monolog.name' => 'gplay.ws',
    'monolog.handler.debug' => new ErrorLogHandler()
]);

$app->register(new ServiceControllerServiceProvider());

$app['ws.auth.header.name'] = 'X-Mashape-Key';
$app['ws.auth.header.value'] = getenv('MASHAPE_KEY');
$app['ws.url'] = 'https://gplaystore.p.mashape.com/applicationDetails';

$app['ws.useragent'] = 'gplay.ws/1.0';

$app['controllers.badge'] = $app->share(function() use ($app) {
    return new BadgeController($app);
});

$app['guzzle'] = function () use ($app) {
    $g = new Client([
        'defaults' => [
            'headers' => ['User-Agent' => $app['ws.useragent'] ]
        ]
    ]);
    return $g;
};

//Routes
$app->get('/', function () use ($app) {
    return $app['twig']->render('home.twig');
})->bind('home');

$app->get('/badge/', 'controllers.badge:badgeAction')
    ->assert('id', '[\w\.]+')
    ->assert('lang', '[a-z]{2}')
    ->value('lang', 'en')
    ->bind('badge');

$app->run();
