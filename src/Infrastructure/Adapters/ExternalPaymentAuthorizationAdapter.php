<?php

declare(strict_types=1);

namespace App\Infrastructure\Adapters;

use App\Domain\Gateways\PaymentAuthorizationGateway;
use App\Domain\Exceptions\UnauthorizedTransactionException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;

/**
 * Adaptador para Autorização de Pagamentos via Serviço HTTP Externo
 * 
 * Implementa o gateway de autorização usando comunicação HTTP
 */
class ExternalPaymentAuthorizationAdapter implements PaymentAuthorizationGateway
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

    public function authorizePayment(array $transactionData): bool
    {
        try {
            $this->logger->info('Consultando gateway de autorização de pagamentos', [
                'url' => $this->authorizerUrl,
                'transaction_data' => $transactionData
            ]);

            if ($this->isUrlNotConfigured()) {
                $this->logger->warning('URL do gateway de autorização não configurada, aprovando automaticamente');
                return true;
            }

            $response = $this->makeAuthorizationRequest($transactionData);
            $responseData = $this->parseResponseBody($response);
            $authorized = $this->extractAuthorizationStatus($responseData);

            $this->logger->info('Resposta do gateway de autorização', [
                'authorized' => $authorized,
                'response_body' => $response->getBody()->getContents()
            ]);

            if (!$authorized) {
                throw UnauthorizedTransactionException::externalServiceDenied();
            }

            return $authorized;

        } catch (RequestException $e) {
            return $this->handleRequestException($e);
        } catch (GuzzleException $e) {
            $this->logger->error('Erro HTTP no gateway de autorização', [
                'error' => $e->getMessage(),
                'url' => $this->authorizerUrl
            ]);
            throw UnauthorizedTransactionException::externalServiceDenied();
        } catch (\Exception $e) {
            $this->logger->error('Erro inesperado no gateway de autorização', [
                'error' => $e->getMessage(),
                'url' => $this->authorizerUrl
            ]);
            throw UnauthorizedTransactionException::externalServiceDenied();
        }
    }

    private function isUrlNotConfigured(): bool
    {
        return empty($this->authorizerUrl);
    }

    private function makeAuthorizationRequest(array $transactionData)
    {
        return $this->httpClient->get($this->authorizerUrl, [
            'query' => $transactionData,
            'timeout' => 10,
            'connect_timeout' => 5,
        ]);
    }

    private function parseResponseBody($response): array
    {
        $body = $response->getBody()->getContents();
        return json_decode($body, true) ?? [];
    }

    private function extractAuthorizationStatus(array $data): bool
    {
        if (isset($data['data']['authorization'])) {
            return (bool) $data['data']['authorization'];
        }
        
        if (isset($data['message']) && $data['message'] === 'Autorizado') {
            return true;
        }
        
        if (isset($data['authorized'])) {
            return (bool) $data['authorized'];
        }

        return false;
    }

    private function handleRequestException(RequestException $e): bool
    {
        $response = $e->getResponse();
        
        if (!$response) {
            $this->logger->error('Erro na comunicação com gateway de autorização', [
                'error' => $e->getMessage(),
                'url' => $this->authorizerUrl
            ]);
            throw new \Exception('Falha na comunicação com o gateway de autorização');
        }

        $statusCode = $response->getStatusCode();
        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        if ($this->hasValidAuthorizationResponse($data)) {
            $authorized = (bool) $data['data']['authorization'];
            
            $this->logger->warning('Gateway de autorização retornou erro HTTP, mas com resposta válida', [
                'status_code' => $statusCode,
                'authorized' => $authorized,
                'response_body' => $body
            ]);
            
            return $authorized;
        }

        $this->logger->error('Erro na comunicação com gateway de autorização', [
            'status_code' => $statusCode,
            'response_body' => $body,
            'url' => $this->authorizerUrl
        ]);

        if ($statusCode === 403) {
            throw UnauthorizedTransactionException::externalServiceDenied();
        }

        throw new \Exception('Falha na comunicação com o gateway de autorização (HTTP ' . $statusCode . ')');
    }

    private function hasValidAuthorizationResponse(?array $data): bool
    {
        return $data && isset($data['data']['authorization']);
    }

    public function isAvailable(): bool
    {
        try {
            if ($this->isUrlNotConfigured()) {
                return false;
            }

            $response = $this->makeHealthCheckRequest();
            return $this->isHealthCheckSuccessful($response);

        } catch (\Exception $e) {
            $this->logger->warning('Gateway de autorização indisponível', [
                'error' => $e->getMessage(),
                'url' => $this->authorizerUrl
            ]);
            return false;
        }
    }

    private function makeHealthCheckRequest()
    {
        return $this->httpClient->head($this->authorizerUrl, [
            'timeout' => 5,
            'connect_timeout' => 3,
        ]);
    }

    private function isHealthCheckSuccessful($response): bool
    {
        return $response->getStatusCode() < 400;
    }
}
