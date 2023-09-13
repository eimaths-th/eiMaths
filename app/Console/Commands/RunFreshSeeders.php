<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class RunFreshSeeders extends Command
{
    protected $signature = 'db:seed-fresh';

    protected $description = 'Run specific seeders after migrate:fresh';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Running migrate:fresh...');
        Artisan::call('migrate:fresh');

        $seeders = [
            'Department',
            'Position',
            'PermissionTableSeeder',
            'CreateAdminUserSeeder',
            'termSeeder',
            'stermSeeder',
            'levelSeeder',
            'discontinuedSeeder',
            'bookuseSeeder',
            'Parameter',
        ];

        foreach ($seeders as $seeder) {
            $this->info("Seeding $seeder...");
            Artisan::call('db:seed', ['--class' => $seeder]);
        }

        $this->info('All seeders have been executed.');
    }
}
