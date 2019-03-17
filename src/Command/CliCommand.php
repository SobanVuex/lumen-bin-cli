<?php

namespace SobanVuex\LumenBin\Console\Command;

use BaconQrCode\Renderer\PlainTextRenderer as QrRenderer;
use BaconQrCode\Writer as QrWriter;
use SobanVuex\LumenBin\Console\Bin;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class CliCommand extends Command
{
    /**
     * File Argument.
     *
     * @var string
     */
    const ARGUMENT_FILE = 'file';

    /**
     * Raw raw URL option.
     *
     * @var string
     */
    const OPTION_RAW = 'raw';

    /**
     * QR code option.
     *
     * @var string
     */
    const OPTION_QR_CODE = 'qr-code';

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription($this->description())
            ->addArgument(self::ARGUMENT_FILE, InputArgument::OPTIONAL, 'A path to file')
            ->addOption(self::OPTION_RAW, 'r', InputOption::VALUE_NONE, 'Get raw URL')
            ->addOption(self::OPTION_QR_CODE, 'c', InputOption::VALUE_NONE, 'Show QR Code')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bin = new Bin($input->getArgument(self::ARGUMENT_FILE));
        if (!$bin->valid) {
            $this
                ->getApplication()
                ->find('help')
                ->run(new ArrayInput(['command_name' => $this->getName()]), $output)
            ;
        } else {
            $result = $this->post($bin);
            $url = $this->url($result->key, $input->getOption(self::OPTION_RAW));
            $output->writeln($url);
            if ($input->getOption(self::OPTION_QR_CODE)) {
                $output->write($this->qrCode($url));
            }
        }
    }

    /**
     * Generate the description for the command.
     *
     * @return string
     */
    protected function description(): string
    {
        return sprintf(
            'Command line interface for lumen-bin. %s',
            getenv('LUMENBIN_HOST')
                ? sprintf('Host: %s', getenv('LUMENBIN_HOST'))
                : 'Set "LUMENBIN_HOST" in your Environment.'
        );
    }

    /**
     * Save Bin online.
     *
     * @param Bin $bin Contents of the input
     *
     * @return \stdClass
     */
    protected function post(Bin $bin): \stdClass
    {
        if (!getenv('LUMENBIN_HOST')) {
            throw new RuntimeException('Empty/Missing environment "LUMENBIN_HOST".');
        }
        $url = sprintf('%s/new', getenv('LUMENBIN_HOST'));
        if (!filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
            throw new RuntimeException(sprintf('Host "%s" is not a valid URL.', $url));
        }
        $context = stream_context_create([
            'http' => $this->postContext() + $this->postContent($bin),
        ]);
        $response = @file_get_contents($url, false, $context);
        if (false === $response) {
            throw new RuntimeException(sprintf('Upload failed: %s', error_get_last()['message']));
        }

        return json_decode($response);
    }

    /**
     * Prepare request context.
     *
     * @return array
     */
    protected function postContext(): array
    {
        $app = $this->getApplication();

        return [
            'method' => 'POST',
            'header' => [
                'Content-Type: application/x-www-form-urlencoded',
                sprintf('User-Agent: %s/%s', $app->getName(), $app->getVersion()),
            ],
        ];
    }

    /**
     * Prepare request data.
     *
     * @return array
     */
    protected function postContent(Bin $bin): array
    {
        $data = (string) $bin;

        return ['content' => http_build_query(compact('data'))];
    }

    /**
     * Return the QR code to display.
     *
     * @param string $url URL to encode
     *
     * @return string
     */
    protected function qrCode(string $url): string
    {
        return (new QrWriter(new QrRenderer(1)))->writeString($url);
    }

    /**
     * Return the URL to send to.
     *
     * @param string $key URL key
     * @param bool   $raw Return a raw URL
     *
     * @return string
     */
    protected function url(string $key, ?bool $raw = false): string
    {
        return sprintf('%s/%s%s', getenv('LUMENBIN_HOST'), $raw ? 'r/' : '', $key);
    }
}
