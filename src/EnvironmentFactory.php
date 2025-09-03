<?php declare(strict_types = 1);

namespace Idrinth\Quickly;

use Idrinth\Quickly\DependencyInjection\Container;
use Psr\Container\ContainerInterface;

final class EnvironmentFactory implements ContainerFactory
{
    private ?ContainerInterface $container = null;
    public function __construct(
        private readonly string $configPath = '/../../../../.quickly/generated.php'
    ) {
    }
    public function createContainer(): ContainerInterface
    {
        if ($this->container instanceof ContainerInterface) {
            return $this->container;
        }
        $data = include(__DIR__ . $this->configPath);
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
