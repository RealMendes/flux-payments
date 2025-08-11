<?php

declare(strict_types=1);

namespace App\Domain\Gateways;

/**
 * Gateway de Notificação (Port de saída)
 * Define o contrato mínimo que o domínio necessita para enviar notificações.
 */
interface NotificationGateway
{
    /**
     * Envia notificação de que uma transação foi realizada.
     *
     * @param int $payerId
     * @param int $payeeId
     * @param float $amount
     * @return bool
     */
    public function sendTransactionNotification(int $payerId, int $payeeId, float $amount): bool;
}
