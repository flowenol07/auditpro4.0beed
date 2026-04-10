<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AuditUnitMaster extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE,
            ],

            'section_type_id' => [
                'type' => 'INT',
                'constraint' => 2
            ],

            'audit_unit_code' => [
                'type' => 'VARCHAR',
                'constraint' => 16,
            ],

            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 1024,
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

            'frequency' => [
                'type' => 'INT',
                'constraint' => 2,
            ],

            'last_audit_date' => [
                'type' => 'DATE',
                'null' => true,
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

        $this->forge->createTable('audit_unit_master');
    }

    public function down()
    {
        $this->forge->dropTable('audit_unit_master');
    }
}
