<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AnswersDataTimeline extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE,
            ],

            'answer_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'annex_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'assesment_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'last_updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            'answer_type' => [
                'type' => 'INT',
                'constraint' => 1,
                'default' => 1
            ],

            'answer_given' => [
                'type' => 'TEXT'
            ],

            'audit_comment' => [
                'type' => 'VARCHAR',
                'constraint' => 2048,
                'null' => true,
            ],

            'audit_emp_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'audit_status_id' => [
                'type' => 'INT',
                'constraint' => 2,
            ],

            'audit_reviewer_emp_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'audit_reviewer_comment' => [
                'type' => 'VARCHAR',
                'constraint' => 2048,
                'null' => true,
            ],

            'audit_commpliance' => [
                'type' => 'VARCHAR',
                'constraint' => 2048,
                'null' => true,
            ],

            'audit_evidance_upload' => [
                'type' => 'VARCHAR',
                'constraint' => 256,
                'null' => true,
            ],

            'audit_compulsary_ev_upload' => [
                'type' => 'INT',
                'constraint' => 1,
            ],

            'compliance_evidance_upload' => [
                'type' => 'VARCHAR',
                'constraint' => 256,
                'null' => true,
            ],

            'compliance_compulsary_ev_upload' => [
                'type' => 'INT',
                'constraint' => 1,
            ],

            'compliance_emp_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'compliance_status_id' => [
                'type' => 'INT',
                'constraint' => 2,
            ],

            'compliance_reviewer_emp_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'compliance_reviewer_comment' => [
                'type' => 'VARCHAR',
                'constraint' => 2048,
                'null' => true,
            ],

            'business_risk' => [
                'type' => 'INT',
                'constraint' => 2,
            ],

            'control_risk' => [
                'type' => 'INT',
                'constraint' => 2,
            ],

            'risk_cat_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'instances_count' => [
                'type' => 'INT',
                'constraint' => 6,
            ],

            'batch_key' => [
                'type' => 'VARCHAR',
                'constraint' => 56
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

        $this->forge->createTable('answers_data_timeline');
    }

    public function down()
    {
        $this->forge->dropTable('answers_data_timeline');
    }
}
