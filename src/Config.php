<?php

namespace SobanVuex\LumenBin\Console;

use Noodlehaus\AbstractConfig;

class Config extends AbstractConfig
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    /**
     * Load the configuration from file.
     *
     * @return self
     */
    public function load(): self
    {
        $config = is_file($file = $this->file()) ? json_decode((string) file_get_contents($file), true) : [];
        $this->data = array_merge($this->data, $config);

        return $this;
    }

    /**
     * Save the configuration to file.
     *
     * @return self
     */
    public function save(): self
    {
        $file = $this->file();
        if (!is_dir($dir = dirname($file))) {
            mkdir($dir, 0700, true);
        }
        file_put_contents($file, json_encode($this->all(), JSON_PRETTY_PRINT));

        return $this;
    }

    /**
     * Provide default configuration.
     *
     * @return array
     */
    protected function getDefaults(): array
    {
        return [
            'url' => null,
            'secret' => null,
        ];
    }

    /**
     * Get the config file path.
     *
     * @return string
     */
    protected function file(): string
    {
        return getenv('LUMENBIN_CONFIG') ?: getenv('HOME').'/.lumenbin/config.json';
    }
}
