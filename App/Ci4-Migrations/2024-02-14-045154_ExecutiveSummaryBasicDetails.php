<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ExecutiveSummaryBasicDetails extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE,
            ],

            'year_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'assesment_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'report_submitted_date' => [
                'type' => 'DATE',
                'null' => true,
            ],    

            'staff_count' => [
                'type' => 'VARCHAR',
                'constraint' => 16,
            ],

            'manual_challans_per_day' => [
                'type' => 'VARCHAR',
                'constraint' => 16
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

        $this->forge->createTable('executive_summary_basic_details');
    }

    public function down()
    {
        $this->forge->dropTable('executive_summary_basic_details');
    }
}
