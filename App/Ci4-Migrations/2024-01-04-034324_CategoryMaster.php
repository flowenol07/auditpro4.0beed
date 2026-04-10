<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CategoryMaster extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE,
            ],

            'menu_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 1024,
            ],

            'linked_table_id' => [
                'type' => 'INT',
                'constraint' => 2
            ],

            'question_set_ids' => [
                'type' => 'VARCHAR',
                'constraint' => 1024,
            ],

            'is_cc_acc_category' => [
                'type' => 'INT',
                'constraint' => 1,
                'default' => 0
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

        $this->forge->createTable('category_master');
    }

    public function down()
    {
        $this->forge->dropTable('category_master');
    }
}
