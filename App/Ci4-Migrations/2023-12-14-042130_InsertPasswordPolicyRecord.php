<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class InsertPasswordPolicyRecord extends Migration
{
    public function up()
    {
        // Inserting a record
        $data = [
            'id' => 1,
            'min_length' => 8,
            'num_cnt' => 1,
            'uppercase_cnt' => 1,
            'lowercase_cnt' => 1,
            'symbol_cnt' => 1,
            'admin_id' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Using the Query Builder to insert data
        $this->db->table('password_policy')->insert($data);
    }

    public function down()
    {
        //
    }
}
