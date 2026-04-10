<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ExecutiveSummaryBranchPosition extends Migration
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

            'assesment_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'type_id' => [
                'type' => 'INT',
                'constraint' => 6,
            ],

            'amount' => [
                'type' => 'VARCHAR',
                'constraint' => 16,
            ],

            'business_risk' => [
                'type' => 'INT',
                'constraint' => 2,
            ],

            'control_risk' => [
                'type' => 'INT',
                'constraint' => 2,
            ],

            'risk_type' => [
                'type' => 'INT',
                'constraint' => 2,
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

        $this->forge->createTable('executive_summary_branch_position');
    }

    public function down()
    {
        $this->forge->dropTable('executive_summary_branch_position');
    }
}
