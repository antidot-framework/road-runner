<?php

declare(strict_types=1);

namespace Antidot\RoadRunner;

use Antidot\Application\Http\Handler\NextHandler;
use Antidot\Application\Http\Middleware\MiddlewareQueue;
use Antidot\Application\Http\Middleware\Pipeline;
use Antidot\Application\Http\Middleware\SyncMiddlewareQueue;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;
use Throwable;

final class MiddlewarePipeline implements Pipeline
{
    /** @var array<MiddlewareQueue> */
    public array $concurrentPipelines;
    /** @var array<MiddlewareInterface> */
    private array $middlewareCollection;

    /**
     * @param array<MiddlewareInterface> $middlewareCollection
     * @param array<MiddlewareQueue> $concurrentPipelines
     */
    public function __construct(
        array $middlewareCollection = [],
        array $concurrentPipelines = []
    ) {
        $this->concurrentPipelines = $concurrentPipelines;
        $this->middlewareCollection = $middlewareCollection;
    }

    public function pipe(MiddlewareInterface $middleware): void
    {
        $this->middlewareCollection[] = $middleware;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var string $requestId */
        $requestId = $request->getAttribute('request_id');
        $this->setCurrentPipeline($requestId);

        try {
            $middleware = $this->concurrentPipelines[$requestId]->dequeue();

            $response = $middleware->process($request, $this);
            unset($this->concurrentPipelines[$requestId]);

            return $response;
        } catch (Throwable $exception) {
            unset($this->concurrentPipelines[$requestId]);

            throw $exception;
        }
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var ?string $requestId */
        $requestId = $request->getAttribute('request_id');
        if (!$requestId) {
            $requestId = Uuid::uuid4()->toString();
            $request = $request->withAttribute('request_id', $requestId);
        }

        try {
            $queue = $this->concurrentPipelines[$requestId];
            $next = new NextHandler($queue, $handler);

            return $next->handle($request);
        } catch (Throwable $exception) {
            unset($this->concurrentPipelines[$requestId]);

            throw $exception;
        }
    }

    private function setCurrentPipeline(string $requestId): void
    {
        if (empty($this->concurrentPipelines[$requestId])) {
            $queue = new SyncMiddlewareQueue();
            foreach ($this->middlewareCollection as $middlewareName) {
                $queue->enqueue($middlewareName);
            }
            $this->concurrentPipelines[$requestId] = $queue;
        }
    }
}
