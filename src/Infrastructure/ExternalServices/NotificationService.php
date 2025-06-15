<?php

declare(strict_types=1);

namespace App\Infrastructure\ExternalServices;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

class NotificationService
{
    private Client $httpClient;
    private string $notificationUrl;
    private LoggerInterface $logger;

    public function __construct(Client $httpClient, string $notificationUrl, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->notificationUrl = $notificationUrl;
        $this->logger = $logger;
    }

    /**
     *
     * @param array
     * @return bool
     * @throws \Exception
     */
    public function sendNotification(array $notificationData): bool
    {
        try {
            $this->logger->info('Enviando notificação', [
                'notification_data' => $notificationData
            ]);

            $response = $this->httpClient->post($this->notificationUrl, [
                'json' => $notificationData,
                'timeout' => 10,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ]
            ]);

            $statusCode = $response->getStatusCode();
            $responseBody = json_decode($response->getBody()->getContents(), true);

            if ($statusCode === 200 || $statusCode === 201) {
                $success = $responseBody['success'] ?? true;
                
                $this->logger->info('Notificação enviada com sucesso', [
                    'success' => $success,
                    'response' => $responseBody
                ]);

                return (bool) $success;
            }

            $this->logger->warning('Resposta inesperada do serviço de notificação', [
                'status_code' => $statusCode,
                'response' => $responseBody
            ]);

            return false;

        } catch (GuzzleException $e) {
            $this->logger->error('Erro na comunicação com serviço de notificação', [
                'error' => $e->getMessage(),
                'notification_data' => $notificationData
            ]);

            throw new \Exception('Falha na comunicação com o serviço de notificação: ' . $e->getMessage());
        }
    }

    /**
     * @param array
     * @param int
     * @return bool
     */
    public function sendNotificationWithRetry(array $notificationData, int $maxRetries = 3): bool
    {
        $attempts = 0;
        
        while ($attempts < $maxRetries) {
            try {
                $attempts++;
                
                if ($this->sendNotification($notificationData)) {
                    return true;
                }
                
                if ($attempts < $maxRetries) {
                    $this->logger->info('Tentativa de notificação falhou, tentando novamente', [
                        'attempt' => $attempts,
                        'max_retries' => $maxRetries
                    ]);
                    
                    sleep(pow(2, $attempts)); // Backoff exponencial
                }
                
            } catch (\Exception $e) {
                $this->logger->error('Erro na tentativa de envio de notificação', [
                    'attempt' => $attempts,
                    'error' => $e->getMessage()
                ]);
                
                if ($attempts >= $maxRetries) {
                    throw $e;
                }
                
                sleep(pow(2, $attempts)); // Backoff exponencial
            }
        }
        
        $this->logger->error('Falha ao enviar notificação após todas as tentativas', [
            'max_retries' => $maxRetries,
            'notification_data' => $notificationData
        ]);
        
        return false;
    }
}