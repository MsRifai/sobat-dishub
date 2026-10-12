<?php

namespace App\Providers;

require_once __DIR__ . '/../helpers.php';

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\FilesystemAdapter;
use League\Flysystem\Filesystem;
use Masbug\Flysystem\GoogleDriveAdapter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production' || str_contains(request()->header('x-forwarded-proto', ''), 'https')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        try {
            Storage::extend('google', function ($app, $config) {
                $options = [];
                if (!empty($config['teamDriveId'] ?? null)) {
                    $options['teamDriveId'] = $config['teamDriveId'];
                }
                if (!empty($config['folder'] ?? null)) {
                    $options['sharedFolderId'] = $config['folder'];
                }

                $client = new \Google\Client();
                $client->setClientId($config['clientId']);
                $client->setClientSecret($config['clientSecret']);
                $client->fetchAccessTokenWithRefreshToken($config['refreshToken']);

                $service = new \Google\Service\Drive($client);
                $adapter = new GoogleDriveAdapter($service, null, $options);

                return new FilesystemAdapter(
                    new Filesystem($adapter, $config),
                    $adapter,
                    $config
                );
            });
        } catch (\Throwable $e) {
            // Storage extend error fallback
        }
    }
}
