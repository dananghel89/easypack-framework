<?php

/**
 * Runs the fixture site on PHP's built-in web server for HTTP-level tests.
 *
 * Every request is a fresh PHP request, so the framework's constants are
 * defined again each time. The `site` option picks the fixture site
 * (tests/Fixtures/sites/<site>) through the X-Epack-Site header.
 */

namespace Tests\Support;

use RuntimeException;

final class Server
{
    /** @var resource|null */
    private static mixed $process = null;
    private static int $port = 0;
    private static string $log = '';

    public static function request(string $method, string $path, array $options = []): array
    {
        self::start();

        $headers = ['X-Epack-Site: ' . ($options['site'] ?? 'default')];

        if (!empty($options['define'])) {
            $pairs = [];

            foreach ($options['define'] as $name => $value) {
                $pairs[] = $name . '=' . (is_bool($value) ? ($value ? 'true' : 'false') : $value);
            }

            $headers[] = 'X-Epack-Define: ' . implode(';', $pairs);
        }

        if (!empty($options['cookies'])) {
            $headers[] = 'Cookie: ' . http_build_query($options['cookies'], '', '; ');
        }

        foreach ($options['headers'] ?? [] as $name => $value) {
            $headers[] = "{$name}: {$value}";
        }

        $ch = curl_init('http://127.0.0.1:' . self::$port . $path);

        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => strtoupper($method),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 10,
        ]);

        if (isset($options['form'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($options['form']));
        }

        $response = curl_exec($ch);

        if ($response === false) {
            throw new RuntimeException('Request failed: ' . curl_error($ch) . "\n" . self::log());
        }

        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $headers = self::parseHeaders(substr($response, 0, $headerSize));

        $cookies = [];

        foreach ($headers['set-cookie'] ?? [] as $line) {
            [$pair] = explode(';', $line, 2);
            [$name, $value] = array_pad(explode('=', $pair, 2), 2, '');
            $cookies[trim($name)] = urldecode(trim($value));
        }

        return [
            'status'  => $status,
            'headers' => $headers,
            'cookies' => $cookies,
            'body'    => substr($response, $headerSize),
        ];
    }

    public static function url(string $path = '/'): string
    {
        self::start();

        return 'http://127.0.0.1:' . self::$port . $path;
    }

    public static function log(): string
    {
        return self::$log && is_file(self::$log) ? (string) file_get_contents(self::$log) : '';
    }

    private static function start(): void
    {
        if (self::$process) {
            return;
        }

        $socket = stream_socket_server('tcp://127.0.0.1:0');
        $name = stream_socket_get_name($socket, false);
        fclose($socket);

        self::$port = (int) substr(strrchr($name, ':'), 1);
        self::$log = tempnam(sys_get_temp_dir(), 'epack-server-');

        $root = dirname(__DIR__) . '/Fixtures/public';

        $command = [
            PHP_BINARY,
            '-d', 'xdebug.mode=off',
            '-d', 'html_errors=0',
            '-S', '127.0.0.1:' . self::$port,
            '-t', $root,
            $root . '/index.php',
        ];

        self::$process = proc_open($command, [
            0 => ['pipe', 'r'],
            1 => ['file', self::$log, 'a'],
            2 => ['file', self::$log, 'a'],
        ], $pipes, $root);

        if (!is_resource(self::$process)) {
            throw new RuntimeException('Could not start the built-in web server.');
        }

        register_shutdown_function(static function () {
            proc_terminate(self::$process);
            @unlink(self::$log);
        });

        // wait until the server accepts connections (curl, so no PHP warnings while it starts)
        $deadline = microtime(true) + 10;

        while (microtime(true) < $deadline) {
            $ch = curl_init('http://127.0.0.1:' . self::$port . '/');
            curl_setopt_array($ch, [CURLOPT_NOBODY => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 1]);

            if (curl_exec($ch) !== false) {
                return;
            }

            usleep(50000);
        }

        throw new RuntimeException("The built-in web server didn't start.\n" . self::log());
    }

    private static function parseHeaders(string $raw): array
    {
        $headers = [];

        foreach (preg_split('/\r?\n/', trim($raw)) as $i => $line) {
            if ($i === 0 || !str_contains($line, ':')) {
                continue;
            }

            [$name, $value] = explode(':', $line, 2);
            $headers[strtolower(trim($name))][] = trim($value);
        }

        return $headers;
    }
}
