<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AuditRemarkStatus extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE,
            ],

            'noti_id' => [
                'type' => 'INT',
                'constraint' => 11
            ],

            'emp_id' => [
                'type' => 'INT',
                'constraint' => 11
            ],

            'readed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);

        $this->forge->createTable('audit_remark_status');
    }

    public function down()
    {
        $this->forge->dropTable('audit_remark_status');
    }
}
