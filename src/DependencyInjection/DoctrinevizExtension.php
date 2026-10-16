<?php

declare(strict_types=1);

namespace Janalis\Doctrineviz\DependencyInjection;

use Janalis\Doctrineviz\Command\DoctrinevizCommand;
use Janalis\Doctrineviz\SchemaDotRenderer;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

final class DoctrinevizExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $container->register(SchemaDotRenderer::class);
        $container
            ->register(DoctrinevizCommand::class)
            ->setArguments([new Reference('doctrine'), new Reference(SchemaDotRenderer::class)])
            ->addTag('console.command');
    }
}
