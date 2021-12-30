<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

use App\Models\User;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->delete();

        User::factory()
                ->count(Config::get('seeding.n_users'))
                ->create();

        // User with id=1 will always be john@x.com. It is
        // convenient to have at least one user with a fixed
        // email address as it saves us the hassle of having
        // to pick out a new email address each time we reseed
        // the database.
        User::first()->update(['email' => 'john@x.com']);
    }
}
