<?php

declare(strict_types=1);

namespace Janalis\Doctrineviz\Tests;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Janalis\Doctrineviz\ColumnDefault;
use Janalis\Doctrineviz\Command\DoctrinevizCommand;
use Janalis\Doctrineviz\DependencyInjection\DoctrinevizExtension;
use Janalis\Doctrineviz\DoctrinevizBundle;
use Janalis\Doctrineviz\SchemaDotRenderer;
use Janalis\Doctrineviz\SchemaEntityDiagram;
use Janalis\Doctrineviz\SchemaExporter;
use Janalis\Doctrineviz\SchemaLinks;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;

#[CoversClass(DoctrinevizBundle::class)]
#[CoversClass(DoctrinevizExtension::class)]
#[UsesClass(DoctrinevizCommand::class)]
#[UsesClass(SchemaDotRenderer::class)]
#[UsesClass(SchemaExporter::class)]
#[UsesClass(SchemaLinks::class)]
#[UsesClass(ColumnDefault::class)]
#[UsesClass(SchemaEntityDiagram::class)]
final class BundleTest extends TestCase
{
    public function testExtensionRegistersTheCommandAndRenderer(): void
    {
        $bundle = new DoctrinevizBundle();
        self::assertSame($bundle->getContainerExtension(), $bundle->getContainerExtension());
        $extension = $bundle->getContainerExtension();
        self::assertInstanceOf(DoctrinevizExtension::class, $extension);
        self::assertSame('doctrineviz', $extension->getAlias());
        $container = new ContainerBuilder();
        $extension->load([], $container);
        self::assertTrue($container->hasDefinition(SchemaDotRenderer::class));
        self::assertTrue($container->getDefinition(DoctrinevizCommand::class)->hasTag('console.command'));
    }

    public function testMinimalConsumerWithoutOrmCanRunTheCommand(): void
    {
        $kernel = new class extends Kernel {
            private readonly string $cacheKey;

            public function __construct()
            {
                $this->cacheKey = bin2hex(random_bytes(8));
                parent::__construct('test', false);
            }

            public function registerBundles(): iterable
            {
                yield new FrameworkBundle();
                yield new DoctrineBundle();
                yield new DoctrinevizBundle();
            }

            public function registerContainerConfiguration(LoaderInterface $loader): void
            {
                $loader->load(static function (ContainerBuilder $container): void {
                    $container->loadFromExtension('framework', ['secret' => bin2hex(random_bytes(16)), 'test' => true]);
                    $container->loadFromExtension('doctrine', ['dbal' => [
                        'driver' => 'pdo_sqlite',
                        'memory' => true,
                        'logging' => false,
                    ]]);
                });
            }

            public function getCacheDir(): string
            {
                return dirname(__DIR__) . '/var/test-kernel/' . $this->cacheKey;
            }

            public function getLogDir(): string
            {
                return dirname(__DIR__) . '/var/log';
            }
        };
        $previousExceptionHandler = set_exception_handler(null);
        restore_exception_handler();
        try {
            $application = new Application($kernel);
            $tester = new CommandTester($application->find('doctrine:schema:graphviz'));
            $tester->execute([]);
            self::assertSame(0, $tester->getStatusCode());
            self::assertSame(
                "digraph database {\n  graph [rankdir=LR];\n  node [shape=plain];\n}\n",
                $tester->getDisplay(),
            );
        } finally {
            $kernel->shutdown();
            // Symfony 7.4 installs an exception handler while booting the framework.
            $currentExceptionHandler = set_exception_handler(null);
            restore_exception_handler();
            if ($currentExceptionHandler !== $previousExceptionHandler) {
                restore_exception_handler();
            }
            new Filesystem()->remove($kernel->getCacheDir());
        }
    }
}
