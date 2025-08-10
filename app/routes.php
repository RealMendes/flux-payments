<?php

declare(strict_types=1);

use App\Application\Actions\User\RegisterUserAction;
use App\Application\Actions\Wallet\GetBalanceAction;
use App\Application\Actions\Transaction\ExecuteTransactionAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write('Hello world! Flux API is running.');
        return $response;
    });
    $app->group('/api/v1', function (Group $group) {
        // Rotas de usuários
        $group->post('/users', RegisterUserAction::class);

        // Rotas de carteiras
        $group->get('/wallets/{user_id}/balance', GetBalanceAction::class);

        // Rotas de transações
        $group->post('/transaction', ExecuteTransactionAction::class);
    });
};
