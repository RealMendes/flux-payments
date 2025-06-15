<?php

declare(strict_types=1);

namespace App\Application\Actions\Transfer;

use App\Application\Actions\Action;
use App\Application\Actions\ActionPayload;
use App\Application\DTO\TransferRequestDTO;
use App\Domain\Transaction\TransferService;
use App\Domain\Exceptions\UserNotFoundException;
use App\Domain\Exceptions\UnauthorizedTransferException;
use App\Domain\Exceptions\InsufficientBalanceException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

class ExecuteTransferAction extends Action
{
    private TransferService $transferService;

    public function __construct(LoggerInterface $logger, TransferService $transferService)
    {
        parent::__construct($logger);
        $this->transferService = $transferService;
    }

    protected function action(Request $request, Response $response, array $args): Response
    {
        try {
            $data = $this->getFormData($request);
            
            if (!is_array($data)) {
                throw new \InvalidArgumentException('Dados JSON inválidos');
            }

            $dto = new TransferRequestDTO(
                (float) ($data['value'] ?? 0),
                (int) ($data['payer_id'] ?? 0),
                (int) ($data['payee_id'] ?? 0)
            );

            $transaction = $this->transferService->execute($dto);

            $this->logger->info('Transferência realizada com sucesso', [
                'transaction_id' => $transaction->getId(),
                'payer_id' => $transaction->getPayerId(),
                'payee_id' => $transaction->getPayeeId(),
                'value' => $transaction->getValue()
            ]);

            $transactionData = [
                'transaction_id' => $transaction->getId(),
                'value' => $transaction->getValue(),
                'payer_id' => $transaction->getPayerId(),
                'payee_id' => $transaction->getPayeeId(),
                'status' => $transaction->getStatus(),
                'created_at' => $transaction->getCreatedAt()->format('Y-m-d H:i:s')
            ];

            return $this->respondWithData($response, [
                'message' => 'Transferência realizada com sucesso',
                'data' => $transactionData
            ], 201);

        } catch (\InvalidArgumentException $e) {
            $this->logger->warning('Erro de validação na transferência', [
                'error' => $e->getMessage(),
                'data' => $data ?? null
            ]);

            $payload = new ActionPayload(400, null, new \App\Application\Actions\ActionError(
                \App\Application\Actions\ActionError::BAD_REQUEST,
                $e->getMessage()
            ));

            return $this->respond($response, $payload);

        } catch (UserNotFoundException $e) {
            $this->logger->warning('Tentativa de transferência com usuário inexistente', [
                'error' => $e->getMessage(),
                'data' => $data ?? null
            ]);

            $payload = new ActionPayload(404, null, new \App\Application\Actions\ActionError(
                \App\Application\Actions\ActionError::RESOURCE_NOT_FOUND,
                $e->getMessage()
            ));

            return $this->respond($response, $payload);

        } catch (UnauthorizedTransferException $e) {
            $this->logger->warning('Transferência não autorizada', [
                'error' => $e->getMessage(),
                'data' => $data ?? null
            ]);

            $payload = new ActionPayload(403, null, new \App\Application\Actions\ActionError(
                \App\Application\Actions\ActionError::INSUFFICIENT_PRIVILEGES,
                $e->getMessage()
            ));

            return $this->respond($response, $payload);

        } catch (InsufficientBalanceException $e) {
            $this->logger->warning('Tentativa de transferência com saldo insuficiente', [
                'error' => $e->getMessage(),
                'data' => $data ?? null
            ]);

            $payload = new ActionPayload(400, null, new \App\Application\Actions\ActionError(
                \App\Application\Actions\ActionError::BAD_REQUEST,
                $e->getMessage()
            ));

            return $this->respond($response, $payload);

        } catch (\Exception $e) {
            $this->logger->error('Erro interno na transferência', [
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
