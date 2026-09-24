<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * SAFETY NET — runs before any test trait (RefreshDatabase runs
     * `migrate:fresh`, which DROPS EVERY TABLE of the connected database).
     *
     * Tests are only allowed to run against a throw-away database:
     *   - SQLite in memory, or
     *   - a database whose name contains "test" (e.g. hontomo_testing).
     * Anything else (your real MySQL database, database/database.sqlite, a
     * cached config that points at it, ...) aborts the run before a single
     * table is touched.
     */
    protected function setUpTraits(): array
    {
        $this->assertDatabaseIsSafeForTests();

        return parent::setUpTraits();
    }

    private function assertDatabaseIsSafeForTests(): void
    {
        $config = $this->app['config'];

        $connection = (string) $config->get('database.default');
        $database = (string) $config->get("database.connections.{$connection}.database");

        $isMemory = $connection === 'sqlite' && str_contains($database, ':memory:');
        $looksLikeTestDb = (bool) preg_match('/test/i', basename($database));

        if ($this->app->environment('testing') && ($isMemory || $looksLikeTestDb)) {
            return;
        }

        throw new RuntimeException(sprintf(
            "TEST DIHENTIKAN demi keamanan data. Koneksi '%s', database '%s', APP_ENV '%s' bukan database test. ".
            "Test memakai migrate:fresh yang menghapus semua tabel. ".
            "Gunakan SQLite in-memory (default phpunit.xml) atau database khusus yang namanya mengandung 'test'. ".
            "Jika config pernah di-cache, jalankan: php artisan config:clear",
            $connection,
            $database,
            $this->app->environment(),
        ));
    }
}
