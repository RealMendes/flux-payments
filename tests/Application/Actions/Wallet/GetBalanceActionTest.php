<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Wallet;

use App\Application\Actions\Wallet\GetBalanceAction;
use App\Domain\Wallet\WalletManagementService;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request as SlimRequest;
use Slim\Psr7\Response;
use Slim\Psr7\Uri;

class GetBalanceActionTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $walletServiceProphecy;
    private ObjectProphecy $loggerProphecy;

    protected function setUp(): void
    {
        $this->walletServiceProphecy = $this->prophesize(WalletManagementService::class);
        $this->loggerProphecy = $this->prophesize(LoggerInterface::class);
    }

    public function testActionCanBeInstantiated(): void
    {
        $action = new GetBalanceAction(
            $this->loggerProphecy->reveal(),
            $this->walletServiceProphecy->reveal()
        );

        $this->assertInstanceOf(GetBalanceAction::class, $action);
    }

    public function testActionWithInvalidUserIdReturns400(): void
    {
        $action = new GetBalanceAction(
            $this->loggerProphecy->reveal(),
            $this->walletServiceProphecy->reveal()
        );

        $request = $this->createRequest('GET', '/api/v1/wallet/balance/invalid');
        $response = new Response();
        $result = $action($request, $response, ['user_id' => 'invalid']);

        $this->assertEquals(400, $result->getStatusCode());
    }

    private function createRequest(string $method, string $path): \Psr\Http\Message\ServerRequestInterface
    {
        return new SlimRequest(
            $method,
            new Uri('', '', 80, $path),
            new Headers(),
            [],
            [],
            (new StreamFactory())->createStream('')
        );
    }
}
