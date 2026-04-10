<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DumpAdvances extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE,
            ],

            'branch_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'scheme_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'account_no' => [
                'type' => 'VARCHAR',
                'constraint' => 256,
            ],

            'account_holder_name' => [
                'type' => 'VARCHAR',
                'constraint' => 1024,
            ],

            'ucic' => [
                'type' => 'VARCHAR',
                'constraint' => 56,
            ],

            'customer_type' => [
                'type' => 'VARCHAR',
                'constraint' => 56,
            ],

            'account_opening_date' => [
                'type' => 'DATE',
                'null' => true,
            ],

            'renewal_date' => [
                'type' => 'DATE',
                'null' => true,
            ],

            'sanction_amount' => [
                'type' => 'VARCHAR',
                'constraint' => 16,
            ],

            'intrest_rate' => [
                'type' => 'VARCHAR',
                'constraint' => 16,
            ],

            'due_date' => [
                'type' => 'DATE',
                'null' => true,
            ],

            'outstanding_balance' => [
                'type' => 'VARCHAR',
                'constraint' => 16,
            ],

            'balance_date' => [
                'type' => 'DATE',
                'null' => true,
            ],

            'npa_status' => [
                'type' => 'VARCHAR',
                'constraint' => 56,
            ],

            'account_status' => [
                'type' => 'VARCHAR',
                'constraint' => 56,
            ],

            'upload_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            'upload_period_from' => [
                'type' => 'DATE',
                'null' => true,
            ],

            'upload_period_to' => [
                'type' => 'DATE',
                'null' => true,
            ],

            'upload_key' => [
                'type' => 'VARCHAR',
                'constraint' => 16,
            ],

            'sampling_filter' => [
                'type' => 'INT',
                'constraint' => 1,
                'default' => 0
            ],

            'assesment_period_id' => [
                'type' => 'INT',
                'constraint' => 11,
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

        $this->forge->createTable('dump_advances');
    }

    public function down()
    {
        $this->forge->dropTable('dump_advances');
    }
}
