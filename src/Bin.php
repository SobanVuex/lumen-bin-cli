<?php

namespace SobanVuex\LumenBin\Console;

use Symfony\Component\Console\Exception\RuntimeException;

final class Bin
{
    /**
     * Is the input a valid resource.
     *
     * @var bool
     */
    public $valid;
    /**
     * The contents from the input source.
     *
     * @var string
     */
    private $contents;

    /**
     * Create a bin from an input.
     *
     * @param null|string $file Input
     */
    public function __construct(?string $file)
    {
        $stream = $file ? $this->fromFile($file) : $this->fromInput();
        if ($this->valid = $this->validate($stream)) {
            if (false === ($contents = stream_get_contents($stream))) {
                throw new RuntimeException(sprintf('Input read failed: %s', error_get_last()['message']));
            }
            if (empty($contents)) {
                throw new RuntimeException('File/Input is empty.');
            }
            $this->contents = (string) $contents;
        }
        fclose($stream);
    }

    /**
     * Get bin contents.
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->contents;
    }

    /**
     * Create from file.
     *
     * @param string $file Path to the file
     *
     * @return resource
     */
    private function fromFile(string $file)
    {
        if (!is_file($file)) {
            throw new RuntimeException(sprintf('File "%s" is not found.', $file));
        }
        if (!is_readable($file)) {
            throw new RuntimeException(sprintf('File "%s" is not readable.', $file));
        }
        $stream = @fopen($file, 'r');
        if (!\is_resource($stream)) {
            throw new RuntimeException(sprintf('Input stream error: %s', error_get_last()['message']));
        }

        return $stream;
    }

    /**
     * Create from standard input.
     *
     * @return resource
     */
    private function fromInput()
    {
        $stream = @fopen('php://stdin', 'r');
        if (!\is_resource($stream)) {
            throw new RuntimeException(sprintf('Input stream error: %s', error_get_last()['message']));
        }
        stream_set_blocking($stream, false);

        return $stream;
    }

    /**
     * Check input resource type.
     *
     * @param resource $stream Input resource
     *
     * @return bool
     */
    private function validate($stream): bool
    {
        $meta = stream_get_meta_data($stream);
        switch ($meta['wrapper_type']) {
            case 'PHP':
                return false === $meta['seekable']; // Seekable PHP equals NO input
            case 'plainfile':
            default:
                return true;
        }
    }
}
