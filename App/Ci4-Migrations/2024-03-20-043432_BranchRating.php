<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BranchRating extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE,
            ],

            'audit_type_id' => [
                'type' => 'INT',
                'constraint' => 2,
            ],

            'year_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'audit_unit_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'risk_type_id' => [
                'type' => 'INT',
                'constraint' => 2,
            ],

            'range_from' => [
                'type' => 'VARCHAR',
                'constraint' => 8,
            ], 

            'range_to' => [
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

        $this->forge->createTable('risk_branch_rating');
    }

    public function down()
    {
        $this->forge->dropTable('risk_branch_rating');
    }
}
