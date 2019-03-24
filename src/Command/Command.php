<?php

namespace SobanVuex\LumenBin\Console\Command;

use SobanVuex\LumenBin\Console\Config;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

abstract class Command extends SymfonyCommand
{
    /**
     * @var Config
     */
    private $config;

    /**
     * Retrieve the application configuration.
     *
     * @return Config
     */
    public function config(): Config
    {
        if (null === $this->config) {
            $this->config = new Config();
            $this->config->load();
        }

        return $this->config;
    }
}
