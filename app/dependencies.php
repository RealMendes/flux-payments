<?php

declare(strict_types=1);

use App\Application\Settings\SettingsInterface;
use App\Application\Actions\User\RegisterUserAction;
use App\Application\Actions\Wallet\GetBalanceAction;
use App\Application\Actions\Transaction\ExecuteTransactionAction;
use App\Domain\User\UserService;
use App\Domain\User\UserRepository;
use App\Domain\Wallet\WalletService;
use App\Domain\Wallet\WalletRepository;
use App\Domain\Transaction\TransactionService;
use App\Domain\Transaction\TransactionRepository;
use App\Infrastructure\ExternalServices\AuthorizerService;
use App\Infrastructure\ExternalServices\NotificationService;
use App\Infrastructure\Database\DatabaseConnection;
use GuzzleHttp\Client;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);

            $loggerSettings = $settings->get('logger');
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },

        // PDO Database Connection
        \PDO::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);
            return DatabaseConnection::getConnection($settings);
        },

        // HTTP Client for External Services
        Client::class => function (ContainerInterface $c) {
            return new Client([
                'timeout' => 10,
                'connect_timeout' => 5,
            ]);
        },

        // External Services
        AuthorizerService::class => function (ContainerInterface $c) {
            return new AuthorizerService(
                $c->get(Client::class),
                $_ENV['AUTHORIZER_API_URL'] ?? '',
                $c->get(LoggerInterface::class)
            );
        },        
        NotificationService::class => function (ContainerInterface $c) {
            return new NotificationService(
                $c->get(Client::class),
                $_ENV['NOTIFICATION_API_URL'] ?? '',
                $c->get(LoggerInterface::class)
            );
        },        
        // Domain Services
        UserService::class => function (ContainerInterface $c) {
            return new UserService(
                $c->get(UserRepository::class),
                $c->get(WalletRepository::class)
            );
        },

        WalletService::class => function (ContainerInterface $c) {
            return new WalletService(
                $c->get(WalletRepository::class)
            );
        },        
        TransactionService::class => function (ContainerInterface $c) {
            return new TransactionService(
                $c->get(UserRepository::class),
                $c->get(WalletRepository::class),
                $c->get(TransactionRepository::class),
                $c->get(AuthorizerService::class),
                $c->get(NotificationService::class),
                $c->get(\PDO::class)
            );
        },
        // Actions
        RegisterUserAction::class => function (ContainerInterface $c) {
            return new RegisterUserAction(
                $c->get(LoggerInterface::class),
                $c->get(UserService::class)
            );
        },
        GetBalanceAction::class => function (ContainerInterface $c) {
            return new GetBalanceAction(
                $c->get(LoggerInterface::class),
                $c->get(WalletService::class)
            );
        },       
        ExecuteTransactionAction::class => function (ContainerInterface $c) {
            return new ExecuteTransactionAction(
                $c->get(LoggerInterface::class),
                $c->get(TransactionService::class)
            );
        },
    ]);
};
