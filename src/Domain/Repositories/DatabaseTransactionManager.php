<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

/**
 * Gerenciador de Transações de Banco de Dados
 * 
 * Define o contrato para operações transacionais de persistência
 * Interface que representa "o que a aplicação precisa" para transações DB
 */
interface DatabaseTransactionManager
{
    /**
     * Executa operações dentro de uma transação de banco de dados
     *
     * @param callable $operations Função que contém as operações a serem executadas
     * @return mixed Resultado das operações executadas
     * @throws \Exception Se houver erro na transação
     */
    public function executeInTransaction(callable $operations);

    /**
     * Inicia uma transação de banco de dados
     *
     * @return void
     * @throws \Exception Se não conseguir iniciar a transação
     */
    public function beginTransaction(): void;

    /**
     * Confirma a transação atual
     *
     * @return void
     * @throws \Exception Se não conseguir confirmar a transação
     */
    public function commit(): void;

    /**
     * Desfaz a transação atual
     *
     * @return void
     * @throws \Exception Se não conseguir desfazer a transação
     */
    public function rollback(): void;
}
