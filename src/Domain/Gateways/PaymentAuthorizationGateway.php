<?php

declare(strict_types=1);

namespace App\Domain\Gateways;

/**
 * Gateway de Autorização de Pagamentos
 * 
 * Define o contrato para autorização externa de transações financeiras
 * Interface que representa "o que a aplicação precisa" de serviços de autorização
 */
interface PaymentAuthorizationGateway
{
    /**
     * Autoriza uma transação baseada nos dados fornecidos
     *
     * @param array $transactionData Dados da transação para autorização
     * @return bool True se autorizada, false caso contrário
     * @throws \Exception Se houver erro na comunicação com o serviço
     */
    public function authorizePayment(array $transactionData): bool;

    /**
     * Verifica se o gateway está disponível
     *
     * @return bool True se disponível, false caso contrário
     */
    public function isAvailable(): bool;
}
