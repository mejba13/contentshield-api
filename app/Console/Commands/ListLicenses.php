<?php

namespace App\Console\Commands;

use App\Models\License;
use Illuminate\Console\Command;

class ListLicenses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'licenses:list
                            {--status= : Filter by status (active, expired, cancelled, etc.)}
                            {--plan= : Filter by plan (starter, pro, agency)}
                            {--limit=20 : Number of licenses to show}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all licenses';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $query = License::with('user:id,email')
            ->orderBy('created_at', 'desc');

        if ($status = $this->option('status')) {
            $query->where('status', $status);
        }

        if ($plan = $this->option('plan')) {
            $query->where('plan', $plan);
        }

        $licenses = $query->limit($this->option('limit'))->get();

        if ($licenses->isEmpty()) {
            $this->info('No licenses found.');
            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'User', 'Plan', 'Status', 'Activations', 'Expires', 'Created'],
            $licenses->map(fn ($license) => [
                $license->id,
                $license->user->email ?? 'N/A',
                ucfirst($license->plan),
                $license->status,
                "{$license->activations_count}/{$license->activations_limit}",
                $license->expires_at?->format('Y-m-d') ?? 'Never',
                $license->created_at->format('Y-m-d'),
            ])
        );

        $this->newLine();
        $this->info("Total: {$licenses->count()} license(s)");

        return self::SUCCESS;
    }
}
