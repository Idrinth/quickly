<?php declare(strict_types = 1);

namespace Idrinth\Quickly;

use Idrinth\Quickly\DependencyInjection\Container;
use Idrinth\Quickly\DependencyInjection\DependencyNotFound;
use Psr\Container\ContainerInterface;

final class EnvironmentFactory implements ContainerFactory
{
    private ?ContainerInterface $container = null;
    public function __construct(
        private readonly string $configPath = __DIR__.'/../../../../.quickly',
        private readonly ?ContainerInterface $fallbackContainer = null
    ) {
    }
    public function createContainer(): ContainerInterface
    {
        if ($this->container instanceof ContainerInterface) {
            return $this->container;
        }
        $fallback = $this->fallbackContainer ?? new class implements ContainerInterface {
            public function get(string $id)
            {
                throw new DependencyNotFound("$id is not defined");
            }

            public function has(string $id): bool
            {
                return false;
            }
        };
        if ((!isset($_ENV['DI_USE_REFLECTION']) || strtolower($_ENV['DI_USE_REFLECTION']) !== 'true') && isset($_ENV['DI_USE_CONFIG_VALIDATION']) && strtolower($_ENV['DI_USE_CONFIG_VALIDATION']) === 'false') {
            include($this->configPath.'/Container.php');
            if (class_exists('Idrinth\\Quickly\\Built\\DependendyInjection\\Container')) {
                return $this->container = new \Idrinth\Quickly\Built\DependendyInjection\Container($_ENV, $fallback);
            }
        }
        $overwrites = include($this->configPath.'/overwrites.php');
        if (!is_array($overwrites)) {
            $overwrites = [];
        }
        $data = include($this->configPath.'/generated.php');
        if (is_array($data)) {
            return $this->container = new Container(
                $_ENV,
                $overwrites,
                $fallback,
                $data['constructors'] ?? [],
                $data['factories'] ?? [],
                $data['classAliases'] ?? [],
            );
        }
        return $this->container = new Container($_ENV, $overwrites, $fallback);
    }
}
