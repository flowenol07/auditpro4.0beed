<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TargetDetails extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE,
            ],

            'year_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'audit_unit_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'deposit_target' => [
                'type' => 'VARCHAR',
                'constraint' => 16,
            ],
            
            'advances_target' => [
                'type' => 'VARCHAR',
                'constraint' => 16,
            ],

            'npa_target' => [
                'type' => 'VARCHAR',
                'constraint' => 16,
            ],

            'admin_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);

        $this->forge->createTable('target_details');
    }

    public function down()
    {
        $this->forge->dropTable('target_details');
    }
}
