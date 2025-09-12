<?php

use PHPUnit\Framework\TestCase;
use Nucleus\Config\Config;

final class ConfigTest extends TestCase
{
    protected Config $config;

    protected function setUp(): void
    {
        $this->config = new Config(__DIR__ . '/../Fakes/config');
    }

    public function testCanLoadSimpleConfigValue(): void
    {
        $this->assertSame('dev', $this->config->get('app.env'));
    }

    public function testCanLoadNestedConfigValues(): void
    {
        $this->assertSame('127.0.0.1', $this->config->get('database.connections.mysql.host'));
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
        $this->assertSame('dev', $this->config->get('app.env'));
        $this->assertSame('127.0.0.1', $this->config->get('database.connections.mysql.host'));
    }
}
