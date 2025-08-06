<?php

declare(strict_types=1);

namespace App\Domain\Services;

/**
 * Serviço de Notificações
 * 
 * Define o contrato para envio de notificações aos usuários
 * Interface que representa "o que a aplicação precisa" para comunicação
 */
interface NotificationService
{
    /**
     * Envia uma notificação para um usuário
     *
     * @param int $userId ID do usuário que receberá a notificação
     * @param string $message Mensagem da notificação
     * @param array $metadata Dados adicionais da notificação
     * @return bool True se enviada com sucesso, false caso contrário
     * @throws \Exception Se houver erro crítico no envio da notificação
     */
    public function sendNotification(int $userId, string $message, array $metadata = []): bool;

    /**
     * Envia notificação de transação realizada
     *
     * @param int $payerId ID do pagador
     * @param int $payeeId ID do recebedor
     * @param float $amount Valor da transação
     * @return bool True se enviadas com sucesso
     */
    public function sendTransactionNotification(int $payerId, int $payeeId, float $amount): bool;
}
