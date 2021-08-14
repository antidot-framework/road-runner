<?php

declare(strict_types=1);

namespace Antidot\RoadRunner\Container;

use Antidot\Application\Http\Application;
use Antidot\Application\Http\Response\ErrorResponseGenerator;
use Antidot\Application\Http\RouteFactory;
use Antidot\Application\Http\Router;
use Antidot\Application\Http\WebServerApplication;
use Antidot\Container\MiddlewareFactory;
use Antidot\Container\RequestFactory;
use Antidot\RoadRunner\MiddlewarePipeline;
use Antidot\RoadRunner\RoadRunnerApplication;
use Laminas\HttpHandlerRunner\Emitter\EmitterInterface;
use Laminas\HttpHandlerRunner\Emitter\EmitterStack;
use Laminas\HttpHandlerRunner\RequestHandlerRunner;
use Psr\Container\ContainerInterface;

final class ApplicationFactory
{
    public function __invoke(ContainerInterface $container): Application
    {
        $pipeline = new MiddlewarePipeline();

        /** @var EmitterInterface $emitterStack */
        $emitterStack = $container->get(EmitterStack::class);
        /** @var RequestFactory $requestFactory */
        $requestFactory = $container->get(RequestFactory::class);
        /** @var ErrorResponseGenerator $errorResponseGenerator */
        $errorResponseGenerator = $container->get(ErrorResponseGenerator::class);
        $runner = new RequestHandlerRunner($pipeline, $emitterStack, $requestFactory(), $errorResponseGenerator);
        /** @var Router $router */
        $router = $container->get(Router::class);
        /** @var MiddlewareFactory $middleware */
        $middleware = $container->get(MiddlewareFactory::class);
        /** @var RouteFactory $routeFactory */
        $routeFactory = $container->get(RouteFactory::class);

        return new RoadRunnerApplication(
            new WebServerApplication($runner, $pipeline, $router, $middleware, $routeFactory),
            $pipeline,
            $errorResponseGenerator
        );
    }
}
