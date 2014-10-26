<?php

use GPlayInfo\HomeController;
use GuzzleHttp\Client;
use Monolog\Handler\ErrorLogHandler;
use Silex\Application;
use \GPlayInfo\BadgeController;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Application();

$app['debug'] = getenv('ENVIRONMENT') == 'dev';

$app['security.firewalls'] = [
    'security.firewalls' => [
        'global' => [
            'pattern' => '^/$',
            'http' => [
                'real_name' => 'gplay.ws'
            ],
            'security' => true,
            'anonymous' => $app['debug'],
            'users' => [
                'demo' => ['ROLE_ADMIN',  'd1f225bc8368365c13341aa09d2cfcd03c8b347facc9048e4a4ec9cc198a489b']
            ]
        ]
    ]
];

$app->register(new TwigServiceProvider(), [
    'twig.path' => __DIR__ . '/../res/views'
]);

$app->register(new MonologServiceProvider(), [
    'monolog.logfile' => '/dev/null',
    'monolog.name' => 'gplay.ws',
    'monolog.handler' => new ErrorLogHandler()
]);

$app->register(new ServiceControllerServiceProvider());

$app->register(new UrlGeneratorServiceProvider());

$app->register(new SecurityServiceProvider(), $app['security.firewalls']);

$app['security.encoder.digest'] = $app->share(function () {
    return new MessageDigestPasswordEncoder('sha256', false, 1);
});

$app['ws.auth.header.name'] = 'X-Mashape-Key';
$app['ws.auth.header.value'] = getenv('MASHAPE_KEY');
$app['ws.url'] = 'https://gplaystore.p.mashape.com/applicationDetails';
$app['ws.useragent'] = 'gplay.ws/1.0';

$app['controllers.badge'] = $app->share(function () use ($app) {
    return new BadgeController($app);
});

$app['controllers.home'] = $app->share(function () use ($app) {
    return new HomeController($app);
});

$app['guzzle_ws'] = $app->share(function () use ($app) {
    $g = new Client([
        'base_url' => $app['ws.url'],
        'defaults' => [
            'headers' => [
                'X-Mashape-User' => $app['ws.user'],
                'X-Mashape-Subscription' => 'MEGA',
                'X-Forwarded-For' => $app['request']->getClientIp(),
                'User-Agent' => $app['request']->headers->get('User-Agent'),
                $app['ws.auth.header.name'] => $app['ws.auth.header.value']
            ]
        ]
    ]);
    return $g;
});

//Error handler
$app->error(function (\Exception $e, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    switch ($code) {
        case 404:
            $message = 'Sorry, the page you are looking for could not be found.';
            break;
        default:
            $message = 'We are sorry, but something went terribly wrong.';
    }

    return $app['twig']->render('error.twig', ['message' => $message]);
});

//Routes
$app->get('/', 'controllers.home:homeAction')
    ->bind('home');

$app->get('/badge/', 'controllers.badge:badgeAction')
    ->assert('lang', '[a-z]{2}')
    ->bind('badge');

$app->get('/favicon.ico', 'controllers.home:faviconAction');

//Enable heroku reverse proxy
if ($app['debug']) {
    Request::setTrustedProxies([$_SERVER['REMOTE_ADDR']]);
}

$app->run();
