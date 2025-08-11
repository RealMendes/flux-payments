<?php

declare(strict_types=1);

namespace App\Infrastructure\Adapters;

use App\Domain\Gateways\NotificationGateway; // altered
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;

/**
 * Adaptador para Serviço de Notificações via HTTP
 * 
 * Implementa o gateway de notificações usando comunicação HTTP externa
 */
class HttpNotificationServiceAdapter implements NotificationGateway // changed implements
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

    private function sendNotification(int $userId, string $message, array $metadata = []): bool
    {
        try {
            $notificationData = array_merge([
                'user_id' => $userId,
                'message' => $message,
                'timestamp' => date('Y-m-d H:i:s'),
            ], $metadata);

            $this->logger->info('Enviando notificação via HTTP', [
                'url' => $this->notificationUrl,
                'notification_data' => $notificationData
            ]);

            $response = $this->httpClient->post($this->notificationUrl, [
                'json' => $notificationData,
                'timeout' => 10,
                'connect_timeout' => 5,
            ]);

            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            $sent = isset($data['message']) && $data['message'] === 'Success';

            $this->logger->info('Resposta do serviço de notificação', [
                'sent' => $sent,
                'response_body' => $body
            ]);

            return $sent;
        } catch (RequestException $e) {
            $this->logger->error('Erro na comunicação com serviço de notificação', [
                'error' => $e->getMessage(),
                'url' => $this->notificationUrl,
                'user_id' => $userId
            ]);

            // Para notificações, não queremos falhar a transação se a notificação falhar
            return false;
        } catch (GuzzleException $e) {
            $this->logger->error('Erro HTTP no serviço de notificação', [
                'error' => $e->getMessage(),
                'url' => $this->notificationUrl,
                'user_id' => $userId
            ]);

            return false;
        } catch (\Exception $e) {
            $this->logger->error('Erro inesperado no serviço de notificação', [
                'error' => $e->getMessage(),
                'url' => $this->notificationUrl,
                'user_id' => $userId
            ]);

            return false;
        }
    }

    public function sendTransactionNotification(int $payerId, int $payeeId, float $amount): bool
    {
        $payerMessage = "Transferência realizada com sucesso! Valor: R$ " . number_format($amount, 2, ',', '.');
        $payeeMessage = "Transferência recebida! Valor: R$ " . number_format($amount, 2, ',', '.');

        $transactionMetadata = [
            'type' => 'transaction',
            'amount' => $amount,
            'payer_id' => $payerId,
            'payee_id' => $payeeId,
        ];

        try {
            // Notifica o pagador
            $payerNotified = $this->sendNotification($payerId, $payerMessage, $transactionMetadata);

            // Notifica o recebedor
            $payeeNotified = $this->sendNotification($payeeId, $payeeMessage, $transactionMetadata);

            return $payerNotified && $payeeNotified;
        } catch (\Exception $e) {
            $this->logger->error('Falha ao enviar notificações de transação', [
                'error' => $e->getMessage(),
                'payer_id' => $payerId,
                'payee_id' => $payeeId,
                'amount' => $amount
            ]);

            return false;
        }
    }
}
