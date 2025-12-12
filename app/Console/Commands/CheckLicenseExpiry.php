<?php

namespace App\Console\Commands;

use App\Models\License;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckLicenseExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'licenses:check-expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for expired licenses and update their status';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking for expired licenses...');

        // Find licenses that have expired but are still marked as active
        $expiredLicenses = License::where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        $count = $expiredLicenses->count();

        if ($count === 0) {
            $this->info('No expired licenses found.');
            return self::SUCCESS;
        }

        $this->info("Found {$count} expired license(s).");

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        foreach ($expiredLicenses as $license) {
            $license->update(['status' => 'expired']);

            Log::info('License marked as expired', [
                'license_id' => $license->id,
                'user_id' => $license->user_id,
                'expired_at' => $license->expires_at,
            ]);

            // TODO: Send expiry notification to user
            // $license->user->notify(new LicenseExpiredNotification($license));

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info("Updated {$count} license(s) to expired status.");

        return self::SUCCESS;
    }
}
