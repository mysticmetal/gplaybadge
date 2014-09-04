<?php

use GuzzleHttp\Client;
use Monolog\Handler\ErrorLogHandler;
use Silex\Application;
use \GPlayInfo\BadgeController;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Application();

$app['debug'] = false;

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

$app['security.encoder.digest'] = $app->share(function() {
    return new MessageDigestPasswordEncoder('sha256', false, 1);
});

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
    ->assert('lang', '[a-z]{2}')
    ->value('lang', 'en')
    ->bind('badge');

$app->run();
