<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class RevokeDestinationToken extends Command
{
    protected $signature = 'destination-token:revoke {email : Email address of an existing user} {--name=destination-api : Name of the token}';

    protected $description = 'Revoke a named personal access token for a user';

    public function handle(): int
    {
        $user = User::query()->where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error('No user exists with that email address.');

            return self::FAILURE;
        }

        $name = $this->option('name');

        if ($user->tokens()->where('name', $name)->delete() === 0) {
            $this->error("No token named {$name} exists for this user.");

            return self::FAILURE;
        }

        $this->info("Token {$name} revoked for {$user->email}.");

        return self::SUCCESS;
    }
}
