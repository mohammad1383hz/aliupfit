<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create {email? : Email address for the administrator}';
    protected $description = 'Create or promote a site administrator account';

    public function handle(): int
    {
        $email = (string) ($this->argument('email') ?: $this->ask('Admin email'));
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('A valid email address is required.');
            return self::FAILURE;
        }

        $name = (string) $this->ask('Admin name');
        $password = (string) $this->secret('Choose a password (minimum 12 characters)');
        if (mb_strlen($password) < 12) {
            $this->error('The password must contain at least 12 characters.');
            return self::FAILURE;
        }

        $user = User::query()->firstOrNew(['email' => $email]);
        $user->name = $name;
        $user->password = Hash::make($password);
        $user->is_admin = true;
        $user->email_verified_at ??= now();
        $user->save();

        $this->info("Administrator account ready for {$email}.");
        return self::SUCCESS;
    }
}
