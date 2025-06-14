<?php

declare(strict_types=1);

use App\Domain\User\UserRepository;
use App\Domain\Wallet\WalletRepository;
use App\Domain\Transaction\TransactionRepository;
use App\Infrastructure\Database\Repositories\UserRepository as DatabaseUserRepository;
use App\Infrastructure\Database\Repositories\WalletRepository as DatabaseWalletRepository;
use App\Infrastructure\Database\Repositories\TransactionRepository as DatabaseTransactionRepository;
use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        UserRepository::class => \DI\autowire(DatabaseUserRepository::class),
        WalletRepository::class => \DI\autowire(DatabaseWalletRepository::class),
        TransactionRepository::class => \DI\autowire(DatabaseTransactionRepository::class),
    ]);
};
