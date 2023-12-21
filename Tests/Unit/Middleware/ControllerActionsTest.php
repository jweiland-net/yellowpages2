<?php

declare(strict_types=1);

namespace JWeiland\Yellowpages2\Tests\Unit\Middleware;

use JWeiland\Yellowpages2\Middleware\ControllerActionsMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Frontend\Http\RequestHandler;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class ControllerActionsTest extends UnitTestCase
{
    /**
     * @dataProvider requestActionDataProvider
     */
    public function testRequestAction(array $requestBody, array $expectedRequestBody): void
    {
        // Make a request body with the prepared dataset request body
        $request = new ServerRequest('https://a-random-domain.com/mysite/', 'POST');
        $request = $request->withParsedBody($requestBody);

        // Here we will assert the modifier request insider handler
        $handler = $this->createMock(RequestHandler::class);
        $handler->expects(self::atLeastOnce())
            ->method('handle')
            ->with($this->callback(static function (ServerRequestInterface $capturedRequest) use ($expectedRequestBody): bool {
                self::assertEquals($expectedRequestBody, $capturedRequest->getParsedBody());
                return true;
            }))
            ->willReturn($this->createMock(ResponseInterface::class));

        $subject = new ControllerActionsMiddleware();
        $subject->process($request, $handler);
    }

    /**
     * This data provider is for prepared requests for company create action and
     * yellowpages2 search action.
     */
    public function requestActionDataProvider(): array
    {
        return [
            'company create action with null trades' => [
                'actual' => [
                    'tx_yellowpages2_directory' => [
                        'company' => [
                            'trades' => [
                                '',
                                '',
                            ],
                        ],
                    ],
                ],
                'expected' => [
                    'tx_yellowpages2_directory' => [
                        'company' => [
                            'trades' => [],
                        ],
                    ],
                ],
            ],
            'search action with arguments needs to be sanitized html special chars' => [
                'actual' => [
                    'tx_yellowpages2_directory' => [
                        'search' => 'bread & butter'
                    ],
                ],
                'expected' => [
                    'tx_yellowpages2_directory' => [
                        'search' => htmlspecialchars('bread & butter')
                    ],
                ],
            ],
        ];
    }
}
