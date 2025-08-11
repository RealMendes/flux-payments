<?php

declare(strict_types=1);

namespace App\Application\Actions\Transaction;

use App\Application\Actions\Action;
use App\Application\Actions\ActionPayload;
use App\Application\DTO\TransactionRequestDTO;
use App\Domain\Transaction\TransactionManagementService;
use App\Domain\Exceptions\UserNotFoundException;
use App\Domain\Exceptions\UnauthorizedTransactionException;
use App\Domain\Exceptions\InsufficientBalanceException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

class ExecuteTransactionAction extends Action
{
    private TransactionManagementService $transactionManagementService;

    public function __construct(LoggerInterface $logger, TransactionManagementService $transactionManagementService)
    {
        parent::__construct($logger);
        $this->transactionManagementService = $transactionManagementService;
    }

    protected function action(Request $request, Response $response, array $args): Response
    {
        try {
            $data = $this->getFormData($request);

            if (!is_array($data)) {
                throw new \InvalidArgumentException('Dados JSON inválidos');
            }

            $dto = new TransactionRequestDTO(
                    (float) ($data['value'] ?? 0),
                    (int) ($data['payer'] ?? 0),
                    (int) ($data['payee'] ?? 0)
                );

            $transaction = $this->transactionManagementService->executeTransaction($dto);
            $this->logger->info('Transação realizada com sucesso', [
                'transaction_id' => $transaction->getId(),
                'payer' => $transaction->getPayerId(),
                'payee' => $transaction->getPayeeId(),
                'value' => $transaction->getValue()
            ]);

            $transactionData = [
                'transaction_id' => $transaction->getId(),
                'value' => $transaction->getValue(),
                'payer' => $transaction->getPayerId(),
                'payee' => $transaction->getPayeeId(),
                'status' => $transaction->getStatus(),
                'created_at' => $transaction->getCreatedAt()->format('Y-m-d H:i:s')
            ];

            return $this->respondWithData($response, [
                'message' => 'Transação realizada com sucesso',
                'data' => $transactionData
            ], 201);
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning('Erro de validação na transação', [
                'error' => $e->getMessage(),
                'data' => $data ?? null
            ]);

            $payload = new ActionPayload(400, null, new \App\Application\Actions\ActionError(
                \App\Application\Actions\ActionError::BAD_REQUEST,
                $e->getMessage()
            ));

            return $this->respond($response, $payload);
        } catch (UserNotFoundException $e) {
            $this->logger->warning('Tentativa de transação com usuário inexistente', [
                'error' => $e->getMessage(),
                'data' => $data ?? null
            ]);

            $payload = new ActionPayload(404, null, new \App\Application\Actions\ActionError(
                \App\Application\Actions\ActionError::RESOURCE_NOT_FOUND,
                $e->getMessage()
            ));

            return $this->respond($response, $payload);
        } catch (UnauthorizedTransactionException $e) {
            $this->logger->warning('Transação não autorizada', [
                'error' => $e->getMessage(),
                'data' => $data ?? null
            ]);

            $payload = new ActionPayload(403, null, new \App\Application\Actions\ActionError(
                \App\Application\Actions\ActionError::INSUFFICIENT_PRIVILEGES,
                $e->getMessage()
            ));

            return $this->respond($response, $payload);
        } catch (InsufficientBalanceException $e) {
            $this->logger->warning('Tentativa de transação com saldo insuficiente', [
                'error' => $e->getMessage(),
                'data' => $data ?? null
            ]);

            $payload = new ActionPayload(400, null, new \App\Application\Actions\ActionError(
                \App\Application\Actions\ActionError::BAD_REQUEST,
                $e->getMessage()
            ));

            return $this->respond($response, $payload);
        } catch (\Exception $e) {
            $this->logger->error('Erro interno na transação', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data ?? null
            ]);

            $payload = new ActionPayload(500, null, new \App\Application\Actions\ActionError(
                \App\Application\Actions\ActionError::SERVER_ERROR,
                'Erro interno do servidor'
            ));

            return $this->respond($response, $payload);
        }
    }
}
