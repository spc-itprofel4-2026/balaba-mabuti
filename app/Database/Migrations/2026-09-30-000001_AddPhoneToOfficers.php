<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Viber-style accounts: the mobile number is a first-class identifier
 * (register with it, sign in with it), so the local mirror needs the column
 * too for offline sign-in.
 */
class AddPhoneToOfficers extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('officers', [
            'phone' => ['type' => 'VARCHAR', 'constraint' => 32, 'null' => true, 'after' => 'username'],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('officers', 'phone');
    }
}
