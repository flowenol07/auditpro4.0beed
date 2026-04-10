<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AuditRemarks extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE,
            ],

            'subject' => [
                'type' => 'VARCHAR',
                'constraint' => 1024,
            ],

            'message' => [ 'type' => 'TEXT' ],

            'noti_type' => [
                'type' => 'INT',
                'constraint' => 2
            ],

            'assesment_id' => [
                'type' => 'INT',
                'constraint' => 11
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

        $this->forge->createTable('audit_remarks');
    }

    public function down()
    {
        $this->forge->dropTable('audit_remarks');
    }
}
