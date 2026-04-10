<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ExeSummary extends Migration
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

            'gl_type_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'march_position' => [
                'type' => 'VARCHAR',
                'constraint' => 16,
            ],
            
            'm_4' => [
                'type' => 'VARCHAR',
                'constraint' => 16,
                'default' => 0
            ],

            'm_5' => [
                'type' => 'VARCHAR',
                'constraint' => 16,
                'default' => 0
            ],

            'm_6' => [
                'type' => 'VARCHAR',
                'constraint' => 16,
                'default' => 0
            ],

            'm_7' => [
                'type' => 'VARCHAR',
                'constraint' => 16,
                'default' => 0
            ],

            'm_8' => [
                'type' => 'VARCHAR',
                'constraint' => 16,
                'default' => 0
            ],

            'm_9' => [
                'type' => 'VARCHAR',
                'constraint' => 16,
                'default' => 0
            ],

            'm_10' => [
                'type' => 'VARCHAR',
                'constraint' => 16,
                'default' => 0
            ],

            'm_11' => [
                'type' => 'VARCHAR',
                'constraint' => 16,
                'default' => 0
            ],

            'm_12' => [
                'type' => 'VARCHAR',
                'constraint' => 16,
                'default' => 0
            ],

            'm_1' => [
                'type' => 'VARCHAR',
                'constraint' => 16,
                'default' => 0
            ],

            'm_2' => [
                'type' => 'VARCHAR',
                'constraint' => 16,
                'default' => 0
            ],

            'm_3' => [
                'type' => 'VARCHAR',
                'constraint' => 16,
                'default' => 0
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

        $this->forge->createTable('exe_summary');
    }

    public function down()
    {
        $this->forge->dropTable('exe_summary');
    }
}
