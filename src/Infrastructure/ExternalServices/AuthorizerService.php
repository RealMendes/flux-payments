<?php

declare(strict_types=1);

namespace App\Infrastructure\ExternalServices;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

class AuthorizerService
{
    private Client $httpClient;
    private string $authorizerUrl;
    private LoggerInterface $logger;

    public function __construct(Client $httpClient, string $authorizerUrl, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->authorizerUrl = $authorizerUrl;
        $this->logger = $logger;
    }

    /**
     * @param array
     * @return bool
     * @throws \Exception
     */
    public function authorize(array $transactionData): bool
    {
        try {
            $this->logger->info('Iniciando autorização de transação', [
                'transaction_data' => $transactionData
            ]);

            $response = $this->httpClient->post($this->authorizerUrl, [
                'json' => $transactionData,
                'timeout' => 10,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ]
            ]);

            $statusCode = $response->getStatusCode();
            $responseBody = json_decode($response->getBody()->getContents(), true);

            if ($statusCode === 200) {
                $authorized = $responseBody['authorized'] ?? false;
                
                $this->logger->info('Resposta da autorização recebida', [
                    'authorized' => $authorized,
                    'response' => $responseBody
                ]);

                return (bool) $authorized;
            }

            $this->logger->warning('Resposta inesperada do serviço de autorização', [
                'status_code' => $statusCode,
                'response' => $responseBody
            ]);

            return false;

        } catch (GuzzleException $e) {
            $this->logger->error('Erro na comunicação com serviço de autorização', [
                'error' => $e->getMessage(),
                'transaction_data' => $transactionData
            ]);

            throw new \Exception('Falha na comunicação com o serviço de autorização: ' . $e->getMessage());
        }
    }
}