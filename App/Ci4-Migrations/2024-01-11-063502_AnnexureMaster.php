<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AnnexureMaster extends Migration
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

            'risk_defination_id' => [
                'type' => 'INT',
                'constraint' => 1,
            ],

            'risk_category_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'business_risk' => [
                'type' => 'INT',
                'constraint' => 2,
            ],

            'control_risk' => [
                'type' => 'INT',
                'constraint' => 2,
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

        $this->forge->createTable('annexure_master');
    }

    public function down()
    {
        $this->forge->dropTable('annexure_master');
    }
}
