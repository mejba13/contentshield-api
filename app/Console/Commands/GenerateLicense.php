<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\LicenseService;
use Illuminate\Console\Command;

class GenerateLicense extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'licenses:generate
                            {email : The email of the user}
                            {--plan=starter : The plan (starter, pro, agency)}
                            {--create-user : Create user if not exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new license key for a user';

    /**
     * Execute the console command.
     */
    public function handle(LicenseService $licenseService): int
    {
        $email = $this->argument('email');
        $plan = $this->option('plan');
        $createUser = $this->option('create-user');

        // Validate plan
        if (!in_array($plan, ['starter', 'pro', 'agency'])) {
            $this->error("Invalid plan: {$plan}. Must be one of: starter, pro, agency");
            return self::FAILURE;
        }

        // Find or create user
        $user = User::where('email', $email)->first();

        if (!$user) {
            if (!$createUser) {
                $this->error("User with email '{$email}' not found. Use --create-user to create.");
                return self::FAILURE;
            }

            $user = User::create([
                'name' => explode('@', $email)[0],
                'email' => $email,
                'password' => bcrypt(str()->random(32)),
            ]);

            $this->info("Created new user: {$email}");
        }

        // Generate license
        $license = $licenseService->generate($user, $plan);
        $plainKey = $licenseService->getPlainKey();

        $this->newLine();
        $this->info('License generated successfully!');
        $this->newLine();

        $this->table(
            ['Field', 'Value'],
            [
                ['User', $user->email],
                ['Plan', ucfirst($plan)],
                ['License Key', $plainKey],
                ['Key Prefix', $license->key_prefix],
                ['Activations Limit', $license->activations_limit],
                ['Expires At', $license->expires_at?->format('Y-m-d H:i:s')],
            ]
        );

        $this->newLine();
        $this->warn('IMPORTANT: Save this license key! It cannot be retrieved later.');

        return self::SUCCESS;
    }
}
