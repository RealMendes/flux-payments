<?php

declare(strict_types=1);

namespace App\Application\Actions\User;

use App\Application\Actions\Action;
use App\Application\Actions\ActionPayload;
use App\Application\DTO\UserRegisterRequestDTO;
use App\Domain\User\UserManagementService;
use App\Domain\Exceptions\UserAlreadyExistsException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

class RegisterUserAction extends Action
{
    private UserManagementService $userService;

    public function __construct(LoggerInterface $logger, UserManagementService $userService)
    {
        parent::__construct($logger);
        $this->userService = $userService;
    }

    protected function action(Request $request, Response $response, array $args): Response
    {
        try {
            $data = $this->getFormData($request);

            if (!is_array($data)) {
                throw new \InvalidArgumentException('Dados JSON inválidos');
            }

            $dto = new UserRegisterRequestDTO(
                $data['full_name'] ?? '',
                $data['cpf_cnpj'] ?? '',
                $data['email'] ?? '',
                $data['password'] ?? '',
                $data['type'] ?? 'COMMON'
            );

            $user = $this->userService->registerUser($dto);

            $this->logger->info('Usuário registrado com sucesso', [
                'user_id' => $user->getId(),
                'email' => $user->getEmail()
            ]);

            $userData = [
                'id' => $user->getId(),
                'full_name' => $user->getFullName(),
                'email' => $user->getEmail(),
                'cpf_cnpj' => $user->getCpfCnpj(),
                'type' => $user->getType(),
                'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s')
            ];

            return $this->respondWithData($response, [
                'message' => 'Usuário registrado com sucesso',
                'data' => $userData
            ], 201);
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning('Erro de validação no registro de usuário', [
                'error' => $e->getMessage(),
                'data' => $data ?? null
            ]);

            $payload = new ActionPayload(400, null, new \App\Application\Actions\ActionError(
                \App\Application\Actions\ActionError::BAD_REQUEST,
                $e->getMessage()
            ));

            return $this->respond($response, $payload);
        } catch (UserAlreadyExistsException $e) {
            $this->logger->warning('Tentativa de registro de usuário duplicado', [
                'error' => $e->getMessage(),
                'data' => $data ?? null
            ]);

            $payload = new ActionPayload(409, null, new \App\Application\Actions\ActionError(
                \App\Application\Actions\ActionError::VALIDATION_ERROR,
                $e->getMessage()
            ));

            return $this->respond($response, $payload);
        } catch (\Exception $e) {
            $this->logger->error('Erro interno no registro de usuário', [
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
