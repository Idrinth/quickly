<?php

namespace Idrinth\Quickly;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

#[CoversClass(EnvironmentFactory::class)]
class EnvironmentFactoryTest extends TestCase
{
    #[Test]
    public function createsDIContainer(): void
    {
        $factory = new EnvironmentFactory();
        $this->assertInstanceOf(ContainerInterface::class, $factory->createContainer());
    }
    #[Test]
    public function createsSingletonDIContainer(): void
    {
        $factory = new EnvironmentFactory();
        $first = $factory->createContainer();
        $this->assertSame($first, $factory->createContainer());
    }
    #[Test]
    public function createsDIContainerFromConfig(): void
    {
        $factory = new EnvironmentFactory(__DIR__ .'/../fixtures');
        $container = $factory->createContainer();
        $this->assertTrue($container->has('Alias:A'));
    }
}
