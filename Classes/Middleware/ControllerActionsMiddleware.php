<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Middleware;

use JWeiland\Yellowpages2\Modifier\RequestFieldModifierInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * This middleware handles the request variable for different actions
 * 1. Remove empty trades from request to prevent errors while storing/updating the FE record [Management]
 * 2. Sanitize search keyword with htmlspecialchars [Search]
 */
final class ControllerActionsMiddleware implements MiddlewareInterface
{
    /**
     * Plugin namespaces of the plugins which receive POST requests
     */
    private const PLUGIN_NAMESPACES = [
        'tx_yellowpages2_management',
        'tx_yellowpages2_search',
    ];

    /**
     * @var array<int, RequestFieldModifierInterface>
     */
    private array $modifiers = [];

    public function addModifier(RequestFieldModifierInterface $modifier): void
    {
        $this->modifiers[] = $modifier;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestBody = $request->getParsedBody();

        // Continue processing the request if it doesn't have the plugin variables
        if (!is_array($requestBody) || !$this->hasPluginArguments($requestBody)) {
            return $handler->handle($request);
        }

        $this->applyModifiers($requestBody);

        // Handle altered request object
        return $handler->handle($request->withParsedBody($requestBody));
    }

    /**
     * @param array<string, mixed> $requestBody
     */
    private function hasPluginArguments(array $requestBody): bool
    {
        foreach (self::PLUGIN_NAMESPACES as $pluginNamespace) {
            if (isset($requestBody[$pluginNamespace])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $requestBody
     */
    private function applyModifiers(array &$requestBody): void
    {
        foreach ($this->modifiers as $modifier) {
            $requestBody = $modifier->modify($requestBody);
        }
    }
}
