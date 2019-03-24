<?php

namespace SobanVuex\LumenBin\Console;

use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\CommandLoader\FactoryCommandLoader;

final class Application extends SymfonyApplication
{
    /**
     * Load the application commands.
     *
     * @return self
     */
    public function loadCommands(): self
    {
        $this->setCommandLoader(new FactoryCommandLoader([
            // no-op
        ]));

        return $this;
    }
}
