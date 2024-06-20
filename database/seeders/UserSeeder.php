<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Models\User;
use Carbon\Carbon;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = File::get("database/data/user.json");
        $site = json_decode($json);
  
        foreach ($site as $value) {
            User::create([
                "name" => $value->name,
                "email" => $value->email,
                "password" => bcrypt($value->password),
                "created_at" => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at" => null
            ]);
        }
    }
}
