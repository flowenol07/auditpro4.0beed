<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RiskMatrix extends Migration
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

            'risk_parameter' => [
                'type' => 'INT',
                'constraint' => 2,
            ],

            'business_risk_app' => [
                'type' => 'INT',
                'constraint' => 1,
            ],

            'business_risk_score' => [
                'type' => 'INT',
                'constraint' => 2,
            ],

            'control_risk_app' => [
                'type' => 'INT',
                'constraint' => 1,
            ],

            'control_risk_score' => [
                'type' => 'INT',
                'constraint' => 2,
            ],

            'residual_risk_app' => [
                'type' => 'INT',
                'constraint' => 1,
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

        $this->forge->createTable('risk_matrix');
    }

    public function down()
    {
        $this->forge->dropTable('risk_matrix');
    }
}
