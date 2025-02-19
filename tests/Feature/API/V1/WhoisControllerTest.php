<?php

namespace Tests\Feature\API\V1;

use App\Services\API\WhoisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Tests\TestCase;
use Mockery;

class WhoisControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function testShouldSuccessfullyLookupValidDomain(): void
    {
        $domain = 'example.com';
        $expectedResponse = [
            'domain' => $domain,
            'raw' => 'Domain Name: example.com',
            'parsed' => [
                'Domain Name' => 'example.com',
                'Registrar' => 'Example Registrar',
            ],
        ];

        $this->mock(WhoisService::class, function ($mock) use ($expectedResponse) {
            $mock->shouldReceive('lookup')
                ->once()
                ->andReturn($expectedResponse);
        });

        $response = $this->postJson('/api/v1/whois', ['domain' => $domain]);

        $response->assertStatus(200)
            ->assertJson($expectedResponse);
    }

    public function testShouldReturnValidationErrorForInvalidDomain(): void
    {
        $response = $this->postJson('/api/v1/whois', ['domain' => 'i_d']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['domain']);
    }

    public function testShouldReturnErrorWhenDomainIsMissing(): void
    {
        $response = $this->postJson('/api/v1/whois', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['domain']);
    }

    public function testShouldHandleWhoisServerNotFoundError(): void
    {
        $domain = 'example.com';
        $errorMessage = __('api.whois.error.no_server');

        $this->mock(WhoisService::class, function ($mock) use ($errorMessage) {
            $mock->shouldReceive('lookup')
                ->once()
                ->andThrow(new RuntimeException($errorMessage));
        });

        $response = $this->postJson('/api/v1/whois', ['domain' => $domain]);

        $response->assertStatus(500)
            ->assertJson(['error' => $errorMessage]);
    }

    public function testShouldHandleConnectionError(): void
    {
        $domain = 'example.com';
        $errorMessage = 'api.whois.error.connection_failed';

        $this->mock(WhoisService::class, function ($mock) use ($errorMessage) {
            $mock->shouldReceive('lookup')
                ->once()
                ->andThrow(new RuntimeException($errorMessage));
        });

        $response = $this->postJson('/api/v1/whois', ['domain' => $domain]);

        $response->assertStatus(500)
            ->assertJson(['error' => $errorMessage]);
    }
}
