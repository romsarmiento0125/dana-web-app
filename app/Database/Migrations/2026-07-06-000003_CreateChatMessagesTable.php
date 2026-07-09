<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateChatMessagesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'conversation_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '36',
            ],
            'role' => [
                'type'       => 'ENUM',
                'constraint' => ['user', 'assistant', 'system'],
                'default'    => 'user',
            ],
            'message' => [
                'type' => 'TEXT',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('conversation_id'); // Index for fast message lookups
        $this->forge->addForeignKey('conversation_id', 'conversations', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('chat_messages');
    }

    public function down(): void
    {
        $this->forge->dropTable('chat_messages');
    }
}
