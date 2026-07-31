<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class DatabaseBackupService
{
    public function create(string $destination): void
    {
        $this->ensureMysql();
        $sqlFile = $destination.'.sql';

        try {
            $process = new Process(array_merge([
                $this->binary('mysqldump'),
                '--single-transaction',
                '--quick',
                '--routines',
                '--events',
                '--triggers',
                '--hex-blob',
                '--add-drop-table',
                '--default-character-set=utf8mb4',
                '--no-tablespaces',
                '--result-file='.$sqlFile,
            ], $this->connectionArguments(), [$this->database()]));
            $process->setEnv(['MYSQL_PWD' => (string) $this->connection()['password']]);
            $process->setTimeout(3600);
            $process->run();

            if (! $process->isSuccessful()) {
                throw new RuntimeException('MySQL could not create the database dump: '.$this->processError($process));
            }

            $this->compress($sqlFile, $destination);
        } finally {
            if (is_file($sqlFile)) {
                unlink($sqlFile);
            }
        }
    }

    public function restore(string $source): array
    {
        $this->ensureMysql();
        $input = $this->openBackup($source);

        try {
            $process = new Process(array_merge([
                $this->binary('mysql'),
                '--binary-mode',
                '--default-character-set=utf8mb4',
            ], $this->connectionArguments(), [$this->database()]));
            $process->setEnv(['MYSQL_PWD' => (string) $this->connection()['password']]);
            $process->setInput($input);
            $process->setTimeout(3600);
            $process->run();

            if (! $process->isSuccessful()) {
                throw new RuntimeException('MySQL could not restore the database dump: '.$this->processError($process));
            }
        } finally {
            fclose($input);
        }

        return ['database' => $this->database()];
    }

    private function connection(): array
    {
        return config('database.connections.'.config('database.default'), []);
    }

    private function database(): string
    {
        $database = (string) ($this->connection()['database'] ?? '');

        return $database !== '' ? $database : throw new RuntimeException('The MySQL database name is not configured.');
    }

    private function ensureMysql(): void
    {
        if (! in_array($this->connection()['driver'] ?? null, ['mysql', 'mariadb'], true)) {
            throw new RuntimeException('Native SQL backup currently supports MySQL and MariaDB databases.');
        }
    }

    private function connectionArguments(): array
    {
        $connection = $this->connection();
        $arguments = ['--user='.(string) ($connection['username'] ?? '')];

        if (filled($connection['unix_socket'] ?? null)) {
            $arguments[] = '--socket='.$connection['unix_socket'];
        } else {
            $arguments[] = '--host='.(string) ($connection['host'] ?? '127.0.0.1');
            $arguments[] = '--port='.(string) ($connection['port'] ?? 3306);
        }

        return $arguments;
    }

    private function binary(string $name): string
    {
        $configured = $this->connection()[$name.'_binary'] ?? null;
        $candidates = array_filter([
            $configured,
            (new ExecutableFinder)->find($name),
            '/Applications/MAMP/Library/bin/'.$name,
            '/Applications/MAMP/bin/mysql/bin/'.$name,
        ]);

        foreach ($candidates as $candidate) {
            if (is_executable($candidate)) {
                return $candidate;
            }
        }

        throw new RuntimeException("The {$name} utility was not found. Configure its path in the environment.");
    }

    private function compress(string $source, string $destination): void
    {
        $input = fopen($source, 'rb');
        $output = gzopen($destination, 'wb9');

        if ($input === false || $output === false) {
            is_resource($input) && fclose($input);
            is_resource($output) && gzclose($output);
            throw new RuntimeException('The database dump could not be compressed.');
        }

        try {
            while (! feof($input)) {
                gzwrite($output, (string) fread($input, 1024 * 1024));
            }
        } finally {
            fclose($input);
            gzclose($output);
        }
    }

    private function openBackup(string $source)
    {
        $header = file_get_contents($source, false, null, 0, 2);
        $input = $header === "\x1f\x8b" ? gzopen($source, 'rb') : fopen($source, 'rb');

        return $input !== false ? $input : throw new RuntimeException('The database dump could not be opened.');
    }

    private function processError(Process $process): string
    {
        return trim($process->getErrorOutput()) ?: 'The command exited unsuccessfully.';
    }
}
