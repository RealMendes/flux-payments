<?php

declare(strict_types=1);

namespace App\Domain\Transaction;

use App\Application\DTO\TransactionRequestDTO;
use App\Domain\User\User;
use App\Domain\User\UserRepository;
use App\Domain\Wallet\WalletRepository;
use App\Domain\Wallet\WalletNotFoundException;
use App\Domain\Services\TransactionManagementService;
use App\Domain\Gateways\PaymentAuthorizationGateway;
use App\Domain\Services\NotificationService;
use App\Domain\Repositories\DatabaseTransactionManager;
use App\Domain\Exceptions\UserNotFoundException;
use App\Domain\Exceptions\UnauthorizedTransactionException;
use App\Domain\Exceptions\InsufficientBalanceException;

class TransactionService implements TransactionManagementService
{    private UserRepository $userRepository;
    private WalletRepository $walletRepository;
    private TransactionRepository $transactionRepository;
    private PaymentAuthorizationGateway $authorizationGateway;
    private NotificationService $notificationService;
    private DatabaseTransactionManager $databaseTransactionManager;

    public function __construct(
        UserRepository $userRepository,
        WalletRepository $walletRepository,
        TransactionRepository $transactionRepository,
        PaymentAuthorizationGateway $authorizationGateway,
        NotificationService $notificationService,
        DatabaseTransactionManager $databaseTransactionManager
    ) {        $this->userRepository = $userRepository;
        $this->walletRepository = $walletRepository;
        $this->transactionRepository = $transactionRepository;
        $this->authorizationGateway = $authorizationGateway;
        $this->notificationService = $notificationService;
        $this->databaseTransactionManager = $databaseTransactionManager;
    }    /**
     * Executa uma transação entre usuários
     *
     * @param TransactionRequestDTO $dto
     * @return Transaction
     * @throws \Exception
     */    
    public function executeTransaction(TransactionRequestDTO $dto): Transaction
    {
        return $this->execute($dto);
    }

    /**
     * Consulta o histórico de transações de um usuário
     *
     * @param int $userId ID do usuário
     * @return Transaction[] Lista de transações
     * @throws \Exception Se houver erro na consulta
     */
    public function getUserTransactionHistory(int $userId): array
    {
        try {
            return $this->transactionRepository->findByUserId($userId);
        } catch (\Exception $e) {
            throw new \Exception('Erro ao consultar histórico de transações: ' . $e->getMessage());
        }
    }

    /**
     * Executa uma transação entre usuários (método interno)
     *
     * @param TransactionRequestDTO $dto
     * @return Transaction
     * @throws \Exception
     */    
    private function execute(TransactionRequestDTO $dto): Transaction
    {
        try {
            $payer = $this->userRepository->findUserOfId($dto->getPayerId());
            $payee = $this->userRepository->findUserOfId($dto->getPayeeId());
        } catch (\App\Domain\User\UserNotFoundException $e) {
            throw UserNotFoundException::byId($dto->getPayerId());
        }

        if ($dto->getPayerId() === $dto->getPayeeId()) {
            throw UnauthorizedTransactionException::sameUserTransaction();
        }

        if ($payer->isMerchant()) {
            throw UnauthorizedTransactionException::merchantCannotTransact();
        }

        try {
            $payerWallet = $this->walletRepository->findByUserId($dto->getPayerId());
            $payeeWallet = $this->walletRepository->findByUserId($dto->getPayeeId());
        } catch (WalletNotFoundException $e) {
            throw UserNotFoundException::withoutWallet($dto->getPayerId());
        }

        if ($dto->getValue() <= 0) {
            throw UnauthorizedTransactionException::invalidTransactionAmount();
        }
        
        if ($payerWallet->getBalance() < $dto->getValue()) {
            throw new InsufficientBalanceException($payerWallet->getBalance(), $dto->getValue());
        }        $authorizationData = [
            'payer' => $dto->getPayerId(),
            'payee' => $dto->getPayeeId(),
            'value' => $dto->getValue()
        ];        try {
            $authorized = $this->authorizationGateway->authorizePayment($authorizationData);
            if (!$authorized) {
                throw UnauthorizedTransactionException::externalServiceDenied();
            }
        } catch (\Exception $e) {
            if ($e instanceof UnauthorizedTransactionException) {
                throw $e;
            }
            throw new UnauthorizedTransactionException('Erro na comunicação com gateway de autorização: ' . $e->getMessage());
        }

        $transaction = new Transaction(
            null,
            $dto->getValue(),
            $dto->getPayerId(),
            $dto->getPayeeId(),
            Transaction::STATUS_PENDING
        );

        return $this->databaseTransactionManager->executeInTransaction(function () use ($payerWallet, $payeeWallet, $transaction, $dto, $payer, $payee) {
            $payerWallet->decreaseBalance($dto->getValue());
            $payeeWallet->increaseBalance($dto->getValue());

            $this->walletRepository->save($payerWallet);
            $this->walletRepository->save($payeeWallet);

            $transaction->markAsCompleted();
            $savedTransaction = $this->transactionRepository->save($transaction);

            $this->sendTransactionNotification($payer, $payee, $dto->getValue());

            return $savedTransaction;
        });
    }    /**
     * Envia notificação da transação
     *
     * @param User $payer
     * @param User $payee
     * @param float $value
     */
    private function sendTransactionNotification(User $payer, User $payee, float $value): void
    {
        try {
            // Usa o método específico para notificações de transação
            $this->notificationService->sendTransactionNotification(
                $payer->getId(),
                $payee->getId(),
                $value
            );
            
        } catch (\Exception $e) {
            // Log do erro mas não interrompe o fluxo da transação
            error_log('Falha ao enviar notificação da transação: ' . $e->getMessage());
        }
    }
}
