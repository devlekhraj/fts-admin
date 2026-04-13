<?php

namespace App\Providers;

use App\Foundation\Infrastructure\Persistence\Eloquent\Models\SettingModel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

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
        // Dynamically override mail configuration from DB settings (cached).
        $settings = Cache::remember('mail_settings', 300, static function () {
            return optional(
                SettingModel::query()->where('module', 'core')->first()
            )->settings ?? [];
        });

        if (is_array($settings) && !empty($settings)) {
            $defaultMailer = config('mail.default', 'smtp');

            Config::set("mail.mailers.{$defaultMailer}", array_merge([
                'transport' => 'smtp',
                'host' => 'smtp.mailtrap.io',
                'port' => 587,
                'encryption' => 'tls',
                'username' => null,
                'password' => null,
                'timeout' => null,
                'auth_mode' => null,
            ], [
                'host' => $settings['mail_host'] ?? 'smtp.mailtrap.io',
                'port' => $settings['mail_port'] ?? 587,
                'encryption' => $settings['mail_encryption'] ?? 'tls',
                'username' => $settings['mail_username'] ?? null,
                'password' => $settings['mail_password'] ?? null,
            ]));

            Config::set('mail.from', [
                'address' => $settings['mail_from_address'] ?? 'no-reply@example.com',
                'name' => $settings['mail_from_name'] ?? 'Fatafat Sewa',
            ]);
        }
    }
}
