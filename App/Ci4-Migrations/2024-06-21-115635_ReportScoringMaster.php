<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ReportScoringMaster extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE,
            ],

            'year' => [
                'type' => 'VARCHAR',
                'constraint' => 8,
            ],

            'assesment_id' => [
                'type' => 'INT',
                'constraint' => 11
            ],

            'audit_type_id' => [
                'type' => 'INT',
                'constraint' => 2
            ],

            'audit_unit_id' => [
                'type' => 'INT',
                'constraint' => 11
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

            'audit_review_date' => [
                'type' => 'DATE',
                'null' => true,
            ],

            'compliance_start_date' => [
                'type' => 'DATE',
                'null' => true,
            ],

            'compliance_end_date' => [
                'type' => 'DATE',
                'null' => true,
            ],

            'compliance_review_date' => [
                'type' => 'DATE',
                'null' => true,
            ],

            'risk_data' => [ 'type' => 'TEXT' ],

            'weighted_score' => [
                'type' => 'VARCHAR',
                'constraint' => 16,
            ],

            'advances_sampling' => [
                'type' => 'VARCHAR',
                'constraint' => 16,
            ],

            'deposits_sampling' => [
                'type' => 'VARCHAR',
                'constraint' => 16,
            ],

            'last_updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
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

        $this->forge->createTable('report_scoring_master');
    }

    public function down()
    {
        $this->forge->dropTable('report_scoring_master');
    }
}
