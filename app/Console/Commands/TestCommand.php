<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        logger(storage_path());
        // $users = User::all();
        // foreach ($users as $user) 
        // {
        //     $role = Role::where('name', 'user')->first();
        //     $user->assignRole($role);
        // }
    }
}
