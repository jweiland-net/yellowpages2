<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\DependenyInjection;

use JWeiland\Yellowpages2\Middleware\ControllerActionsMiddleware;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ModifierServiceProvider implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(ControllerActionsMiddleware::class)) {
            return;
        }

        $this->addModifiersToMiddleware($container);
    }

    private function addModifiersToMiddleware(ContainerBuilder $container): void
    {
        $definition = $container->getDefinition(ControllerActionsMiddleware::class);
        $taggedServices = $container->findTaggedServiceIds('yellowpages2.request.modifiers');

        foreach ($taggedServices as $id => $tags) {
            $reference = new Reference($id);
            $definition->addMethodCall('addModifier', [$reference]);
        }
    }
}
