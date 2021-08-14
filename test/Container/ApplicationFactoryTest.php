<?php

namespace Antidot\Test\RoadRunner\Container;

use Antidot\Application\Http\Response\ErrorResponseGenerator;
use Antidot\Application\Http\RouteFactory;
use Antidot\Application\Http\Router;
use Antidot\Container\MiddlewareFactory;
use Antidot\Container\RequestFactory;
use Antidot\RoadRunner\Container\ApplicationFactory;
use Laminas\HttpHandlerRunner\Emitter\EmitterStack;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ApplicationFactoryTest extends TestCase
{
    public function testItCreatesRoadRunnerApplicationInstances(): void
    {
        $factory = new ApplicationFactory();
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(static::exactly(6))
            ->method('get')
            ->withConsecutive(
                [EmitterStack::class],
                [RequestFactory::class],
                [ErrorResponseGenerator::class],
                [Router::class],
                [MiddlewareFactory::class],
                [RouteFactory::class],
            )
            ->willReturnOnConsecutiveCalls(
                $this->createMock(EmitterStack::class),
                $this->createMock(RequestFactory::class),
                $this->createMock(ErrorResponseGenerator::class),
                $this->createMock(Router::class),
                $this->createMock(MiddlewareFactory::class),
                $this->createMock(RouteFactory::class),
            );


        $factory($container);
    }
}
