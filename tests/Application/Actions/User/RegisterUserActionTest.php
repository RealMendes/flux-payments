<?php

declare(strict_types=1);

namespace Tests\Application\Actions\User;

use App\Application\Actions\User\RegisterUserAction;
use App\Domain\User\UserService;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request as SlimRequest;
use Slim\Psr7\Response;
use Slim\Psr7\Uri;

class RegisterUserActionTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $userServiceProphecy;
    private ObjectProphecy $loggerProphecy;

    protected function setUp(): void
    {
        $this->userServiceProphecy = $this->prophesize(UserService::class);
        $this->loggerProphecy = $this->prophesize(LoggerInterface::class);
    }

    public function testActionCanBeInstantiated(): void
    {
        $action = new RegisterUserAction(
            $this->loggerProphecy->reveal(),
            $this->userServiceProphecy->reveal()
        );

        $this->assertInstanceOf(RegisterUserAction::class, $action);
    }

    public function testActionWithInvalidDataReturns400(): void
    {
        $action = new RegisterUserAction(
            $this->loggerProphecy->reveal(),
            $this->userServiceProphecy->reveal()
        );

        $request = $this->createJsonRequest('POST', '/api/v1/user/register', [
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
