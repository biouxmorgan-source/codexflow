<?php

namespace Tests\Feature;

use App\Support\ProductionCheck;
use App\Support\SystemHealth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

/**
 * Préparation de la mise en ligne : vérification de l'installation, sauvegarde de nuit,
 * avertissement de la console d'administration.
 */
class ProductionPrepTest extends TestCase
{
    use RefreshDatabase;

    private string $backups;

    protected function setUp(): void
    {
        parent::setUp();

        $this->backups = storage_path('framework/testing/backups');
        File::deleteDirectory($this->backups);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->backups);

        parent::tearDown();
    }

    private function productionConfig(): void
    {
        config([
            'app.debug' => false,
            'app.url' => 'https://sagawyn.com',
            'session.secure' => true,
            'mail.default' => 'smtp',
            'mail.from.address' => 'bonjour@sagawyn.com',
            'broadcasting.default' => 'reverb',
            'broadcasting.connections.reverb.key' => 'cle-du-serveur',
            'broadcasting.connections.reverb.secret' => 'secret-du-serveur',
            'broadcasting.connections.reverb.options.scheme' => 'https',
            'webpush.vapid.public_key' => null,
            'webpush.vapid.private_key' => null,
        ]);
    }

    private function item(string $label): array
    {
        return collect(ProductionCheck::run())->firstWhere('label', $label);
    }

    public function test_check_flags_debug_http_and_the_sample_reverb_keys(): void
    {
        config([
            'app.debug' => true,
            'app.url' => 'http://sagawyn.com',
            'mail.default' => 'log',
            'broadcasting.connections.reverb.key' => 'codexflow-local',
        ]);

        $failed = collect(ProductionCheck::errors())->pluck('label');

        $this->assertContains('Mode débogage coupé', $failed);
        $this->assertContains('Adresse en HTTPS', $failed);
        $this->assertContains('Envoi d’e-mails', $failed);
        $this->assertContains('Temps réel (Reverb)', $failed);
        $this->assertTrue($this->item('Base PostgreSQL')['ok']);
        $this->assertTrue($this->item('Base à jour')['ok']);
    }

    public function test_a_configured_server_has_no_blocking_point_and_the_command_succeeds(): void
    {
        $this->productionConfig();
        File::ensureDirectoryExists(public_path('build'));
        $manifest = public_path('build/manifest.json');
        $created = ! is_file($manifest);
        if ($created) {
            File::put($manifest, '{}');
        }

        try {
            $this->assertSame([], ProductionCheck::errors());
            $this->assertFalse($this->item('Sauvegarde de nuit')['ok']);

            $this->artisan('sagawyn:check')
                ->expectsOutputToContain('Mode débogage coupé')
                ->expectsOutputToContain('Aucun point bloquant.')
                ->assertExitCode(0);
        } finally {
            if ($created) {
                File::delete($manifest);
            }
        }
    }

    public function test_command_fails_and_explains_when_a_blocking_point_remains(): void
    {
        $this->productionConfig();
        config(['app.debug' => true]);

        $this->artisan('sagawyn:check')
            ->expectsOutputToContain('APP_DEBUG=false')
            ->assertExitCode(1);
    }

    public function test_admin_console_warns_in_production_only(): void
    {
        config(['app.debug' => true]);

        $this->assertEmpty(array_filter(SystemHealth::warnings(), fn ($warning) => str_contains($warning, 'sagawyn:check')));

        $this->app['env'] = 'production';

        $this->assertNotEmpty(array_filter(SystemHealth::warnings(), fn ($warning) => str_contains($warning, 'sagawyn:check')));
    }

    public function test_backup_dumps_the_database_archives_files_and_prunes_old_copies(): void
    {
        Process::fake();
        File::ensureDirectoryExists($this->backups.'/2020-01-01_033000');
        File::ensureDirectoryExists($this->backups.'/a-garder');

        $this->artisan('sagawyn:backup', ['--path' => $this->backups, '--keep' => 7])
            ->expectsOutputToContain('Sauvegarde écrite dans')
            ->assertExitCode(0);

        Process::assertRan(fn (PendingProcess $process) => $process->command[0] === 'pg_dump'
            && in_array('--format=custom', $process->command, true)
            && $process->environment['PGPASSWORD'] === (string) config('database.connections.pgsql.password'));
        Process::assertRan(fn (PendingProcess $process) => $process->command[0] === 'tar'
            && in_array(storage_path('app/private'), $process->command, true));

        $this->assertDirectoryDoesNotExist($this->backups.'/2020-01-01_033000');
        $this->assertDirectoryExists($this->backups.'/a-garder');
        $this->assertCount(2, File::directories($this->backups));
    }

    public function test_backup_reports_a_failed_dump(): void
    {
        Process::fake(fn (PendingProcess $process) => $process->command[0] === 'pg_dump'
            ? Process::result(errorOutput: 'connexion refusée', exitCode: 1)
            : Process::result());

        $this->artisan('sagawyn:backup', ['--path' => $this->backups])
            ->expectsOutputToContain('pg_dump a échoué : connexion refusée')
            ->assertExitCode(1);

        Process::assertNotRan(fn (PendingProcess $process) => $process->command[0] === 'tar');
    }

    public function test_backup_is_scheduled_only_when_enabled(): void
    {
        $this->artisan('schedule:list')->doesntExpectOutputToContain('sagawyn:backup');
    }
}
