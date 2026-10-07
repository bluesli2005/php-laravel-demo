<?php

use Symfony\Component\Process\Process;

$projectRoot = dirname(__DIR__);
$envPath = $projectRoot.'/.env';

if (! is_file($envPath)) {
    fwrite(STDERR, "Missing .env file.\n");
    exit(1);
}

$contents = file_get_contents($envPath);

if ($contents === false || ! preg_match('/^APP_KEY=(.*)$/m', $contents, $matches)) {
    fwrite(STDERR, "Missing APP_KEY entry in .env.\n");
    exit(1);
}

if (trim($matches[1], " \t\n\r\0\x0B\"'") !== '') {
    fwrite(STDOUT, "APP_KEY is already set; keeping the existing key.\n");
    exit(0);
}

require $projectRoot.'/vendor/autoload.php';

$process = new Process([PHP_BINARY, $projectRoot.'/artisan', 'key:generate', '--no-interaction'], $projectRoot);

exit($process->run(static function (string $type, string $output): void {
    fwrite($type === Process::ERR ? STDERR : STDOUT, $output);
}));
