<?php

namespace App\Commands;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;

class CompileBinaryCommand extends Command
{
    protected $signature = 'compile-binary';

    protected $description = 'Compile the binary for the current version';

    public function handle(): void
    {
        $version = File::json(base_path('composer.json'))['version'];

        info("Compiling binary for version {$version}");

        $destination = base_path('bin/php-parser-' . $version);

        if (File::exists($destination)) {
            if (!confirm('The binary already exists. Do you want to overwrite it?', false)) {
                return;
            }
        } else {
            confirm('Continue?', true);
        }

        exec('mv ' . base_path('.env') . ' ' . base_path('.env.bak'));

        $extensions = collect([
            'bcmath',
            'calendar',
            'ctype',
            'curl',
            'dba',
            'dom',
            'exif',
            'filter',
            'fileinfo',
            'iconv',
            'mbstring',
            'mbregex',
            'openssl',
            'pcntl',
            'pdo',
            'pdo_mysql',
            'pdo_sqlite',
            'phar',
            'posix',
            'readline',
            'simplexml',
            'sockets',
            'sqlite3',
            'tokenizer',
            'xml',
            'xmlreader',
            'xmlwriter',
            'zip',
            'zlib',
            'sodium',
        ])->implode(',');

        $spc = base_path('spc');

        collect([
            sprintf('%s download --with-php=8.2 --for-extensions="%s"', $spc, $extensions),
            sprintf('%s build --build-micro --build-cli "%s"', $spc, $extensions),
            sprintf('%s micro:combine %s -O %s', $spc, base_path('builds/php-parser'), $destination),
        ])->each(function (string $command) {
            Process::run($command, function (string $type, string $output) {
                echo $output;
            });
        });

        exec('mv ' . base_path('.env.bak') . ' ' . base_path('.env'));
    }
}
