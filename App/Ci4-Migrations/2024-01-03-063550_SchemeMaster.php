<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SchemeMaster extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE,
            ],

            'scheme_type_id' => [
                'type' => 'INT',
                'constraint' => 2,
                'default' => 1
            ],

            'category_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'scheme_code' => [
                'type' => 'VARCHAR',
                'constraint' => 1024,
            ],

            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 1024,
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

        $this->forge->createTable('scheme_master');
    }

    public function down()
    {
        $this->forge->dropTable('scheme_master');
    }
}
