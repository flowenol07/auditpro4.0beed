<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class QuestionMaster extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE,
            ],

            'header_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'set_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'question' => [
                'type' => 'TEXT',
            ],

            'risk_category_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'option_id' => [
                'type' => 'INT',
                'constraint' => 1,
            ],

            'parameters' => [
                'type' => 'TEXT',
            ],

            'question_type_id' => [
                'type' => 'INT',
                'constraint' => 1,
            ],

            'annexure_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'subset_multi_id' => [
                'type' => 'VARCHAR',
                'constraint' => 1024,
            ],

            'area_of_audit_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'applicable_id' => [
                'type' => 'INT',
                'constraint' => 1,
            ],

            'control_risk_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'key_aspect_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'residual_risk_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'show_instances' => [
                'type' => 'INT',
                'constraint' => 1,
            ],

            'is_active' => [
                'type' => 'INT',
                'constraint' => 1,
                'default' => 1
            ],

            'audit_ev_upload' => [
                'type' => 'INT',
                'constraint' => 1,
            ],

            'compliance_ev_upload' => [
                'type' => 'INT',
                'constraint' => 1,
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

        $this->forge->createTable('question_master');
    }

    public function down()
    {
        $this->forge->dropTable('question_master');
    }
}
