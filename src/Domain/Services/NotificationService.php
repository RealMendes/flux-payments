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
     * Envia notificação de transação realizada
     *
     * @param int $payerId ID do pagador
     * @param int $payeeId ID do recebedor
     * @param float $amount Valor da transação
     * @return bool True se enviadas com sucesso
     */
    public function sendTransactionNotification(int $payerId, int $payeeId, float $amount): bool;
}
