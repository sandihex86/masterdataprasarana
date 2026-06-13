<?php

use App\Support\Infrastructure\InfrastructureBootstrapper;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('infrastructure:bootstrap', function (InfrastructureBootstrapper $bootstrapper) {
    $this->info('Bootstrapping multi-database infrastructure schema...');

    foreach (config('infrastructure.connections', []) as $connection) {
        if (! is_array($connection)) {
            continue;
        }

        $name = (string) ($connection['connection'] ?? '');
        $label = (string) ($connection['label'] ?? $name);

        if ($name === '') {
            continue;
        }

        if (! $bootstrapper->connectionConfigured($name)) {
            $this->line(sprintf('- %s [%s]: skipped, database belum dikonfigurasi.', $label, $name));

            continue;
        }

        $message = match ($name) {
            'reference' => tap('bootstrap reference selesai.', fn () => $bootstrapper->bootstrapReferenceConnection($name)),
            'track', 'operational_facility', 'certificate', 'warehouse' => tap('bootstrap domain selesai.', fn () => $bootstrapper->bootstrapOperationalDomainConnection($name)),
            'reporting' => tap('bootstrap reporting selesai.', fn () => $bootstrapper->bootstrapReportingConnection($name)),
            'bridge' => 'dikelola migration source bridge terpisah.',
            'core' => 'dikelola migration aplikasi inti.',
            default => 'tidak ada bootstrap khusus.',
        };

        $this->line(sprintf('- %s [%s]: %s', $label, $name, $message));
    }

    $this->newLine();
    $this->info('Infrastructure bootstrap selesai.');
})->purpose('Create idempotent bootstrap schema for multi-database bounded context connections');
