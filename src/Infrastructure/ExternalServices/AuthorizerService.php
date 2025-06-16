<?php

declare(strict_types=1);

namespace App\Infrastructure\ExternalServices;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
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
     * Autoriza uma transação consultando o serviço externo
     * 
     * @param array $transactionData
     * @return bool
     * @throws \Exception
     */
    public function authorize(array $transactionData): bool
    {
        try {
            $response = $this->httpClient->get($this->authorizerUrl, [
                'timeout' => 10,
                'headers' => ['Accept' => 'application/json']
            ]);

            return $this->extractAuthorization($response->getBody()->getContents());

        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                return $this->extractAuthorization($e->getResponse()->getBody()->getContents());
            }
            
            $this->logger->error('Erro na comunicação com serviço de autorização', [
                'error' => $e->getMessage()
            ]);
            
            throw new \Exception('Falha na comunicação com o serviço de autorização');
            
        } catch (GuzzleException $e) {
            $this->logger->error('Erro na comunicação com serviço de autorização', [
                'error' => $e->getMessage()
            ]);
            
            throw new \Exception('Falha na comunicação com o serviço de autorização');
        }
    }

    /**
     * Extrai o valor de autorização da resposta JSON
     * 
     * @param string $responseBody
     * @return bool
     */
    private function extractAuthorization(string $responseBody): bool
    {
        $data = json_decode($responseBody, true);
        
        return $data['data']['authorization'] ?? false;
    }
}
