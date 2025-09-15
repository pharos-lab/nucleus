<?php

namespace Tests\Unit;

use Nucleus\Config\Environment;
use PHPUnit\Framework\TestCase;

class EnvironmentTest extends TestCase
{
    protected function setUp(): void
    {
        // Toujours réinitialiser avant chaque test
        Environment::reset();
    }

    public function testLoadAndGetVariable(): void
    {
        $envFile = __DIR__ . '/../Fakes/.env';
        Environment::load($envFile);

        $this->assertEquals('localhost', Environment::get('APP_HOST'));
        $this->assertEquals('NucleusAppTesting', Environment::get('APP_NAME'));
    }

    public function testGetWithDefault(): void
    {
        $this->assertEquals('default_value', Environment::get('NON_EXISTENT_KEY', 'default_value'));
    }

    public function testSetVariableAtRuntime(): void
    {
        Environment::set('NEW_KEY', 'value123');

        $this->assertEquals('value123', Environment::get('NEW_KEY'));
    }

    public function testResetClearsVariables(): void
    {
        Environment::set('SOME_KEY', 'value');
        Environment::reset();

        $this->assertNull(Environment::get('SOME_KEY'));
    }

    public function testBooleanCasting(): void
    {
        Environment::set('TRUE_KEY', 'true');
        Environment::set('FALSE_KEY', 'false');

        $this->assertTrue(Environment::get('TRUE_KEY'));
        $this->assertFalse(Environment::get('FALSE_KEY'));
    }

    public function testIntegerCasting(): void
    {
        Environment::set('INT_KEY', '42');
        $this->assertSame(42, Environment::get('INT_KEY'));
    }

    public function testFloatCasting(): void
    {
        Environment::set('FLOAT_KEY', '3.14');
        $this->assertSame(3.14, Environment::get('FLOAT_KEY'));
    }
}