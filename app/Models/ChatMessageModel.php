<?php

namespace App\Models;

use CodeIgniter\Model;

class ChatMessageModel extends Model
{
    protected $table         = 'chat_messages';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['conversation_id', 'role', 'message', 'created_at'];
    protected $useTimestamps = false;

    /**
     * Return all messages for a conversation in chronological order.
     */
    public function getConversationMessages(string $conversationId): array
    {
        return $this->where('conversation_id', $conversationId)
                    ->orderBy('created_at', 'ASC')
                    ->orderBy('id', 'ASC') // tiebreak for same-second inserts
                    ->findAll();
    }
}
