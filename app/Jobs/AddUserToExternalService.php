<?php

namespace App\Jobs;

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AddUserToExternalService implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    /**
     * Create a new job instance.
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            (new AuthController())->updateUserWithVerificationStatus($this->user);
        } catch (\Exception $e) {
            \Log::error("Failed to add user to {$this->user->email}: ".$e->getMessage());
        }
    }
}
