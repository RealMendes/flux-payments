<?php

declare(strict_types=1);

namespace App\Application\Actions\Wallet;

use App\Application\Actions\Action;
use App\Application\Actions\ActionPayload;
use App\Application\DTO\WalletBalanceResponseDTO;
use App\Domain\Wallet\WalletService;
use App\Domain\Exceptions\UserNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

class GetBalanceAction extends Action
{
    private WalletService $walletService;

    public function __construct(LoggerInterface $logger, WalletService $walletService)
    {
        parent::__construct($logger);
        $this->walletService = $walletService;
    }

    protected function action(Request $request, Response $response, array $args): Response
    {
        try {
            $userId = (int) $this->resolveArg('user_id', $args);
            
            if ($userId <= 0) {
                throw new \InvalidArgumentException('ID do usuário inválido');
            }

            $balance = $this->walletService->getBalanceByUserId($userId);

            $responseDto = new WalletBalanceResponseDTO($userId, $balance);

            return $this->respondWithData($response, [
                'message' => 'Saldo consultado com sucesso',
                'data' => $responseDto->toArray()
            ]);

        } catch (\InvalidArgumentException $e) {
            $this->logger->warning('Erro de validação na consulta de saldo', [
                'user_id' => $userId ?? null,
                'error' => $e->getMessage()
            ]);

            $payload = new ActionPayload(400, null, new \App\Application\Actions\ActionError(
                \App\Application\Actions\ActionError::BAD_REQUEST,
                $e->getMessage()
            ));

            return $this->respond($response, $payload);

        } catch (UserNotFoundException $e) {
            $this->logger->warning('Tentativa de consulta de saldo para usuário inexistente', [
                'user_id' => $userId ?? null,
                'error' => $e->getMessage()
            ]);

            $payload = new ActionPayload(404, null, new \App\Application\Actions\ActionError(
                \App\Application\Actions\ActionError::RESOURCE_NOT_FOUND,
                $e->getMessage()
            ));

            return $this->respond($response, $payload);

        } catch (\Exception $e) {
            $this->logger->error('Erro interno na consulta de saldo', [
                'user_id' => $userId ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $payload = new ActionPayload(500, null, new \App\Application\Actions\ActionError(
                \App\Application\Actions\ActionError::SERVER_ERROR,
                'Erro interno do servidor'
            ));

            return $this->respond($response, $payload);
        }
    }
}
