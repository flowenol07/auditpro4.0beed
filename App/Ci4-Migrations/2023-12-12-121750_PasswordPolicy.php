<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PasswordPolicy extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE,
            ],

            'min_length' => [
                'type' => 'INT',
                'constraint' => 2,
            ],

            'num_cnt' => [
                'type' => 'INT',
                'constraint' => 2,
            ],

            'uppercase_cnt' => [
                'type' => 'INT',
                'constraint' => 2,
            ],

            'lowercase_cnt' => [
                'type' => 'INT',
                'constraint' => 2,
            ],

            'symbol_cnt' => [
                'type' => 'INT',
                'constraint' => 2,
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
        $this->forge->createTable('password_policy');
    }

    public function down()
    {
        $this->forge->dropTable('password_policy');
    }
}
