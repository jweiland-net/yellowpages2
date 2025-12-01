<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tests\Unit\Middleware;

use JWeiland\Yellowpages2\Middleware\ControllerActionsMiddleware;
use JWeiland\Yellowpages2\Modifier\HtmlspecialcharsModifier;
use JWeiland\Yellowpages2\Modifier\RemoveEmptyTradesModifier;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Frontend\Http\RequestHandler;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class ControllerActionsTest extends UnitTestCase
{
    #[DataProvider('requestActionDataProvider')]
    public function testRequestAction(array $requestBody, array $expectedRequestBody): void
    {
        // Make a request body with the prepared dataset request body
        $request = new ServerRequest('https://a-random-domain.com/mysite/', 'POST');
        $request = $request->withParsedBody($requestBody);

        // Here we will assert the modifier request insider handler
        $handler = $this->createMock(RequestHandler::class);
        $handler->expects(self::atLeast(1))
            ->method('handle')
            ->with(self::callback(static function (ServerRequestInterface $capturedRequest) use ($expectedRequestBody): bool {
                self::assertEquals($expectedRequestBody, $capturedRequest->getParsedBody());
                return true;
            }))
            ->willReturn($this->createMock(ResponseInterface::class));

        // adding modifiers for the handler
        $subject = new ControllerActionsMiddleware();
        $subject->addModifier(new HtmlspecialcharsModifier());
        $subject->addModifier(new RemoveEmptyTradesModifier());
        $subject->process($request, $handler);
    }

    /**
     * This data provider is for prepared requests for company create action and
     * yellowpages2 search action.
     */
    public static function requestActionDataProvider(): array
    {
        return [
            'company create action with null trades' => [
                'requestBody' => [
                    'tx_yellowpages2_directory' => [
                        'company' => [
                            'trades' => [
                                '',
                                '',
                            ],
                        ],
                    ],
                ],
                'expectedRequestBody' => [
                    'tx_yellowpages2_directory' => [
                        'company' => [
                            'trades' => [],
                        ],
                    ],
                ],
            ],
            'search action with arguments needs to be sanitized html special chars' => [
                'requestBody' => [
                    'tx_yellowpages2_directory' => [
                        'search' => 'bread & butter',
                    ],
                ],
                'expectedRequestBody' => [
                    'tx_yellowpages2_directory' => [
                        'search' => htmlspecialchars('bread & butter'),
                    ],
                ],
            ],
        ];
    }
}
