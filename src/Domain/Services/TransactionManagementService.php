<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Application\DTO\TransactionRequestDTO;
use App\Domain\Transaction\Transaction;

/**
 * Serviço de Gerenciamento de Transações
 * 
 * Define os casos de uso principais para o domínio de transações
 * Interface que representa "o que a aplicação oferece" para o mundo exterior
 */
interface TransactionManagementService
{
    /**
     * Executa uma transação entre usuários
     *
     * @param TransactionRequestDTO $dto Dados da transação
     * @return Transaction Transação processada
     * @throws \Exception Se houver erro no processamento
     */
    public function executeTransaction(TransactionRequestDTO $dto): Transaction;

    /**
     * Consulta o histórico de transações de um usuário
     *
     * @param int $userId ID do usuário
     * @return Transaction[] Lista de transações
     * @throws \Exception Se houver erro na consulta
     */
    public function getUserTransactionHistory(int $userId): array;
}
