<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Transfer;

use App\Application\Actions\Transfer\ExecuteTransferAction;
use App\Domain\Transaction\TransferService;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request as SlimRequest;
use Slim\Psr7\Response;
use Slim\Psr7\Uri;

class ExecuteTransferActionTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $transferServiceProphecy;
    private ObjectProphecy $loggerProphecy;

    protected function setUp(): void
    {
        $this->transferServiceProphecy = $this->prophesize(TransferService::class);
        $this->loggerProphecy = $this->prophesize(LoggerInterface::class);
    }

    public function testActionCanBeInstantiated(): void
    {
        $action = new ExecuteTransferAction(
            $this->loggerProphecy->reveal(),
            $this->transferServiceProphecy->reveal()
        );

        $this->assertInstanceOf(ExecuteTransferAction::class, $action);
    }

    public function testActionWithInvalidDataReturns400(): void
    {
        $action = new ExecuteTransferAction(
            $this->loggerProphecy->reveal(),
            $this->transferServiceProphecy->reveal()
        );

        $request = $this->createJsonRequest('POST', '/api/v1/transfer', [
            'invalid' => 'data'
        ]);

        $response = new Response();
        $result = $action($request, $response, []);

        $this->assertEquals(400, $result->getStatusCode());
    }

    private function createJsonRequest(string $method, string $path, array $data): \Psr\Http\Message\ServerRequestInterface
    {
        $json = json_encode($data);
        $stream = (new StreamFactory())->createStream($json);
        
        return new SlimRequest(
            $method,
            new Uri('', '', 80, $path),
            new Headers(['Content-Type' => 'application/json']),
            [],
            [],
            $stream
        );
    }
}
