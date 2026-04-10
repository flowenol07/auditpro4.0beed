<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MultiLevelControlMaster extends Migration
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

            'section_type_id' => [
                'type' => 'INT',
                'constraint' => 2,
            ],

            'user_type_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'audit_unit_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'start_month_year' => [
                'type' => 'VARCHAR',
                'constraint' => 16,
            ], 

            'end_month_year' => [
                'type' => 'VARCHAR',
                'constraint' => 16,
            ], 

            'menu_ids' => [ 'type' => 'TEXT' ],
            'cat_ids' => [ 'type' => 'TEXT' ],
            'header_ids' => [ 'type' => 'TEXT' ],
            'question_ids' => [ 'type' => 'TEXT' ],
            'advances_scheme_ids' => [ 'type' => 'TEXT' ],
            'deposits_scheme_ids' => [ 'type' => 'TEXT' ],

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

        $this->forge->createTable('multi_level_control_master');
    }

    public function down()
    {
        $this->forge->dropTable('multi_level_control_master');
    }
}
