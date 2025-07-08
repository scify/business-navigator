<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminResetAdminPassword extends Command
{
    protected $signature = 'admin:reset-admin-password {--password=} {--confirm}';

    protected $description = 'Emergency password reset for admin user (ID: 1) - logs all activity';

    public function handle(): int
    {
        // Force confirmation
        if (! $this->option('confirm')) {
            $this->error('This is a security-sensitive operation.');
            $this->info('Use --confirm flag to proceed');
            $this->info('This action will be logged for security audit');
            $this->info('This will reset the password for admin user (ID: 1)');

            return 1;
        }

        // Explicitly find admin user by ID 1 (consistent with UserSeeder)
        $user = User::find(1);
        if (! $user) {
            $this->error('Admin user not found. Run database seeders first.');

            return 1;
        }

        $this->info("Resetting password for: $user->name ($user->email)");

        $password = $this->option('password') ?: $this->secret('New password');

        // Ensures password is a string and not empty!
        if (empty($password) || ! is_string($password)) {
            $this->error('Password cannot be empty or invalid.');

            return 1;
        }

        Log::critical('ADMIN PASSWORD RESET', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_name' => $user->name,
            'timestamp' => now(),
            'server_user' => get_current_user(),
            'ip' => $_SERVER['SERVER_ADDR'] ?? 'unknown',
        ]);

        $user->update(['password' => Hash::make($password)]);

        $this->warn('🚨 SECURITY: Admin password reset completed and logged');
        $this->info("Password updated for user ID: $user->id");

        return 0;
    }
}
