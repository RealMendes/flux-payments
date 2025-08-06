<?php

declare(strict_types=1);

namespace App\Infrastructure\Adapters;

use App\Domain\Gateways\PaymentAuthorizationGateway;
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

            if (empty($this->authorizerUrl)) {
                $this->logger->warning('URL do gateway de autorização não configurada, aprovando automaticamente');
                return true;
            }

            $response = $this->httpClient->get($this->authorizerUrl, [
                'query' => $transactionData,
                'timeout' => 10,
                'connect_timeout' => 5,
            ]);

            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            $authorized = isset($data['message']) && $data['message'] === 'Autorizado';

            $this->logger->info('Resposta do gateway de autorização', [
                'authorized' => $authorized,
                'response_body' => $body
            ]);

            return $authorized;

        } catch (RequestException $e) {
            $this->logger->error('Erro na comunicação com gateway de autorização', [
                'error' => $e->getMessage(),
                'url' => $this->authorizerUrl
            ]);
            throw new \Exception('Falha na comunicação com o gateway de autorização: ' . $e->getMessage());

        } catch (GuzzleException $e) {
            $this->logger->error('Erro HTTP no gateway de autorização', [
                'error' => $e->getMessage(),
                'url' => $this->authorizerUrl
            ]);
            throw new \Exception('Erro no gateway de autorização: ' . $e->getMessage());

        } catch (\Exception $e) {
            $this->logger->error('Erro inesperado no gateway de autorização', [
                'error' => $e->getMessage(),
                'url' => $this->authorizerUrl
            ]);
            throw new \Exception('Erro inesperado no gateway de autorização: ' . $e->getMessage());
        }
    }

    public function isAvailable(): bool
    {
        try {
            if (empty($this->authorizerUrl)) {
                return false;
            }

            $response = $this->httpClient->head($this->authorizerUrl, [
                'timeout' => 5,
                'connect_timeout' => 3,
            ]);

            return $response->getStatusCode() < 400;

        } catch (\Exception $e) {
            $this->logger->warning('Gateway de autorização indisponível', [
                'error' => $e->getMessage(),
                'url' => $this->authorizerUrl
            ]);
            return false;
        }
    }
}
