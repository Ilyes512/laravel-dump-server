<?php

declare(strict_types=1);

namespace Ilyes512\DumpServer;

use Illuminate\Console\Command;
use InvalidArgumentException;
use Symfony\Component\VarDumper\Cloner\Data;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Symfony\Component\VarDumper\Server\DumpServer;
use Symfony\Component\VarDumper\Command\Descriptor\CliDescriptor;
use Symfony\Component\VarDumper\Command\Descriptor\HtmlDescriptor;

class DumpServerCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'dump-server {--format=cli : The output format (cli,html).}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start the dump server to collect dump information.';

    /**
     * The Dump server.
     *
     * @var \Symfony\Component\VarDumper\Server\DumpServer
     */
    protected $server;

    /**
     * DumpServerCommand constructor.
     *
     * @return void
     */
    public function __construct(DumpServer $dumpServer)
    {
        $this->server = $dumpServer;

        parent::__construct();
    }

    /**
     * Handle the command.
     */
    public function handle(): void
    {
        switch ($format = $this->option('format')) {
            case 'cli':
                $descriptor = new CliDescriptor(new CliDumper);
                break;
            case 'html':
                $descriptor = new HtmlDescriptor(new HtmlDumper);
                break;
            default:
                throw new InvalidArgumentException(sprintf('Unsupported format "%s".', $format));
        }

        $symfonyStyle = new SymfonyStyle($this->input, $this->output);

        $errorIo = $symfonyStyle->getErrorStyle();
        $errorIo->title('Laravel Var Dump Server');

        $this->server->start();

        $errorIo->success(sprintf('Server listening on %s', $this->server->getHost()));
        $errorIo->comment('Quit the server with CONTROL-C.');

        $this->server->listen(function (Data $data, array $context, int $clientId) use ($descriptor, $symfonyStyle): void {
            $descriptor->describe($symfonyStyle, $data, $context, $clientId);
        });
    }
}
