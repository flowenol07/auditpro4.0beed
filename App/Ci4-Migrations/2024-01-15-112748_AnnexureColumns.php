<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AnnexureColumns extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE,
            ],

            'annexure_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 1024,
            ],

            'column_type_id' => [
                'type' => 'INT',
                'constraint' => 1,
            ],

            'column_options' => [
                'type' => 'TEXT'
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

        $this->forge->createTable('annexure_columns');
    }

    public function down()
    {
        $this->forge->dropTable('annexure_columns');
    }
}
