<?php

namespace Idrinth\Quickly\DependencyInjection;

use DateTime;
use Idrinth\Quickly\Example1;
use Idrinth\Quickly\Example2;
use Idrinth\Quickly\Example3;
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
    public function BuildsNoneExistingClassesWithAlias(): void
    {
        $container = new Container(['EX_AMPLE' => 'value', 'DI_USE_REFLECTION' => 'truE'], classAliases: ['DateTimeMutable' => DateTime::class]);
        self::assertTrue($container->has('Alias:DateTimeMutable'));
        self::assertInstanceOf(DateTime::class, $container->get('Alias:DateTimeMutable'));
    }
    #[Test]
    public function failsToBuildUnresolvableDependencies(): void
    {
        $container = new Container(['EX_AMPLE' => 'value', 'DI_USE_REFLECTION' => 'true']);
        $this->expectException(DependencyUnbuildable::class);
        self::assertFalse($container->has('ClassObject:PDO'));
        $container->get('ClassObject:PDO');
    }
    #[Test]
    public function canBuildExample1WithDependencies(): void
    {
        $container = new Container(['EX_AMPLE' => 'value', 'DI_USE_REFLECTION' => 'true']);
        self::assertFalse($container->has('ClassObject:'.Example1::class));
        $example1 = $container->get('ClassObject:'.Example1::class);
        self::assertInstanceOf(Example1::class, $example1);
        self::assertInstanceOf(DateTime::class, $example1->date);
        self::assertEquals(0, $example1->time);
    }
    #[Test]
    public function canBuildExample2WithDependencies(): void
    {
        $container = new Container(['EX_AMPLE' => 'value', 'DI_USE_REFLECTION' => 'true']);
        self::assertFalse($container->has('ClassObject:'.Example2::class));
        $example2 = $container->get('ClassObject:'.Example2::class);
        self::assertInstanceOf(Example2::class, $example2);
        self::assertInstanceOf(Example1::class, $example2->example1);
        self::assertSame($example2->example11, $example2->example1);
        self::assertNull($example2->a);
    }
    #[Test]
    public function canBuildExample3WithDependencies(): void
    {
        $container = new Container(['EX_AMPLE' => 'value', 'DI_USE_REFLECTION' => 'true']);
        self::assertFalse($container->has('ClassObject:'.Example3::class));
        $example3 = $container->get('ClassObject:'.Example3::class);
        self::assertInstanceOf(Example3::class, $example3);
        self::assertEquals('value', $example3->envExAmple);
    }
}
