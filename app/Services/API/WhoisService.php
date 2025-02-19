<?php

namespace App\Services\API;

use Illuminate\Support\Facades\Cache;
use RuntimeException;

class WhoisService
{
    public const WHOIS_SERVER = 'whois.iana.org';
    public const WHOIS_PORT = 43;
    public const CONNECTION_TIMEOUT = 10;
    public function lookup(string $input): array
    {
        $cacheKey = $this->generateCacheKey($input);

        if ($cachedResult = $this->getCachedResult($cacheKey)) {
            return $cachedResult;
        }

        $whoisServer = $this->fetchWhoisServer($input);
        if (!$whoisServer) {
            throw new RuntimeException(__('api.whois.error.no_server'));
        }

        $whoisData = $this->getWhoisData($input, $whoisServer);

        $rawWhois = $whoisData['whois'] ?? null;
        $parsedWhois = $this->parseWhois($rawWhois);

        $result = $this->prepareResult($rawWhois, $parsedWhois, $input);

        $this->cacheResult($cacheKey, $result);

        return $result;
    }

    private function generateCacheKey(string $domain): string
    {
        return "whois:$domain";
    }

    private function getCachedResult(string $cacheKey): ?array
    {
        return Cache::has($cacheKey) ? Cache::get($cacheKey) : null;
    }

    private function getWhoisData(string $input, string $whoisServer): array
    {
        $whoisData = $this->queryWhois($input, $whoisServer);

        $serverKeyRegex = '/Registrar WHOIS Server:\s*([\S]+)/i';
        if (isset($whoisData['whois']) && preg_match($serverKeyRegex, $whoisData['whois'], $matches)) {
            $registrarWhoisServer = trim($matches[1]);
            return $this->queryWhois($input, $registrarWhoisServer);
        }

        return $whoisData;
    }

    private function prepareResult(?string $rawWhois, array $parsedWhois, string $domain): array
    {
        return [
            'raw' => $rawWhois,
            'parsed' => $parsedWhois,
            'domain' => $domain,
        ];
    }

    private function cacheResult(string $cacheKey, array $result): void
    {
        Cache::put($cacheKey, $result, now()->addHours(24));
    }

    private function fetchWhoisServer(string $domain): ?string
    {
        $ianaResponse = $this->queryWhois($domain, self::WHOIS_SERVER);

        return isset($ianaResponse['whois'])
            ? $this->parseWhoisServer($ianaResponse['whois'])
            : null;
    }

    private function parseWhoisServer(?string $whoisData): ?string
    {
        if ($whoisData && preg_match('/whois:\s*([\S]+)/i', $whoisData, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    private function queryWhois(string $domain, string $server): array
    {
        $connection = @fsockopen($server, self::WHOIS_PORT, $errno, $errstr, self::CONNECTION_TIMEOUT);

        if (!$connection) {
            throw new RuntimeException(__('api.whois.error.connection_failed', ['server' => $server]));
        }

        fwrite($connection, $domain . "\r\n");
        $response = stream_get_contents($connection);
        fclose($connection);

        return ['whois' => $response];
    }

    private function parseWhois(?string $whoisText): array
    {
        if (!$whoisText) {
            return [];
        }
        $parsed = [];
        $lines = explode("\n", $whoisText);

        foreach ($lines as $line) {
            $line = trim($line);

            if (empty($line) || str_starts_with($line, '%') || str_starts_with($line, '#') || str_starts_with($line, '>>')) {
                continue;
            }

            if (preg_match('/^([^:]+):\s*(.*)$/', $line, $matches)) {
                $key = trim($matches[1]);
                $value = trim($matches[2]);

                if (isset($parsed[$key])) {
                    if (!is_array($parsed[$key])) {
                        $parsed[$key] = [$parsed[$key]];
                    }
                    $parsed[$key][] = $value;
                } else {
                    $parsed[$key] = $value;
                }
            }
        }

        return $parsed;
    }
}
