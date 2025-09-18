<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Nucleus\Container\Container;

class ContainerTest extends TestCase
{
    public function test_bind_creates_new_instance_each_time()
    {
        $container = new Container();

        $container->bind(stdClass::class, fn () => new stdClass());

        $obj1 = $container->make(stdClass::class);
        $obj2 = $container->make(stdClass::class);

        $this->assertInstanceOf(stdClass::class, $obj1);
        $this->assertInstanceOf(stdClass::class, $obj2);
        $this->assertNotSame($obj1, $obj2, 'bind() should return different instances');
    }

    public function test_singleton_returns_same_instance()
    {
        $container = new Container();

        $container->singleton(stdClass::class, fn () => new stdClass());

        $obj1 = $container->make(stdClass::class);
        $obj2 = $container->make(stdClass::class);

        $this->assertInstanceOf(stdClass::class, $obj1);
        $this->assertSame($obj1, $obj2, 'singleton() should always return the same instance');
    }

    public function test_make_auto_resolves_dependencies()
    {
        $container = new Container();

        $obj = $container->make(ClassWithDependency::class);

        $this->assertInstanceOf(ClassWithDependency::class, $obj);
        $this->assertInstanceOf(DependencyStub::class, $obj->dependency);
    }

    public function test_make_uses_default_values_for_untyped_params()
    {
        $container = new Container();

        $obj = $container->make(ClassWithDefaultParam::class);

        $this->assertInstanceOf(ClassWithDefaultParam::class, $obj);
        $this->assertEquals('default', $obj->value);
    }

    public function test_has_returns_true_if_binding_exists()
    {
        $container = new Container();
        $container->bind(stdClass::class, fn () => new stdClass());

        $this->assertTrue($container->has(stdClass::class));
    }

    public function test_has_returns_false_if_binding_not_exists()
    {
        $container = new Container();

        $this->assertFalse($container->has(stdClass::class));
    }

    public function test_get_returns_instance_if_binding_exists()
    {
        $container = new Container();
        $container->bind(stdClass::class, fn () => new stdClass());

        $obj = $container->get(stdClass::class);

        $this->assertInstanceOf(stdClass::class, $obj);
    }

    public function test_get_throws_exception_if_no_binding()
    {
        $this->expectException(Exception::class);

        $container = new Container();
        $container->get('NonExistentService');
    }

    public function test_make_with_mixed_params()
    {
        $this->expectException(Exception::class);
        
        $container = new Container();

        $container->make(ClassWithMixedParams::class);

    }
}

/**
 * Stubs pour tester la résolution automatique des dépendances
 */
class DependencyStub {}

class ClassWithDependency
{
    public DependencyStub $dependency;

    public function __construct(DependencyStub $dependency)
    {
        $this->dependency = $dependency;
    }
}

class ClassWithDefaultParam
{
    public string $value;

    public function __construct(string $value = 'default')
    {
        $this->value = $value;
    }
}

class ClassWithMixedParams
{
    public DependencyStub $dependency;
    public string $value;
    public int $count;

    public function __construct(DependencyStub $dependency, int $count, string $value = 'default')
    {
        $this->dependency = $dependency;
        $this->value = $value;
        $this->count = $count;
    }
}
