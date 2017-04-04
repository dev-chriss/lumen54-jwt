<?php

use App\Models\User;
use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = factory(User::class)->times(5)->make();

        $datas = array_map(function ($value) {
            return $value->getAttributes();
        }, $users->all());

        User::insert($datas);
    }
}
