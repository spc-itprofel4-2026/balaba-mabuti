<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Local operational store: officers (accounts), Viber members (audience)
 * and activity logs. Announcement records live in Supabase (see
 * App\Libraries\SupabaseService).
 */
class CreateCoreTables extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'          => ['type' => 'VARCHAR', 'constraint' => 120],
            'username'      => ['type' => 'VARCHAR', 'constraint' => 60, 'unique' => true],
            'password_hash' => ['type' => 'VARCHAR', 'constraint' => 255],
            'role'          => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'officer'],
            'active'        => ['type' => 'BOOLEAN', 'default' => 1],
            'last_login'    => ['type' => 'DATETIME', 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('officers', true);

        $this->forge->addField([
            'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'viber_id'      => ['type' => 'VARCHAR', 'constraint' => 64, 'unique' => true],
            'name'          => ['type' => 'VARCHAR', 'constraint' => 120, 'default' => 'Unknown'],
            'phone'         => ['type' => 'VARCHAR', 'constraint' => 32, 'null' => true],
            'avatar'        => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'status'        => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'subscribed'],
            'subscribed_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('status');
        $this->forge->createTable('viber_members', true);

        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'officer_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'action'     => ['type' => 'VARCHAR', 'constraint' => 60],
            'detail'     => ['type' => 'TEXT', 'null' => true],
            'channel'    => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'system'],
            'status'     => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'ok'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('created_at');
        $this->forge->createTable('activity_logs', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('activity_logs', true);
        $this->forge->dropTable('viber_members', true);
        $this->forge->dropTable('officers', true);
    }
}
