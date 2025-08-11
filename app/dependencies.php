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
use App\Domain\Transaction\TransactionManagementService;
use App\Domain\Gateways\PaymentAuthorizationGateway;
use App\Domain\Gateways\NotificationGateway;
use App\Infrastructure\Adapters\ExternalPaymentAuthorizationAdapter;
use App\Infrastructure\Adapters\HttpNotificationServiceAdapter;
use App\Infrastructure\Adapters\DatabaseTransactionManagerAdapter;
use App\Infrastructure\Database\DatabaseConnection;
use GuzzleHttp\Client;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use App\Domain\Repositories\DatabaseTransactionManager;
use App\Domain\User\UserManagementService;
use App\Domain\Wallet\WalletManagementService;

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

        // Gateways e Serviços Externos (Driven Adapters)
        PaymentAuthorizationGateway::class => function (ContainerInterface $c) {
            return new ExternalPaymentAuthorizationAdapter(
                $c->get(Client::class),
                $_ENV['AUTHORIZER_API_URL'] ?? '',
                $c->get(LoggerInterface::class)
            );
        },
        NotificationGateway::class => function (ContainerInterface $c) {
            return new HttpNotificationServiceAdapter(
                $c->get(Client::class),
                $_ENV['NOTIFICATION_API_URL'] ?? '',
                $c->get(LoggerInterface::class)
            );
        },
        DatabaseTransactionManager::class => function (ContainerInterface $c) {
            return new DatabaseTransactionManagerAdapter(
                $c->get(\PDO::class),
                $c->get(LoggerInterface::class)
            );
        },

        // Serviços de Domínio (Domain Services)
        TransactionManagementService::class => \DI\autowire(TransactionService::class),
        // Domain Services
        UserService::class => function (ContainerInterface $c) {
            return new UserService(
                $c->get(UserRepository::class),
                $c->get(WalletRepository::class)
            );
        },
        UserManagementService::class => \DI\get(UserService::class), // alias

        WalletService::class => function (ContainerInterface $c) {
            return new WalletService(
                $c->get(WalletRepository::class)
            );
        },
        WalletManagementService::class => \DI\get(WalletService::class), // alias
        TransactionService::class => function (ContainerInterface $c) {
            return new TransactionService(
                $c->get(UserRepository::class),
                $c->get(WalletRepository::class),
                $c->get(TransactionRepository::class),
                $c->get(PaymentAuthorizationGateway::class),
                $c->get(NotificationGateway::class),
                $c->get(DatabaseTransactionManager::class),
                $c->get(LoggerInterface::class)
            );
        },
        // Actions
        RegisterUserAction::class => function (ContainerInterface $c) {
            return new RegisterUserAction(
                $c->get(LoggerInterface::class),
                $c->get(UserManagementService::class) // updated
            );
        },
        GetBalanceAction::class => function (ContainerInterface $c) {
            return new GetBalanceAction(
                $c->get(LoggerInterface::class),
                $c->get(WalletManagementService::class) // updated
            );
        },
        ExecuteTransactionAction::class => function (ContainerInterface $c) {
            return new ExecuteTransactionAction(
                $c->get(LoggerInterface::class),
                $c->get(TransactionManagementService::class)
            );
        },
    ]);
};
