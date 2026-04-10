<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EvidenceMaster extends Migration
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

            'evi_type' => [
                'type' => 'INT',
                'constraint' => 1,
            ],

            'file_name' => [
                'type' => 'VARCHAR',
                'constraint' => 56,
            ],

            'file_type' => [
                'type' => 'INT',
                'constraint' => 2,
            ],

            'description' => [ 'type' => 'TEXT' ],

            'emp_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'status_id' => [
                'type' => 'INT',
                'constraint' => 2,
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

        $this->forge->createTable('evidence_master');
    }

    public function down()
    {
        $this->forge->dropTable('evidence_master');
    }
}
