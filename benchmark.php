<?php

require_once 'vendor/autoload.php';

class A {}
class B {
    public function __construct(A $a)
    {
    }
}
class C {
    public function __construct(B $b)
    {
    }
}
class D {
    public function __construct(C $c, B $b, A $a)
    {
    }
}
class E {
    public function __construct(D $d, C $c, B $b)
    {
    }
}
class F {
    public function __construct(E $e, D $d, B $b)
    {
    }
}

echo "Starting with reflection (simple)\n";
$start = microtime(true);
for($i = 0; $i < 100000; $i++) {
    $devContainer = new \Idrinth\Quickly\DependencyInjection\Container(['DI_USE_REFLECTION' => 'true']);
    $devContainer->get(stdClass::class);
}
echo "Duration (simple): ".(microtime(true) - $start)." seconds per 100,000\n";
echo "Starting with configured (simple)\n";
$start = microtime(true);
for($i = 0; $i < 100000; $i++) {
    $prodContainer = new \Idrinth\Quickly\DependencyInjection\Container([], constructors: [
        stdClass::class => [],
    ]);
    $prodContainer->get(stdClass::class);
}
echo "Duration (simple): ".(microtime(true) - $start)." seconds per 100,000\n";
echo "Starting with reflection (medium)\n";
$start = microtime(true);
for($i = 0; $i < 100000; $i++) {
    $devContainer = new \Idrinth\Quickly\DependencyInjection\Container(['DI_USE_REFLECTION' => 'true']);
    $devContainer->get(F::class);
}
echo "Duration (medium): ".(microtime(true) - $start)." seconds per 100,000\n";
echo "Starting with configured (medium)\n";
$start = microtime(true);
for($i = 0; $i < 100000; $i++) {
    $prodContainer = new \Idrinth\Quickly\DependencyInjection\Container([], constructors: [
        F::class => [
            new \Idrinth\Quickly\DependencyInjection\Definitions\ClassObject(E::class),
            new \Idrinth\Quickly\DependencyInjection\Definitions\ClassObject(D::class),
            new \Idrinth\Quickly\DependencyInjection\Definitions\ClassObject(B::class),
        ],
        E::class => [
            new \Idrinth\Quickly\DependencyInjection\Definitions\ClassObject(D::class),
            new \Idrinth\Quickly\DependencyInjection\Definitions\ClassObject(C::class),
            new \Idrinth\Quickly\DependencyInjection\Definitions\ClassObject(B::class),
        ],
        D::class => [
            new \Idrinth\Quickly\DependencyInjection\Definitions\ClassObject(C::class),
            new \Idrinth\Quickly\DependencyInjection\Definitions\ClassObject(B::class),
            new \Idrinth\Quickly\DependencyInjection\Definitions\ClassObject(A::class),
        ],
        C::class => [
            new \Idrinth\Quickly\DependencyInjection\Definitions\ClassObject(B::class),
        ],
        B::class => [
            new \Idrinth\Quickly\DependencyInjection\Definitions\ClassObject(A::class),
        ],
        A::class => [],
    ]);
    $prodContainer->get(F::class);
}
echo "Duration (medium): ".(microtime(true) - $start)." seconds per 100,000\n";
