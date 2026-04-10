<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AuditAssesmentMaster extends Migration
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
                'default' => 1
            ],

            'year_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'audit_unit_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'frequency' => [
                'type' => 'INT',
                'constraint' => 2,
            ],

            'audit_head_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'branch_head_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'branch_subhead_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'multi_compliance_ids' => [
                'type' => 'VARCHAR',
                'constraint' => 1024,
            ],

            'assesment_period_from' => [
                'type' => 'DATE',
                'null' => true,
            ],

            'assesment_period_to' => [
                'type' => 'DATE',
                'null' => true,
            ],

            'audit_status_id' => [
                'type' => 'INT',
                'constraint' => 2,
            ],

            'audit_start_date' => [
                'type' => 'DATE',
                'null' => true,
            ],

            'audit_end_date' => [
                'type' => 'DATE',
                'null' => true,
            ],

            'audit_due_date' => [
                'type' => 'DATE',
                'null' => true,
            ],

            'audit_emp_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'audit_review_emp_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'audit_review_date' => [
                'type' => 'DATE',
                'null' => true,
            ],

            'audit_review_reject_limit' => [
                'type' => 'INT',
                'constraint' => 2,
            ],

            'compliance_start_date' => [
                'type' => 'DATE',
                'null' => true,
            ],

            'compliance_end_date' => [
                'type' => 'DATE',
                'null' => true,
            ],

            'compliance_due_date' => [
                'type' => 'DATE',
                'null' => true,
            ],

            'compliance_emp_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'compliance_review_emp_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'compliance_review_date' => [
                'type' => 'DATE',
                'null' => true,
            ],

            'compliance_review_reject_limit' => [
                'type' => 'INT',
                'constraint' => 2,
            ],

            'menu_ids' => [ 'type' => 'TEXT' ],
            'cat_ids' => [ 'type' => 'TEXT' ],
            'header_ids' => [ 'type' => 'TEXT' ],
            'question_ids' => [ 'type' => 'TEXT' ],
            'advances_scheme_ids' => [ 'type' => 'TEXT' ],
            'deposits_scheme_ids' => [ 'type' => 'TEXT' ],    
            
            'batch_key' => [
                'type' => 'VARCHAR',
                'constraint' => 56
            ],

            'is_limit_blocked' => [
                'type' => 'INT',
                'constraint' => 1,
                'default' => 0
            ],

            'compliance_onhold_count' => [
                'type' => 'INT',
                'constraint' => 8,
                'default' => 0
            ],

            'compliance_carry_forward_count' => [
                'type' => 'INT',
                'constraint' => 8,
                'default' => 0
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

        $this->forge->createTable('audit_assesment_master');
    }

    public function down()
    {
        $this->forge->dropTable('audit_assesment_master');
    }
}
