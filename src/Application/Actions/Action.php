<?php

declare(strict_types=1);

namespace App\Application\Actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

abstract class Action
{
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @throws \Throwable
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        try {
            return $this->action($request, $response, $args);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * @throws \Throwable
     */
    abstract protected function action(Request $request, Response $response, array $args): Response;

    /**
     * @return array|object
     */
    protected function getFormData(Request $request)
    {
        return $request->getParsedBody();
    }

    /**
     * @return mixed
     * @throws \RuntimeException
     */
    protected function resolveArg(string $name, array $args)
    {
        if (!isset($args[$name])) {
            throw new \RuntimeException("Could not resolve argument `{$name}`.");
        }

        return $args[$name];
    }

    /**
     * @param array|object|null $data
     */
    protected function respondWithData(Response $response, $data = null, int $statusCode = 200): Response
    {
        $payload = new ActionPayload($statusCode, $data);

        return $this->respond($response, $payload);
    }

    protected function respond(Response $response, ActionPayload $payload): Response
    {
        $json = json_encode($payload, JSON_PRETTY_PRINT);
        $response->getBody()->write($json);

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($payload->getStatusCode());
    }
}
