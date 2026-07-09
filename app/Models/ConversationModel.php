<?php

namespace App\Models;

use CodeIgniter\Model;

class ConversationModel extends Model
{
    protected $table         = 'conversations';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['id', 'user_id', 'title', 'created_at'];
    protected $useTimestamps = false;

    /**
     * Return all conversations for a user, newest first.
     */
    public function getUserConversations(int $userId): array
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Verify a conversation belongs to a given user and return it.
     */
    public function findForUser(string $conversationId, int $userId): ?array
    {
        return $this->where('id', $conversationId)
                    ->where('user_id', $userId)
                    ->first();
    }
}
