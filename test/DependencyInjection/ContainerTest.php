<?php

namespace Idrinth\Quickly\DependencyInjection;

use DateTime;
use Idrinth\Quickly\DependencyInjection\Definitions\ClassObject;
use Idrinth\Quickly\DependencyInjection\Definitions\Environment;
use Idrinth\Quickly\Example1;
use Idrinth\Quickly\Example10;
use Idrinth\Quickly\Example11;
use Idrinth\Quickly\Example2;
use Idrinth\Quickly\Example3;
use Idrinth\Quickly\Example3Interface;
use Idrinth\Quickly\Example4;
use Idrinth\Quickly\Example5;
use Idrinth\Quickly\Example6;
use Idrinth\Quickly\Example7;
use Idrinth\Quickly\Example8;
use Idrinth\Quickly\Example9;
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
    #[Test]
    public function canBuildExample5WithFactory(): void
    {
        $container = new Container(['EX_AMPLE' => 'value', 'DI_USE_REFLECTION' => 'true'], constructors: [
            Example5::class => [
                new Definitions\Factory(Example4::class, 'example4', 'abc', Example5::class),
            ],
        ], factories: [
            Example4::class => Example3Interface::class,
        ]);
        self::assertTrue($container->has('Factory:'.Example4::class));
        $example5 = $container->get('ClassObject:'.Example5::class);
        self::assertInstanceOf(Example5::class, $example5);
        self::assertInstanceOf(Example3::class, $example5->abc);
    }
    #[Test]
    public function canBuildExample3InterfaceWithAlias(): void
    {
        $container = new Container(['EX_AMPLE' => 'value', 'DI_USE_REFLECTION' => 'true'], classAliases: [
            Example3Interface::class => Example3::class,
        ]);
        self::assertFalse($container->has('Alias:'.Example3::class));
        self::assertTrue($container->has('Alias:'.Example3Interface::class));
        $example3 = $container->get('Alias:'.Example3Interface::class);
        self::assertInstanceOf(Example3::class, $example3);
    }
    #[Test]
    public function canBuildExample6WithFactory(): void
    {
        $container = new Container(['EX_AMPLE' => 'value', 'DI_USE_REFLECTION' => 'true']);
        self::assertFalse($container->has('ClassObject:'.Example6::class));
        $example5 = $container->get('ClassObject:'.Example6::class);
        self::assertInstanceOf(Example6::class, $example5);
        self::assertInstanceOf(Example5::class, $example5->example5);
    }
    #[Test]
    public function canNotBuildExample7WithMultipleOptions(): void
    {
        $container = new Container(['EX_AMPLE' => 'value', 'DI_USE_REFLECTION' => 'true']);
        self::assertFalse($container->has('ClassObject:'.Example7::class));
        $this->expectException(DependencyUnbuildable::class);
        $container->get('ClassObject:'.Example7::class);
    }
    #[Test]
    public function canBuildExample8WithMultipleButNullableOptions(): void
    {
        $container = new Container(['EX_AMPLE' => 'value', 'DI_USE_REFLECTION' => 'true']);
        self::assertFalse($container->has('ClassObject:'.Example8::class));
        self::assertInstanceOf(Example8::class, $container->get('ClassObject:'.Example8::class));
    }
    #[Test]
    public function canNotInjectIntegersFromEnvironmentInExample9(): void
    {
        $container = new Container(['EX_AMPLE' => 'value', 'DI_USE_REFLECTION' => 'true']);
        self::assertFalse($container->has('ClassObject:'.Example9::class));
        $this->expectException(DependencyNotFound::class);
        $container->get('ClassObject:'.Example9::class);
    }
    #[Test]
    public function canInjectStringFromEnvironmentInExample11(): void
    {
        $container = new Container(['EX_AMPLE' => 'value', 'DI_USE_REFLECTION' => 'true']);
        self::assertFalse($container->has('ClassObject:'.Example11::class));
        self::assertInstanceOf(Example11::class, $container->get('ClassObject:'.Example11::class));
    }
    #[Test]
    public function getUnknownIdThrowsDependencyNotFound(): void
    {
        $container = new Container([]);
        $this->expectException(DependencyTypeUnknown::class);
        $container->get('does-not-exist');
    }
    #[Test]
    public function reflectionDisabledFallsBackToDefinitionsOnly(): void
    {
        $c = new Container([]);
        $this->expectException(DependencyNotFound::class);
        $c->get('ClassObject:\\Some\\Class\\That\\Isnt\\Defined');
    }
    #[Test]
    public function exceptionsInConstructorAreWrapped(): void
    {
        $c = new Container(['DI_USE_REFLECTION' => 'true']);
        $this->expectException(DependencyUnbuildable::class);
        $c->get('ClassObject:'.Example10::class);
    }
}
