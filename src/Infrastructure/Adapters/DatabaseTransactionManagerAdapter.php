<?php

declare(strict_types=1);

namespace App\Infrastructure\Adapters;

use App\Domain\Repositories\DatabaseTransactionManager;
use PDO;
use Psr\Log\LoggerInterface;

/**
 * Adaptador para Gerenciamento de Transações de Banco de Dados via PDO
 * 
 * Implementa o gerenciador de transações usando PDO
 */
class DatabaseTransactionManagerAdapter implements DatabaseTransactionManager
{
    private PDO $pdo;
    private LoggerInterface $logger;

    public function __construct(PDO $pdo, LoggerInterface $logger)
    {
        $this->pdo = $pdo;
        $this->logger = $logger;
    }

    public function executeInTransaction(callable $operations)
    {
        $this->beginTransaction();

        try {
            $result = $operations();
            $this->commit();

            $this->logger->info('Transação de banco de dados executada com sucesso');

            return $result;
        } catch (\Exception $e) {
            $this->rollback();

            $this->logger->error('Erro na transação de banco de dados, executando rollback', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    public function beginTransaction(): void
    {
        if (!$this->pdo->beginTransaction()) {
            throw new \Exception('Não foi possível iniciar a transação de banco de dados');
        }

        $this->logger->debug('Transação de banco de dados iniciada');
    }

    public function commit(): void
    {
        if (!$this->pdo->commit()) {
            throw new \Exception('Não foi possível confirmar a transação de banco de dados');
        }

        $this->logger->debug('Transação de banco de dados confirmada');
    }

    public function rollback(): void
    {
        if (!$this->pdo->rollback()) {
            throw new \Exception('Não foi possível desfazer a transação de banco de dados');
        }

        $this->logger->debug('Transação de banco de dados desfeita (rollback)');
    }
}
