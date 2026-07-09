<?php

namespace App\Controllers;

use App\Models\ChatMessageModel;
use App\Models\ConversationModel;
use CodeIgniter\HTTP\ResponseInterface;

class ChatController extends BaseController
{
    /**
     * n8n webhook endpoint that powers Dana's AI responses.
     */
    private string $n8nWebhookUrl;

    public function __construct()
    {
        $this->n8nWebhookUrl = (string) env(
            'n8n.webhookUrl',
            'https://n8n.rps-home-lab.com/webhook/36b8a40e-1bd2-448b-a85a-523b979d4c4b/chat'
        );
    }

    // -----------------------------------------------------------------------
    // Page
    // -----------------------------------------------------------------------

    /**
     * Render the main dashboard SPA shell.
     */
    public function dashboard()
    {
        return view('chat/dashboard', [
            'username' => session()->get('username'),
        ]);
    }

    // -----------------------------------------------------------------------
    // Conversations
    // -----------------------------------------------------------------------

    /**
     * POST /api/conversations/create
     * Body (JSON): { "title": "optional title" }
     */
    public function createConversation()
    {
        $userId = (int) session()->get('user_id');
        $body   = $this->request->getJSON(true) ?? [];
        $title  = trim($body['title'] ?? 'New Chat');
        $title  = $title !== '' ? $title : 'New Chat';

        $conversationId = $this->generateUuid();

        $conversationModel = new ConversationModel();
        $conversationModel->insert([
            'id'         => $conversationId,
            'user_id'    => $userId,
            'title'      => $title,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_CREATED)
            ->setJSON([
                'id'    => $conversationId,
                'title' => $title,
            ]);
    }

    /**
     * GET /api/conversations/list
     */
    public function listConversations()
    {
        $userId            = (int) session()->get('user_id');
        $conversationModel = new ConversationModel();

        return $this->response->setJSON(
            $conversationModel->getUserConversations($userId)
        );
    }

    /**
     * GET /api/conversations/messages/:conversationId
     */
    public function getMessages(string $conversationId)
    {
        $userId            = (int) session()->get('user_id');
        $conversationModel = new ConversationModel();

        if (! $conversationModel->findForUser($conversationId, $userId)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                ->setJSON(['error' => 'Conversation not found.']);
        }

        $messageModel = new ChatMessageModel();

        return $this->response->setJSON(
            $messageModel->getConversationMessages($conversationId)
        );
    }

    // -----------------------------------------------------------------------
    // Chat
    // -----------------------------------------------------------------------

    /**
     * POST /api/chat/send
     * Body (JSON): { "conversation_id": "...", "message": "..." }
     */
    public function send()
    {
        $userId = (int) session()->get('user_id');
        $body   = $this->request->getJSON(true) ?? [];

        $conversationId = $body['conversation_id'] ?? '';
        $userMessage    = trim($body['message'] ?? '');

        if ($conversationId === '' || $userMessage === '') {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON(['error' => 'conversation_id and message are required.']);
        }

        // Verify ownership
        $conversationModel = new ConversationModel();
        $conversation      = $conversationModel->findForUser($conversationId, $userId);

        if (! $conversation) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                ->setJSON(['error' => 'Conversation not found.']);
        }

        $messageModel = new ChatMessageModel();
        $now          = date('Y-m-d H:i:s');

        // 1. Persist the user's message immediately
        $messageModel->insert([
            'conversation_id' => $conversationId,
            'role'            => 'user',
            'message'         => $userMessage,
            'created_at'      => $now,
        ]);

        // 2. Auto-title the conversation from the first user message
        if ($conversation['title'] === 'New Chat') {
            $title = mb_strlen($userMessage) > 60
                ? mb_substr($userMessage, 0, 57) . '...'
                : $userMessage;
            $conversationModel->update($conversationId, ['title' => $title]);
        }

        // 3. Forward to n8n and get Dana's response
        $danasResponse = $this->callN8nWebhook($conversationId, $userId, $userMessage);

        // 4. Persist Dana's response (always, even on error messages)
        $messageModel->insert([
            'conversation_id' => $conversationId,
            'role'            => 'assistant',
            'message'         => $danasResponse,
            'created_at'      => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'reply'           => $danasResponse,
            'conversation_id' => $conversationId,
            'title'           => $conversation['title'] === 'New Chat'
                ? (mb_strlen($userMessage) > 60 ? mb_substr($userMessage, 0, 57) . '...' : $userMessage)
                : $conversation['title'],
        ]);
    }

    // -----------------------------------------------------------------------
    // Private helpers
    // -----------------------------------------------------------------------

    /**
     * Send the user's message to the n8n webhook and return Dana's reply.
     */
    private function callN8nWebhook(string $conversationId, int $userId, string $message): string
    {
        try {
            $client   = \Config\Services::curlrequest();
            $response = $client->post($this->n8nWebhookUrl, [
                'headers' => ['Content-Type' => 'application/json'],
                'body'    => json_encode([
                    'action'    => 'sendMessage',
                    'sessionId' => $conversationId,
                    'chatInput' => $message,
                ]),
                'timeout'         => 60,
                'connect_timeout' => 10,
                'http_errors'     => false,
            ]);

            $statusCode = $response->getStatusCode();
            $rawBody    = $response->getBody();

            if ($statusCode < 200 || $statusCode >= 300) {
                log_message('error', "[Dana] n8n returned HTTP {$statusCode}. Body: {$rawBody}");
                return 'I\'m sorry, I\'m having trouble connecting right now. Please try again in a moment.';
            }

            $decoded = json_decode($rawBody, true);

            // Accept common n8n response shapes
            return $decoded['output']
                ?? $decoded['message']
                ?? $decoded['text']
                ?? $decoded['reply']
                ?? (is_string($decoded) ? $decoded : $rawBody);
        } catch (\Throwable $e) {
            log_message('error', '[Dana] n8n webhook error: ' . $e->getMessage());

            return 'I\'m sorry, I\'m having trouble connecting right now. Please try again in a moment.';
        }
    }

    /**
     * Generate a RFC 4122-compliant UUID v4.
     */
    private function generateUuid(): string
    {
        $data    = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40); // version 4
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80); // variant

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
