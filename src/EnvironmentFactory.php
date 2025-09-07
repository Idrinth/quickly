<?php declare(strict_types = 1);

namespace Idrinth\Quickly;

use Idrinth\Quickly\DependencyInjection\Container;
use Psr\Container\ContainerInterface;

final class EnvironmentFactory implements ContainerFactory
{
    private ?ContainerInterface $container = null;
    public function __construct(
        private readonly string $configPath = '/../../../../.quickly'
    ) {
    }
    public function createContainer(): ContainerInterface
    {
        if ($this->container instanceof ContainerInterface) {
            return $this->container;
        }
        if ((!isset($_ENV['DI_USE_REFLECTION']) || strtolower($_ENV['DI_USE_REFLECTION']) !== 'true') && isset($_ENV['DI_USE_CONFIG_VALIDATION']) && strtolower($_ENV['DI_USE_CONFIG_VALIDATION']) === 'false') {
            include(__DIR__ . $this->configPath.'/Container.php');
            if (class_exists('Idrinth\\Quickly\\Built\\DependendyInjection\\Container')) {
                return $this->container = new \Idrinth\Quickly\Built\DependendyInjection\Container();
            }
        }
        $data = include(__DIR__ . $this->configPath.'/generated.php');
        if (is_array($data)) {
            return $this->container = new Container(
                $_ENV,
                $data['constructors'] ?? [],
                $data['factories'] ?? [],
                $data['classAliases'] ?? [],
            );
        }
        return $this->container = new Container($_ENV);
    }
}
