<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Laravel\Passport\Client;
use Laravel\Passport\Passport;

return new class extends Migration
{
    public function up(): void
    {
        $keys = [storage_path('oauth-private.key'), storage_path('oauth-public.key')];

        if (count(array_filter($keys, 'file_exists')) < 2) {
            Artisan::call('passport:keys');
        }

        // Passport refuses key files readable beyond the owner.
        foreach (array_filter($keys, 'file_exists') as $key) {
            chmod($key, 0600);
        }

        $provider = config('auth.guards.api.provider');

        $exists = Passport::client()->newQuery()
            ->where('revoked', false)
            ->where(fn ($query) => $query->whereNull('provider')->orWhere('provider', $provider))
            ->get()
            ->contains(fn (Client $client): bool => $client->hasGrantType('personal_access'));

        if (! $exists) {
            Artisan::call('passport:client', [
                '--personal' => true,
                '--name' => 'Personal Access Client',
                '--provider' => $provider,
                '--no-interaction' => true,
            ]);
        }
    }

    public function down(): void
    {
        // Intentionally empty: the keys and the personal access client are
        // credentials, not schema. Removing them would revoke every access
        // token already issued against them.
    }
};
