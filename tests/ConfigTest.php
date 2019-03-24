<?php

namespace SobanVuex\LumenBin\Console\Tests;

use PHPUnit\Framework\TestCase;
use SobanVuex\LumenBin\Console\Config;

final class ConfigTest extends TestCase
{
    protected $file;

    protected function setUp(): void
    {
        $this->file = getenv('LUMENBIN_CONFIG');

        if (is_file($this->file)) {
            unlink($this->file);
        }
    }

    public function testSave(): void
    {
        $url = 'http://example.com';
        $secret = sha1(time());

        $config = new Config(compact('url', 'secret'));
        $config->save();

        $this->assertSame(compact('url', 'secret'), $config->all());
        $this->assertFileExists($this->file);
    }

    public function testLoad(): void
    {
        $url = 'http://example.com';
        $secret = sha1(time());

        file_put_contents($this->file, json_encode(compact('url', 'secret')));
        $config = new Config([]);
        $config->load();

        $this->assertSame(compact('url', 'secret'), $config->all());
    }
}
