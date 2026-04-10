<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RiskControlKeyAspect extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE,
            ],

            'risk_control_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 1024,
            ],

            'is_active' => [
                'type' => 'INT',
                'constraint' => 1,
                'default' => 1
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

        $this->forge->createTable('risk_control_key_aspect');
    }

    public function down()
    {
        $this->forge->dropTable('risk_control_key_aspect');
    }
}
