<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:setup';

    /**
     * The console command description.
     */
    protected $description = 'Setup the application: create SQLite database, run migrations, and seed data.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Starting application setup...');
        $this->newLine();

        // Step 1: Check/create SQLite database
        $this->info('📦 Step 1: Checking database...');
        $dbPath = database_path('database.sqlite');

        if (!file_exists($dbPath)) {
            touch($dbPath);
            $this->info("   ✅ Created SQLite database at: {$dbPath}");
        } else {
            $this->info("   ✅ SQLite database already exists at: {$dbPath}");
        }
        $this->newLine();

        // Step 2: Check .env configuration
        $this->info('📝 Step 2: Verifying .env configuration...');
        $envPath = base_path('.env');

        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);

            if (!str_contains($envContent, 'DB_CONNECTION=sqlite')) {
                // Update DB_CONNECTION to sqlite
                $envContent = preg_replace('/DB_CONNECTION=\w+/', 'DB_CONNECTION=sqlite', $envContent);
                file_put_contents($envPath, $envContent);
                $this->info('   ✅ Updated DB_CONNECTION to sqlite in .env');
            } else {
                $this->info('   ✅ .env already configured for SQLite.');
            }
        } else {
            $this->warn('   ⚠️  .env file not found. Please copy .env.example to .env');
            return Command::FAILURE;
        }
        $this->newLine();

        // Step 3: Run migrations
        $this->info('🔧 Step 3: Running migrations...');
        Artisan::call('migrate', ['--force' => true], $this->output);
        $this->newLine();

        // Step 4: Seed database
        $this->info('🌱 Step 4: Seeding database...');
        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\StokAkturlSeeder',
            '--force' => true,
        ], $this->output);
        $this->newLine();

        // Step 5: Generate app key if not set
        $this->info('🔑 Step 5: Checking application key...');
        $envContent = file_get_contents($envPath);
        if (str_contains($envContent, 'APP_KEY=') && !str_contains($envContent, 'APP_KEY=base64:')) {
            Artisan::call('key:generate', [], $this->output);
        } else {
            $this->info('   ✅ Application key already set.');
        }
        $this->newLine();

        $this->info('═══════════════════════════════════════════════');
        $this->info('  ✅ Setup complete! Run: php artisan serve');
        $this->info('═══════════════════════════════════════════════');

        return Command::SUCCESS;
    }
}
