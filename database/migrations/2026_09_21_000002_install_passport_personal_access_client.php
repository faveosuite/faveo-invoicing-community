<?php

use Illuminate\Database\Migrations\Migration;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use phpseclib3\Crypt\RSA;

return new class extends Migration
{
    public function up(): void
    {
        $keys = [storage_path('oauth-private.key'), storage_path('oauth-public.key')];

        // Passport only registers its artisan commands in the console, and the
        // web installer runs migrations over HTTP, so do the work directly.
        if (count(array_filter($keys, 'file_exists')) < 2) {
            $key = RSA::createKey(4096);
            file_put_contents($keys[1], (string) $key->getPublicKey());
            file_put_contents($keys[0], (string) $key);
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
            app(ClientRepository::class)->createPersonalAccessGrantClient('Personal Access Client', $provider);
        }
    }

    public function down(): void
    {
        // Intentionally empty: the keys and the personal access client are
        // credentials, not schema. Removing them would revoke every access
        // token already issued against them.
    }
};
