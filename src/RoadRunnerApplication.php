<?php

declare(strict_types=1);

namespace Antidot\RoadRunner;

use Antidot\Application\Http\Application;
use Antidot\Application\Http\Middleware\Pipeline;
use Antidot\Application\Http\Response\ErrorResponseGenerator;
use Antidot\Application\Http\WebServerApplication;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;
use RuntimeException;
use Throwable;

final class RoadRunnerApplication implements Application, RequestHandlerInterface
{
    private ErrorResponseGenerator $errorResponseGenerator;
    private WebServerApplication $application;
    private Pipeline $pipeline;

    public function __construct(
        WebServerApplication $application,
        Pipeline $pipeline,
        ErrorResponseGenerator $errorResponseGenerator
    ) {
        $this->errorResponseGenerator = $errorResponseGenerator;
        $this->application = $application;
        $this->pipeline = $pipeline;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            return $this->pipeline->handle($request->withAttribute('request_id', Uuid::uuid4()->toString()));
        } catch (Throwable $exception) {
            return $this->errorResponseGenerator->__invoke($exception);
        }
    }

    public function run(): void
    {
        throw new RuntimeException('Connot run Road Runner Application out of Road Runner server.');
    }

    public function pipe(string $middlewareName): void
    {
        $this->application->pipe($middlewareName);
    }

    public function get(string $uri, array $middleware, string $name): void
    {
        $this->application->get($uri, $middleware, $name);
    }

    public function post(string $uri, array $middleware, string $name): void
    {
        $this->application->post($uri, $middleware, $name);
    }

    public function patch(string $uri, array $middleware, string $name): void
    {
        $this->patch($uri, $middleware, $name);
    }

    public function put(string $uri, array $middleware, string $name): void
    {
        $this->application->put($uri, $middleware, $name);
    }

    public function delete(string $uri, array $middleware, string $name): void
    {
        $this->application->delete($uri, $middleware, $name);
    }

    public function options(string $uri, array $middleware, string $name): void
    {
        $this->application->options($uri, $middleware, $name);
    }

    public function route(string $uri, array $middleware, array $methods, string $name): void
    {
        $this->application->route($uri, $middleware, $methods, $name);
    }
}
