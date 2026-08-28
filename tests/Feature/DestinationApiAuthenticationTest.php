<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class DestinationApiAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_request_without_a_token_is_rejected_with_json_401(): void
    {
        Destination::factory()->create();

        $this->get('/api/destinations')
            ->assertUnauthorized()
            ->assertHeader('content-type', 'application/json')
            ->assertExactJson(['message' => 'Unauthenticated.']);
    }

    public function test_a_bearer_token_with_the_read_ability_succeeds(): void
    {
        Destination::factory()->create(['name' => 'Kyoto']);

        $this->withToken($this->readToken())->getJson('/api/destinations')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Kyoto');
    }

    public function test_an_authorized_response_keeps_the_public_contract(): void
    {
        Destination::factory()->create([
            'name' => 'Kyoto',
            'country' => 'Japan',
            'region' => 'Asia',
            'cost_level' => 'Moderate',
            'activities' => ['Cultural Tours', 'Photography'],
            'average_daily_budget' => 180,
            'annual_visitors' => 5300000,
        ]);

        $this->withToken($this->readToken())->getJson('/api/destinations?perPage=5&sort=annualVisitors&direction=desc')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'name', 'country', 'region', 'costLevel', 'activities', 'averageDailyBudget', 'annualVisitors']],
                'links' => ['first', 'last', 'prev', 'next'],
                'meta' => ['current_page', 'from', 'last_page', 'per_page', 'to', 'total'],
            ])
            ->assertJsonPath('data.0.costLevel', 'Moderate')
            ->assertJsonPath('data.0.activities', ['Cultural Tours', 'Photography'])
            ->assertJsonPath('data.0.averageDailyBudget', 180)
            ->assertJsonPath('data.0.annualVisitors', 5300000)
            ->assertJsonPath('meta.per_page', 5)
            ->assertJsonMissingPath('data.0.cost_level');
    }

    public function test_a_token_without_the_read_ability_is_rejected_with_json_403(): void
    {
        Destination::factory()->create();
        $token = User::factory()->create()->createToken('destination-api', ['destinations:write'])->plainTextToken;

        $this->withToken($token)->get('/api/destinations')
            ->assertForbidden()
            ->assertHeader('content-type', 'application/json')
            ->assertJsonPath('message', 'Invalid ability provided.');
    }

    public function test_a_revoked_token_is_rejected_with_json_401(): void
    {
        Destination::factory()->create();
        $user = User::factory()->create();
        $token = $user->createToken('destination-api', ['destinations:read'])->plainTextToken;

        $this->withToken($token)->getJson('/api/destinations')->assertOk();

        $user->tokens()->where('name', 'destination-api')->delete();
        $this->app['auth']->forgetGuards();

        $this->withToken($token)->getJson('/api/destinations')
            ->assertUnauthorized()
            ->assertExactJson(['message' => 'Unauthenticated.']);
    }

    public function test_an_expired_bearer_token_is_rejected_with_json_401(): void
    {
        $this->travelTo(Carbon::create(2026, 8, 28, 12));
        Destination::factory()->create();
        $token = User::factory()->create()->createToken('destination-api', ['destinations:read'], now()->addDays(1))->plainTextToken;

        $this->withToken($token)->getJson('/api/destinations')->assertOk();

        $this->travel(2)->days();
        $this->app['auth']->forgetGuards();

        $this->withToken($token)->getJson('/api/destinations')
            ->assertUnauthorized()
            ->assertExactJson(['message' => 'Unauthenticated.']);
    }

    public function test_tokens_are_stored_as_hashes_with_only_the_read_ability(): void
    {
        $token = $this->readToken();
        [$id, $secret] = explode('|', $token, 2);
        $stored = PersonalAccessToken::query()->findOrFail($id);

        $this->assertNotSame($secret, $stored->token);
        $this->assertStringNotContainsString($secret, $stored->token);
        $this->assertSame(hash('sha256', $secret), $stored->token);
        $this->assertSame(['destinations:read'], $stored->abilities);
        $this->assertFalse($stored->can('*'));
    }

    public function test_validation_still_returns_422_after_authentication(): void
    {
        $this->withToken($this->readToken())->getJson('/api/destinations?perPage=0&sort=password')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['perPage', 'sort']);
    }

    public function test_the_seed_endpoint_stays_absent_with_and_without_a_token(): void
    {
        $this->postJson('/api/seed')->assertNotFound();
        $this->withToken($this->readToken())->postJson('/api/seed')->assertNotFound();
    }

    public function test_the_public_explorer_and_health_check_need_no_token(): void
    {
        Destination::factory()->create(['name' => 'Lisbon']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Project Expedition Destinations')
            ->assertSee('Lisbon');

        $this->get('/up')->assertOk();
    }

    private function readToken(): string
    {
        return User::factory()->create()->createToken('destination-api', ['destinations:read'])->plainTextToken;
    }
}
