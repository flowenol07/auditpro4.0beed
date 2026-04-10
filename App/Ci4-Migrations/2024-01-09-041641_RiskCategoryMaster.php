<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RiskCategoryMaster extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE,
            ],

            'risk_category' => [
                'type' => 'VARCHAR',
                'constraint' => 1024,
            ],

            'risk_weight' => [
                'type' => 'VARCHAR',
                'constraint' => 8,
            ],

            'risk_appetite_percent_from' => [
                'type' => 'VARCHAR',
                'constraint' => 8,
            ],

            'risk_appetite_percent_to' => [
                'type' => 'VARCHAR',
                'constraint' => 8,
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

        $this->forge->createTable('risk_category_master');
    }

    public function down()
    {
        $this->forge->dropTable('risk_category_master');
    }
}
