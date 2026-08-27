<?php

declare(strict_types=1);

use JWeiland\Yellowpages2\DependenyInjection\ModifierServiceProvider;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container, ContainerBuilder $containerBuilder) {
    $containerBuilder->addCompilerPass(new ModifierServiceProvider('yellowpages2.request.modifiers'));
};
