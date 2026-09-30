<?php

namespace App\Database\Seeds;

use App\Models\OfficerModel;
use CodeIgniter\Database\Seeder;

class OfficerSeeder extends Seeder
{
    public function run(): void
    {
        $model = new OfficerModel();

        $officers = [
            [
                'name'     => 'Hermie Jay Balaba',
                'username' => 'hermie',
                'phone'    => '+639181111111',
                'role'     => 'admin',
            ],
            [
                'name'     => 'Marc Erfred Mabuti',
                'username' => 'marc',
                'phone'    => '+639182222222',
                'role'     => 'officer',
            ],
        ];

        foreach ($officers as $officer) {
            if ($model->where('username', $officer['username'])->countAllResults() > 0) {
                continue;
            }

            $model->insert([
                'name'          => $officer['name'],
                'username'      => $officer['username'],
                'phone'         => $officer['phone'],
                'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
                'role'          => $officer['role'],
                'active'        => 1,
            ]);
        }
    }
}
