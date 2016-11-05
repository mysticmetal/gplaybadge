<?php
/**
 * User: massimilianocannarozzo
 * Date: 29/08/14
 * Time: 23:32
 */

namespace GPlayInfo\Controller;

use GPlayInfo\Service\BadgeGenerator;
use GuzzleHttp\Exception\RequestException;
use Silex\Application;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

const MIMETYPE = 'image/png';

class BadgeController
{
    protected $app;
    /** @var BadgeGenerator */
    private $badgeGenerator;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->badgeGenerator = $this->app['service.generator'];
    }

    /**
     * @return Response
     */
    public function badgeAction()
    {
        /** @var Request $request */
        $request = $this->app['request_stack']->getCurrentRequest();
        $utc = new \DateTimeZone('UTC');

        /** @var ParameterBag $query */
        $query = $request->query;

        if (!$query->has('id')) {
            $this->app->abort(400, 'Missing package id');
            return null;
        }

        $packageId = $query->get('id');
        $lang = $query->has('lang') ? $query->get('lang') : 'en';

        $response = new Response();

        $now = new \DateTime('now', $utc);
        $eod = new \DateTime('23:59', $utc);

        $diff = $eod->diff($now);
        $age = $diff->s + ($diff->i + ($diff->h + $diff->d * 24) * 60) * 60;

        $response->setMaxAge($age);
        $response->setSharedMaxAge($age);
        $response->setExpires($eod);
        $response->setLastModified(new \DateTime('midnight', $utc));

        if ($this->app['debug']) {
            $response->headers->addCacheControlDirective('no-cache');
            $response->setPrivate();
            $response->setMaxAge(0);
            $response->setSharedMaxAge(0);
            $response->setExpires(new \DateTime('now', $utc));
        }

        if ($response->isNotModified($request)) {
            $response->setNotModified();
            return $response;
        }

        try {
            $img = $this->badgeGenerator->generate($packageId, $lang, $request->getHost());
            $response->setContent($img->encode(MIMETYPE));
            $response->headers->set('Content-Type', MIMETYPE);
        } catch (RequestException $e) {
            $this->app->abort($e->getCode(), 'Error getting image data');
        } catch (\Exception $e) {
            $response->setStatusCode(500);
        }

        return $response;
    }
}
