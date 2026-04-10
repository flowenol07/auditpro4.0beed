<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AuditAssesmentTimeline extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE,
            ],

            'assesment_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'type_id' => [
                'type' => 'INT',
                'constraint' => 2,
            ],

            'status_id' => [
                'type' => 'INT',
                'constraint' => 2,
            ],

            'rejected_cnt' => [
                'type' => 'INT',
                'constraint' => 8,
            ],

            'reviewer_emp_id' => [
                'type' => 'INT',
                'constraint' => 11,
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

        $this->forge->createTable('audit_assesment_timeline');
    }

    public function down()
    {
        $this->forge->dropTable('audit_assesment_timeline');
    }
}
