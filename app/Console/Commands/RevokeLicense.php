<?php

namespace App\Console\Commands;

use App\Models\License;
use App\Services\LicenseService;
use Illuminate\Console\Command;

class RevokeLicense extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'licenses:revoke
                            {license_id : The ID of the license to revoke}
                            {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Revoke a license';

    /**
     * Execute the console command.
     */
    public function handle(LicenseService $licenseService): int
    {
        $licenseId = $this->argument('license_id');
        $license = License::with('user')->find($licenseId);

        if (!$license) {
            $this->error("License with ID {$licenseId} not found.");
            return self::FAILURE;
        }

        $this->info('License details:');
        $this->table(
            ['Field', 'Value'],
            [
                ['ID', $license->id],
                ['User', $license->user->email ?? 'N/A'],
                ['Plan', ucfirst($license->plan)],
                ['Status', $license->status],
                ['Key Prefix', $license->key_prefix],
            ]
        );

        if (!$this->option('force')) {
            if (!$this->confirm('Are you sure you want to revoke this license?')) {
                $this->info('Operation cancelled.');
                return self::SUCCESS;
            }
        }

        $licenseService->revoke($license);

        $this->info('License has been revoked.');
        $this->warn('All activations have been deactivated.');

        return self::SUCCESS;
    }
}
