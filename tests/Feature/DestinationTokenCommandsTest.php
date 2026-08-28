<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Laravel\Sanctum\PersonalAccessToken;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DestinationTokenCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_issue_command_creates_one_token_limited_to_the_read_ability(): void
    {
        $user = User::factory()->create(['email' => 'operator@example.com']);

        $this->artisan('destination-token:issue', ['email' => 'operator@example.com'])
            ->expectsOutputToContain('It will not be shown again.')
            ->assertSuccessful();

        $this->assertSame(1, PersonalAccessToken::query()->count());

        $token = PersonalAccessToken::query()->first();

        $this->assertTrue($token->tokenable->is($user));
        $this->assertSame('destination-api', $token->name);
        $this->assertSame(['destinations:read'], $token->abilities);
    }

    public function test_the_issue_command_rejects_an_unknown_user(): void
    {
        $this->artisan('destination-token:issue', ['email' => 'nobody@example.com'])
            ->expectsOutputToContain('No user exists with that email address.')
            ->assertFailed();

        $this->assertSame(0, PersonalAccessToken::query()->count());
    }

    public function test_the_issue_command_refuses_a_duplicate_token_name(): void
    {
        User::factory()->create(['email' => 'operator@example.com']);

        $this->artisan('destination-token:issue', ['email' => 'operator@example.com'])->assertSuccessful();

        $this->artisan('destination-token:issue', ['email' => 'operator@example.com'])
            ->expectsOutputToContain('already exists for this user')
            ->assertFailed();

        $this->artisan('destination-token:issue', ['email' => 'operator@example.com', '--name' => 'reporting'])->assertSuccessful();

        $this->assertSame(['destination-api', 'reporting'], PersonalAccessToken::query()->orderBy('id')->pluck('name')->all());
    }

    public function test_the_revoke_command_removes_only_the_named_token_of_that_user(): void
    {
        $user = User::factory()->create(['email' => 'operator@example.com']);
        $other = User::factory()->create();
        $user->createToken('destination-api', ['destinations:read']);
        $user->createToken('reporting', ['destinations:read']);
        $other->createToken('destination-api', ['destinations:read']);

        $this->artisan('destination-token:revoke', ['email' => 'operator@example.com'])
            ->expectsOutputToContain('Token destination-api revoked for operator@example.com.')
            ->assertSuccessful();

        $this->assertSame(['reporting'], $user->tokens()->pluck('name')->all());
        $this->assertSame(['destination-api'], $other->tokens()->pluck('name')->all());
    }

    public function test_the_revoke_command_rejects_an_unknown_user(): void
    {
        $this->artisan('destination-token:revoke', ['email' => 'nobody@example.com'])
            ->expectsOutputToContain('No user exists with that email address.')
            ->assertFailed();
    }

    public function test_the_revoke_command_rejects_a_missing_token(): void
    {
        $user = User::factory()->create(['email' => 'operator@example.com']);
        $user->createToken('reporting', ['destinations:read']);

        $this->artisan('destination-token:revoke', ['email' => 'operator@example.com'])
            ->expectsOutputToContain('No token named destination-api exists for this user.')
            ->assertFailed();

        $this->assertSame(1, PersonalAccessToken::query()->count());
    }

    public function test_the_issued_token_is_shown_once_and_stored_only_as_a_hash(): void
    {
        User::factory()->create(['email' => 'operator@example.com']);
        Destination::factory()->create(['name' => 'Kyoto']);

        Artisan::call('destination-token:issue', ['email' => 'operator@example.com']);

        preg_match('/^(\d+\|\S+)/m', Artisan::output(), $matches);

        $this->assertNotEmpty($matches, 'The plain token was not printed.');

        [$id, $secret] = explode('|', $matches[1], 2);
        $stored = PersonalAccessToken::query()->findOrFail($id);

        $this->assertSame(hash('sha256', $secret), $stored->token);
        $this->assertStringNotContainsString($secret, $stored->token);
        $this->assertSame(0, PersonalAccessToken::query()->where('token', $secret)->count());

        $this->withToken($matches[1])->getJson('/api/destinations')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Kyoto');
    }

    public function test_the_issued_token_expires_after_ninety_days_by_default(): void
    {
        $this->travelTo(Carbon::create(2026, 8, 28, 12));
        User::factory()->create(['email' => 'operator@example.com']);

        $this->artisan('destination-token:issue', ['email' => 'operator@example.com'])
            ->expectsOutputToContain('It expires on 2026-11-26 12:00 UTC (90 days from now).')
            ->assertSuccessful();

        $this->assertSame('2026-11-26 12:00:00', PersonalAccessToken::query()->first()->expires_at->toDateTimeString());
    }

    public function test_a_custom_expiration_in_days_is_respected(): void
    {
        $this->travelTo(Carbon::create(2026, 8, 28, 12));
        User::factory()->create(['email' => 'operator@example.com']);

        $this->artisan('destination-token:issue', ['email' => 'operator@example.com', '--expires' => '30'])
            ->expectsOutputToContain('It expires on 2026-09-27 12:00 UTC (30 days from now).')
            ->assertSuccessful();

        $this->assertSame('2026-09-27 12:00:00', PersonalAccessToken::query()->first()->expires_at->toDateTimeString());
    }

    #[DataProvider('invalidExpirations')]
    public function test_invalid_expiration_values_are_rejected_without_creating_a_token(string $expires): void
    {
        User::factory()->create(['email' => 'operator@example.com']);

        $this->artisan('destination-token:issue', ['email' => 'operator@example.com', '--expires' => $expires])
            ->expectsOutputToContain('The --expires option must be a whole number of days from 1 to 365.')
            ->assertFailed();

        $this->assertSame(0, PersonalAccessToken::query()->count());
    }

    public static function invalidExpirations(): array
    {
        return [
            'zero' => ['0'],
            'negative' => ['-5'],
            'nonnumeric' => ['ninety'],
            'fractional' => ['1.5'],
            'above maximum' => ['366'],
            'empty' => [''],
        ];
    }
}
