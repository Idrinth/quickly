<?php

namespace Idrinth\Quickly\DependencyInjection;

use DateTime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Container::class)]
class ContainerTest extends TestCase
{
    #[Test]
    public function canGetEnvironmentVariables(): void
    {
        $container = new Container(['EX_AMPLE' => 'value']);
        self::assertTrue($container->has('Environment:exAmple'));
        self::assertEquals('value', $container->get('Environment:exAmple'));
    }
    #[Test]
    public function canGetClass(): void
    {
        $container = new Container(['EX_AMPLE' => 'value', 'DI_USE_REFLECTION' => 'True']);
        self::assertFalse($container->has('ClassObject:stdClass'));
        self::assertInstanceOf(\stdClass::class, $container->get('ClassObject:stdClass'));
    }
    #[Test]
    public function canGetClassWithOptionalDependencies(): void
    {
        $container = new Container(['EX_AMPLE' => 'value', 'DI_USE_REFLECTION' => 'trUe']);
        self::assertFalse($container->has('ClassObject:DateTime'));
        self::assertInstanceOf(DateTime::class, $container->get('ClassObject:DateTime'));
    }
    #[Test]
    public function getsTheSameObjectEveryTime(): void
    {
        $container = new Container(['EX_AMPLE' => 'value', 'DI_USE_REFLECTION' => 'tRue']);
        self::assertFalse($container->has('ClassObject:DateTime'));
        $datetime = $container->get('ClassObject:DateTime');
        self::assertInstanceOf(DateTime::class, $datetime);
        self::assertSame($datetime, $container->get('ClassObject:DateTime'));
    }
    #[Test]
    public function failsToBuildUnknownObjects(): void
    {
        $container = new Container(['EX_AMPLE' => 'value']);
        $this->expectException(DependencyNotFound::class);
        self::assertFalse($container->has('ClassObject:DateTime'));
        $container->get('ClassObject:DateTime');
    }
    #[Test]
    public function failsToBuildNoneExistingClasses(): void
    {
        $container = new Container(['EX_AMPLE' => 'value', 'DI_USE_REFLECTION' => 'truE']);
        $this->expectException(DependencyNotFound::class);
        self::assertFalse($container->has('ClassObject:DateTimeMutable'));
        $container->get('ClassObject:DateTimeMutable');
    }
    #[Test]
    public function failsToBuildUnresolvableDependencies(): void
    {
        $container = new Container(['EX_AMPLE' => 'value', 'DI_USE_REFLECTION' => 'true']);
        $this->expectException(DependencyUnbuildable::class);
        self::assertFalse($container->has('ClassObject:PDO'));
        $container->get('ClassObject:PDO');
    }
}
