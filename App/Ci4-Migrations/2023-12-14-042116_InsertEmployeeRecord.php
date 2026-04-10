<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class InsertEmployeeRecord extends Migration
{
    public function up()
    {
        // Inserting a record
        $data = [
            'id' => 1,
            'user_type_id' => 1,
            'emp_code' => 1,
            'gender' => 'mr',
            'name' => 'Kredpool Solutions',
            'profile_pic' => '',
            'email' => 'admin@kredpool.com',
            'mobile' => '7058545449',
            'designation' => 'Admin',
            'password' => 'Emp@2023',
            'password_policy' => 1,
            'admin_id' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Using the Query Builder to insert data
        $this->db->table('employee_master')->insert($data);
    }

    public function down()
    {
        //
    }
}
