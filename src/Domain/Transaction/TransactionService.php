<?php

declare(strict_types=1);

namespace App\Domain\Transaction;

use App\Application\DTO\TransactionRequestDTO;
use App\Domain\User\User;
use App\Domain\User\UserRepository;
use App\Domain\Wallet\WalletRepository;
use App\Domain\Wallet\WalletNotFoundException;
use App\Infrastructure\ExternalServices\AuthorizerService;
use App\Infrastructure\ExternalServices\NotificationService;
use App\Domain\Exceptions\UserNotFoundException;
use App\Domain\Exceptions\UnauthorizedTransactionException;
use App\Domain\Exceptions\InsufficientBalanceException;
use PDO;

class TransactionService
{
    private UserRepository $userRepository;
    private WalletRepository $walletRepository;
    private TransactionRepository $transactionRepository;
    private AuthorizerService $authorizerService;
    private NotificationService $notificationService;
    private PDO $pdo;

    public function __construct(
        UserRepository $userRepository,
        WalletRepository $walletRepository,
        TransactionRepository $transactionRepository,
        AuthorizerService $authorizerService,
        NotificationService $notificationService,
        PDO $pdo
    ) {        $this->userRepository = $userRepository;
        $this->walletRepository = $walletRepository;
        $this->transactionRepository = $transactionRepository;
        $this->authorizerService = $authorizerService;
        $this->notificationService = $notificationService;
        $this->pdo = $pdo;
    }

    /**
     * Executa uma transação entre usuários
     *
     * @param TransactionRequestDTO $dto
     * @return Transaction
     * @throws \Exception
     */    public function execute(TransactionRequestDTO $dto): Transaction
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
        }   

        $authorizationData = [
            'payer' => $dto->getPayerId(),
            'payee' => $dto->getPayeeId(),
            'value' => $dto->getValue()
        ];

        try {
            $authorized = $this->authorizerService->authorize($authorizationData);
            if (!$authorized) {
                throw UnauthorizedTransactionException::externalServiceDenied();
            }
        } catch (\Exception $e) {
            if ($e instanceof UnauthorizedTransactionException) {
                throw $e;
            }
            throw new UnauthorizedTransactionException('Erro na comunicação com serviço de autorização: ' . $e->getMessage());
        }

        $transaction = new Transaction(
            null,
            $dto->getValue(),
            $dto->getPayerId(),
            $dto->getPayeeId(),
            Transaction::STATUS_PENDING
        );
        $this->pdo->beginTransaction();

        try {
            $payerWallet->decreaseBalance($dto->getValue());
            $payeeWallet->increaseBalance($dto->getValue());

            $this->walletRepository->save($payerWallet);
            $this->walletRepository->save($payeeWallet);

            $transaction->markAsCompleted();
            $savedTransaction = $this->transactionRepository->save($transaction);

            $this->pdo->commit();

            $this->sendTransactionNotification($payer, $payee, $dto->getValue());

            return $savedTransaction;

        } catch (\Exception $e) {
            $this->pdo->rollBack();
            
            if (isset($savedTransaction)) {
                $transaction->markAsFailed();
                $this->transactionRepository->save($transaction);
            }

            throw new \Exception('Erro ao processar transação: ' . $e->getMessage());
        }
    }

    /**
     * Envia notificação da transação
     *
     * @param User $payer
     * @param User $payee
     * @param float $value
     */
    private function sendTransactionNotification(User $payer, User $payee, float $value): void
    {
        $notificationData = [
            'payer' => [
                'id' => $payer->getId(),
                'name' => $payer->getFullName(),
                'email' => $payer->getEmail()
            ],
            'payee' => [
                'id' => $payee->getId(),
                'name' => $payee->getFullName(),
                'email' => $payee->getEmail()
            ],
            'value' => $value,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        try {
            $success = $this->notificationService->sendNotification($notificationData);
            
            if (!$success) {
                $this->notificationService->sendNotificationWithRetry($notificationData);
            }
        } catch (\Exception $e) {
            try {
                $this->notificationService->sendNotificationWithRetry($notificationData);
            } catch (\Exception $retryException) {
                error_log('Falha ao enviar notificação após tentativas: ' . $retryException->getMessage());
            }
        }
    }
}
