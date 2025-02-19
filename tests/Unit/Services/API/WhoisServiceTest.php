<?php

namespace Tests\Unit\Services\API;

use App\Services\API\WhoisService;
use Illuminate\Support\Facades\Cache;
use ReflectionMethod;
use RuntimeException;
use Tests\TestCase;

class WhoisServiceTest extends TestCase
{
    private WhoisService $whoisService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->whoisService = new WhoisService();
        Cache::flush();
    }

    public function testShouldReturnCachedResult(): void
    {
        $domain = 'example.com';
        $cachedResult = [
            'raw' => 'Cached WHOIS data',
            'parsed' => ['Domain' => 'example.com'],
            'domain' => $domain
        ];

        Cache::put("whois:$domain", $cachedResult, now()->addHour());

        $result = $this->whoisService->lookup($domain);

        $this->assertEquals($cachedResult, $result);
    }

    public function testShouldParseWhoisDataCorrectly(): void
    {
        $rawWhois = "Domain Name: example.com\nRegistrar: Example Registrar\nCreation Date: 2024-01-01";

        $method = new ReflectionMethod(WhoisService::class, 'parseWhois');

        $result = $method->invoke($this->whoisService, $rawWhois);

        $this->assertEquals([
            'Domain Name' => 'example.com',
            'Registrar' => 'Example Registrar',
            'Creation Date' => '2024-01-01'
        ], $result);
    }

    public function testShouldThrowExceptionForInvalidWhoisServer(): void
    {
        $domain = 'invalid-domain.invalid';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(__('api.whois.error.no_server'));

        $this->whoisService->lookup($domain);
    }

    public function testShouldThrowExceptionForConnectionFailure(): void
    {
        $whoisServer = 'non.existent.server';
        $method = new ReflectionMethod(WhoisService::class, 'queryWhois');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(__('api.whois.error.connection_failed', ['server' => $whoisServer]));

        $method->invoke($this->whoisService, 'example.com', $whoisServer);
    }

    public function testShouldParseWhoisServerCorrectly(): void
    {
        $method = new ReflectionMethod(WhoisService::class, 'parseWhoisServer');

        $whoisData = "whois:        whois.example.com\n";

        $result = $method->invoke($this->whoisService, $whoisData);

        $this->assertEquals('whois.example.com', $result);
    }
}
