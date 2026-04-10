<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EmployeeMaster extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE,
            ],

            'user_type_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'emp_code' => [
                'type' => 'VARCHAR',
                'constraint' => 16,
            ],

            'gender' => [
                'type' => 'VARCHAR',
                'constraint' => 8,
            ],

            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 1024,
            ],

            'profile_pic' => [
                'type' => 'VARCHAR',
                'constraint' => 128,
            ],

            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 128,
            ],

            'mobile' => [
                'type' => 'VARCHAR',
                'constraint' => 16,
            ],

            'designation' => [
                'type' => 'VARCHAR',
                'constraint' => 1024,
            ],

            'password' => [
                'type' => 'VARCHAR',
                'constraint' => 128,
            ],

            'password_policy' => [
                'type' => 'INT',
                'constraint' => 1,
                'default' => 1
            ],

            'audit_unit_authority' => [
                'type' => 'VARCHAR',
                'constraint' => 2160,
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
        $this->forge->createTable('employee_master');
    }

    public function down()
    {
        $this->forge->dropTable('employee_master');
    }
}
