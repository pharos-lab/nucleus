<?php


namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Nucleus\Config\Config;
use Nucleus\Config\Environment;

final class ConfigTest extends TestCase
{
    protected Config $config;

    protected function setUp(): void
    {
        Environment::load(__DIR__ . '/../Fakes/.env');

        $this->config = new Config(__DIR__ . '/../Fakes/config');
    }

    public function testCanLoadSimpleConfigValue(): void
    {
        $this->assertSame('local', $this->config->get('app.env'));
    }

    public function testCanLoadNestedConfigValues(): void
    {
        $this->assertSame('168.148.2.254', $this->config->get('database.connections.mysql.host'));
    }

    public function testReturnsDefaultValueIfKeyNotFound(): void
    {
        $this->assertSame('default', $this->config->get('non.existing', 'default'));
    }

    public function testCanOverrideConfigAtRuntime(): void
    {
        $this->config->set('app.env', 'test');
        $this->assertSame('test', $this->config->get('app.env'));
    }

    public function testLoadsMultipleConfigFiles(): void
    {
        $this->assertSame('testing', $this->config->get('app.env'));
        $this->assertSame('168.148.2.254', $this->config->get('database.connections.mysql.host'));
    }
}
