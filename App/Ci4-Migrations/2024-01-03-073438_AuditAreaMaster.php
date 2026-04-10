<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AuditAreaMaster extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE,
            ],

            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 1024,
            ],

            'appetite_percent' => [
                'type' => 'VARCHAR',
                'constraint' => 8,
            ],

            'occurance_percent' => [
                'type' => 'VARCHAR',
                'constraint' => 8,
            ],

            'magnitude' => [
                'type' => 'VARCHAR',
                'constraint' => 8,
            ],

            'frequency' => [
                'type' => 'VARCHAR',
                'constraint' => 8,
            ],

            'average_qualitative_count' => [
                'type' => 'VARCHAR',
                'constraint' => 8,
            ],

            'average_quantitative_count' => [
                'type' => 'VARCHAR',
                'constraint' => 8,
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

        $this->forge->createTable('audit_area_master');
    }

    public function down()
    {
        $this->forge->dropTable('audit_area_master');
    }
}
