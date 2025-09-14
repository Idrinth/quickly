<?php

namespace Idrinth\Quickly\DependencyInjection;

use BadMethodCallException;
use DateTime;
use Idrinth\Quickly\DependencyInjection\Definitions\ClassObject;
use Idrinth\Quickly\Example1;
use Idrinth\Quickly\Example10;
use Idrinth\Quickly\Example11;
use Idrinth\Quickly\Example12;
use Idrinth\Quickly\Example13;
use Idrinth\Quickly\Example14;
use Idrinth\Quickly\Example15;
use Idrinth\Quickly\Example16;
use Idrinth\Quickly\Example17;
use Idrinth\Quickly\Example18;
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
use Psr\Container\ContainerInterface;
use stdClass;

#[CoversClass(Container::class)]
class ContainerTest extends TestCase
{
    private ContainerInterface $fallbackContainer;
    protected function setUp(): void
    {
        parent::setUp();
        $this->fallbackContainer = new class implements ContainerInterface {

            public function get(string $id)
            {
                throw new BadMethodCallException('Fallback not implemented');
            }

            public function has(string $id): bool
            {
                return false;
            }
        };
    }

    #[Test]
    public function canGetEnvironmentVariables(): void
    {
        $container = new Container(['EX_AMPLE' => 'value'], [], $this->fallbackContainer);
        self::assertTrue($container->has('Environment:exAmple'));
        self::assertEquals('value', $container->get('Environment:exAmple'));
    }
    #[Test]
    public function canGetClass(): void
    {
        $container = new Container(['EX_AMPLE' => 'value', 'DI_USE_REFLECTION' => 'True'], [], $this->fallbackContainer);
        self::assertFalse($container->has('ClassObject:stdClass'));
        self::assertInstanceOf(stdClass::class, $container->get('ClassObject:stdClass'));
    }
    #[Test]
    public function canGetClassWithOptionalDependencies(): void
    {
        $container = new Container(['EX_AMPLE' => 'value', 'DI_USE_REFLECTION' => 'trUe'], [], $this->fallbackContainer);
        self::assertFalse($container->has('ClassObject:DateTime'));
        self::assertInstanceOf(DateTime::class, $container->get('ClassObject:DateTime'));
    }
    #[Test]
    public function getsTheSameObjectEveryTime(): void
    {
        $container = new Container(['EX_AMPLE' => 'value', 'DI_USE_REFLECTION' => 'tRue'], [], $this->fallbackContainer);
        self::assertFalse($container->has('ClassObject:DateTime'));
        $datetime = $container->get('ClassObject:DateTime');
        self::assertInstanceOf(DateTime::class, $datetime);
        self::assertSame($datetime, $container->get('ClassObject:DateTime'));
    }
    #[Test]
    public function failsToBuildUnknownObjects(): void
    {
        $container = new Container(['EX_AMPLE' => 'value'], [], $this->fallbackContainer);
        $this->expectException(DependencyNotFound::class);
        self::assertFalse($container->has('ClassObject:DateTime'));
        $container->get('ClassObject:DateTime');
    }
    #[Test]
    public function failsToBuildNoneExistingClasses(): void
    {
        $container = new Container(['EX_AMPLE' => 'value', 'DI_USE_REFLECTION' => 'truE'], [], $this->fallbackContainer);
        $this->expectException(DependencyNotFound::class);
        self::assertFalse($container->has('ClassObject:DateTimeMutable'));
        $container->get('ClassObject:DateTimeMutable');
    }
    #[Test]
    public function BuildsNoneExistingClassesWithAlias(): void
    {
        $container = new Container(['EX_AMPLE' => 'value', 'DI_USE_REFLECTION' => 'truE'], [], $this->fallbackContainer, classAliases: ['DateTimeMutable' => DateTime::class]);
        self::assertTrue($container->has('Alias:DateTimeMutable'));
        self::assertInstanceOf(DateTime::class, $container->get('Alias:DateTimeMutable'));
    }
    #[Test]
    public function failsToBuildUnresolvableDependencies(): void
    {
        $container = new Container(['EX_AMPLE' => 'value', 'DI_USE_REFLECTION' => 'true'], [], $this->fallbackContainer);
        $this->expectException(DependencyUnbuildable::class);
        self::assertFalse($container->has('ClassObject:PDO'));
        $container->get('ClassObject:PDO');
    }
    #[Test]
    public function canBuildExample1WithDependencies(): void
    {
        $container = new Container(['EX_AMPLE' => 'value', 'DI_USE_REFLECTION' => 'true'], [], $this->fallbackContainer);
        self::assertFalse($container->has('ClassObject:'.Example1::class));
        $example1 = $container->get('ClassObject:'.Example1::class);
        self::assertInstanceOf(Example1::class, $example1);
        self::assertInstanceOf(DateTime::class, $example1->date);
        self::assertEquals(0, $example1->time);
    }
    #[Test]
    public function canNotBuildExample15WithDependenciesByChoosingAliasFirst(): void
    {
        $container = new Container(['EX_AMPLE' => 'value', 'DI_USE_REFLECTION' => 'true'], [], $this->fallbackContainer, constructors: [
            'ClassObject:'.Example15::class => [new Definitions\Factory(Example3Interface::class, 'eee', 'sdsd', Example15::class)],
        ], classAliases: [
            Example3Interface::class => Example3::class,
        ]);
        self::assertFalse($container->has(Example15::class));
        self::assertInstanceOf(Example15::class, $container->get(Example15::class));
    }
    #[Test]
    public function canBuildExample2WithDependencies(): void
    {
        $container = new Container(['EX_AMPLE' => 'value', 'DI_USE_REFLECTION' => 'true'], [], $this->fallbackContainer);
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
        $container = new Container(['EX_AMPLE' => 'value', 'DI_USE_REFLECTION' => 'true'], [], $this->fallbackContainer);
        self::assertFalse($container->has('ClassObject:'.Example3::class));
        $example3 = $container->get('ClassObject:'.Example3::class);
        self::assertInstanceOf(Example3::class, $example3);
        self::assertEquals('value', $example3->envExAmple);
    }
    #[Test]
    public function canBuildExample5WithFactory(): void
    {
        $container = new Container(['EX_AMPLE' => 'value', 'DI_USE_REFLECTION' => 'true'], [], $this->fallbackContainer, constructors: [
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
    public function canNotBuildExample5WithNoneFactory(): void
    {
        $container = new Container(['EX_AMPLE' => 'value', 'DI_USE_REFLECTION' => 'true'], [], $this->fallbackContainer, constructors: [
            Example5::class => [
                new Definitions\Factory(Example1::class, 'example4', 'abc', Example5::class),
            ],
        ], factories: [
            Example1::class => Example3Interface::class,
        ]);
        self::assertTrue($container->has('Factory:'.Example1::class));
        $this->expectException(DependencyUnbuildable::class);
        $container->get('ClassObject:'.Example5::class);
    }
    #[Test]
    public function canNotRegisterNonDefinition(): void
    {
        $this->expectException(InvalidDependency::class);
        new Container([], [], $this->fallbackContainer, constructors: [
            Example5::class => [
                new stdClass(),
            ],
        ]);
    }
    #[Test]
    public function canNotRegisterNamelessClassAsConstructor(): void
    {
        $this->expectException(InvalidClassName::class);
        new Container([], [], $this->fallbackContainer, constructors: [
            '' => [],
        ]);
    }
    #[Test]
    public function canNotRegisterNamelessClassAsFactory(): void
    {
        $this->expectException(InvalidClassName::class);
        new Container([], [], $this->fallbackContainer, factories: [
            '' => 'B',
        ]);
    }
    #[Test]
    public function canNotRegisterNamelessClassAsFactoryTarget(): void
    {
        $this->expectException(InvalidClassName::class);
        new Container([], [], $this->fallbackContainer, factories: [
            'A' => '',
        ]);
    }
    #[Test]
    public function canBuildExample3InterfaceWithAlias(): void
    {
        $container = new Container(['EX_AMPLE' => 'value', 'DI_USE_REFLECTION' => 'true'], [], $this->fallbackContainer, classAliases: [
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
        $container = new Container(['EX_AMPLE' => 'value', 'DI_USE_REFLECTION' => 'true'], [], $this->fallbackContainer);
        self::assertFalse($container->has('ClassObject:'.Example6::class));
        $example5 = $container->get('ClassObject:'.Example6::class);
        self::assertInstanceOf(Example6::class, $example5);
        self::assertInstanceOf(Example5::class, $example5->example5);
    }
    #[Test]
    public function canBuildExample7WithMultipleOptions(): void
    {
        $container = new Container(['EX_AMPLE' => 'value', 'DI_USE_REFLECTION' => 'true'], [], $this->fallbackContainer);
        self::assertFalse($container->has('ClassObject:'.Example7::class));
        self::assertInstanceOf(Example7::class, $container->get('ClassObject:'.Example7::class));
    }
    #[Test]
    public function canNotBuildExample18WithImpossibleUnionOptions(): void
    {
        $container = new Container(['EX_AMPLE' => 'value', 'DI_USE_REFLECTION' => 'true'], [], $this->fallbackContainer);
        self::assertFalse($container->has('ClassObject:'.Example18::class));
        $this->expectException(DependencyUnresolvable::class);
        $container->get('ClassObject:'.Example18::class);
    }
    #[Test]
    public function canBuildExample8WithMultipleButNullableOptions(): void
    {
        $container = new Container(['EX_AMPLE' => 'value', 'DI_USE_REFLECTION' => 'true'], [], $this->fallbackContainer);
        self::assertFalse($container->has('ClassObject:'.Example8::class));
        self::assertInstanceOf(Example8::class, $container->get('ClassObject:'.Example8::class));
    }
    #[Test]
    public function canNotInjectIntegersFromEnvironmentInExample9(): void
    {
        $container = new Container(['EX_AMPLE' => 'value', 'DI_USE_REFLECTION' => 'true'], [], $this->fallbackContainer);
        self::assertFalse($container->has('ClassObject:'.Example9::class));
        $this->expectException(DependencyNotFound::class);
        $container->get('ClassObject:'.Example9::class);
    }
    #[Test]
    public function canInjectStringFromEnvironmentInExample11(): void
    {
        $container = new Container(['EX_AMPLE' => 'value', 'DI_USE_REFLECTION' => 'true'], [], $this->fallbackContainer);
        self::assertFalse($container->has('ClassObject:'.Example11::class));
        self::assertInstanceOf(Example11::class, $container->get('ClassObject:'.Example11::class));
    }
    #[Test]
    public function getUnknownIdThrowsDependencyNotFound(): void
    {
        $container = new Container([], [], $this->fallbackContainer);
        $this->expectException(DependencyNotFound::class);
        $container->get('does-not-exist');
    }
    #[Test]
    public function getUnknownTypeThrowsDependencyTypeUnknown(): void
    {
        $container = new Container([], [], $this->fallbackContainer);
        $this->expectException(DependencyTypeUnknown::class);
        $container->get('Type:does-not-exist');
    }
    #[Test]
    public function reflectionDisabledFallsBackToDefinitionsOnly(): void
    {
        $container = new Container([], [], $this->fallbackContainer);
        $this->expectException(DependencyNotFound::class);
        $container->get('ClassObject:\\Some\\Class\\That\\Isnt\\Defined');
    }
    #[Test]
    public function exceptionsInConstructorAreWrapped(): void
    {
        $container = new Container(['DI_USE_REFLECTION' => 'true'], [], $this->fallbackContainer);
        $this->expectException(DependencyUnbuildable::class);
        $container->get('ClassObject:'.Example10::class);
    }
    #[Test]
    public function classesAreDefaultedTo(): void
    {
        $container = new Container(['DI_USE_REFLECTION' => 'true'], [], $this->fallbackContainer);
        self::assertFalse($container->has(Example1::class));
        self::assertInstanceOf(Example1::class, $container->get(Example1::class));
        self::assertTrue($container->has(Example1::class));
    }
    #[Test]
    public function exceptionsAreWrappedInConfiguredMode(): void
    {
        $container = new Container([], [], $this->fallbackContainer, constructors: [
            Example12::class => [new ClassObject(Example1::class)],
            Example1::class => [new ClassObject(DateTime::class)],
            DateTime::class => [],
        ]);
        self::assertTrue($container->has(Example12::class));
        $this->expectException(DependencyUnbuildable::class);
        $container->get(Example12::class);
    }
    #[Test]
    public function nullableIsUsedWhenNoValueIsAvailable(): void
    {
        $container = new Container(['DI_USE_REFLECTION' => 'true'], [], $this->fallbackContainer);
        self::assertFalse($container->has(Example13::class));
        self::assertInstanceOf(Example13::class, $container->get(Example13::class));
    }
    #[Test]
    public function resolvingLazyWorksEvenOutsideReflection(): void
    {
        $container = new Container([], [], $this->fallbackContainer, constructors: [
            Example14::class => [new ClassObject(Example10::class, true)],
            Example10::class => [],
        ]);
        self::assertTrue($container->has(Example14::class));
        self::assertTrue($container->has(Example10::class));
        self::assertInstanceOf(Example14::class, $container->get(Example14::class));
    }
    #[Test]
    public function reflectionExceptionsAreCaughtAndWrapped(): void
    {
        $container = new Container(['DI_USE_REFLECTION' => 'true', 'EX_AMPLE' => '---'], [], $this->fallbackContainer);
        self::assertInstanceOf(
            Example3::class,
            $container->get('Factory:'.Example4::class.':test:param:'.Example5::class)
        );
    }
    #[Test]
    public function circularDependenciesCantBeBuild(): void
    {
        $container = new Container(['DI_USE_REFLECTION' => 'true'], [], $this->fallbackContainer);
        $this->expectException(CircularDependency::class);
        $container->get(Example16::class);
    }
    #[Test]
    public function overwritesArePreferredOverGuessing(): void
    {
        $container = new Container(['DI_USE_REFLECTION' => 'true'], [
            Example17::class => [
                'someValue' => new ClassObject(Example1::class, true)
            ],
        ], $this->fallbackContainer);
        self::assertInstanceOf(Example17::class, $container->get(Example17::class));
    }
    #[Test]
    public function emptyParametersAreNotAllowedForOverwrites(): void
    {
        $this->expectException(InvalidPropertyName::class);
        new Container(['DI_USE_REFLECTION' => 'true'], [
            Example17::class => [
                '' => new ClassObject(Example1::class, true)
            ],
        ], $this->fallbackContainer);
    }
    #[Test]
    public function emptyClassesAreNotAllowedForOverwrites(): void
    {
        $this->expectException(InvalidClassName::class);
        new Container(['DI_USE_REFLECTION' => 'true'], [
            '' => [
                'someValue' => new ClassObject(Example1::class, true)
            ],
        ], $this->fallbackContainer);
    }
    #[Test]
    public function invalidDefinitionObjectsAreNotAllowedForOverwrites(): void
    {
        $this->expectException(InvalidDependency::class);
        new Container(['DI_USE_REFLECTION' => 'true'], [
            Example17::class => [
                'someValue' => new stdClass()
            ],
        ], $this->fallbackContainer);
    }
    #[Test]
    public function cachedInstancesAreReturnedOnSameIdGiven(): void
    {
        $container = new Container(['DI_USE_REFLECTION' => 'true'], [], $this->fallbackContainer);
        self::assertSame($container->get(Example1::class), $container->get(Example1::class));
    }
    #[Test]
    public function environmentsAreReturnedProperly(): void
    {
        $container = new Container(['DI_USE_REFLECTION' => 'true'], [], $this->fallbackContainer);
        self::assertEquals('true', $container->get('Environment:diUseReflection'));
    }
}
