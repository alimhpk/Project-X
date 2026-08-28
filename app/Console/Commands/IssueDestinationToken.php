<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class IssueDestinationToken extends Command
{
    protected $signature = 'destination-token:issue {email : Email address of an existing user} {--name=destination-api : Name of the token} {--expires=90 : Lifetime in whole days, from 1 to 365}';

    protected $description = 'Issue a personal access token limited to the destinations:read ability';

    public function handle(): int
    {
        $days = (string) $this->option('expires');

        if (! preg_match('/^\d+$/', $days) || (int) $days < 1 || (int) $days > 365) {
            $this->error('The --expires option must be a whole number of days from 1 to 365.');

            return self::FAILURE;
        }

        $user = User::query()->where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error('No user exists with that email address.');

            return self::FAILURE;
        }

        $name = $this->option('name');

        if ($user->tokens()->where('name', $name)->exists()) {
            $this->error("A token named {$name} already exists for this user. Revoke it before issuing a new one.");

            return self::FAILURE;
        }

        $expiresAt = now()->addDays((int) $days);
        $token = $user->createToken($name, ['destinations:read'], $expiresAt);

        $this->info("Token {$name} issued for {$user->email} with the destinations:read ability.");
        $this->info("It expires on {$expiresAt->format('Y-m-d H:i')} {$expiresAt->tzName} ({$days} days from now).");
        $this->warn('Copy this token now and store it securely. It will not be shown again.');
        $this->line($token->plainTextToken);

        return self::SUCCESS;
    }
}
