<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dana AI — Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        gray: { 950: '#0a0a0f' }
                    },
                    typography: {
                        DEFAULT: { css: { color: '#d1d5db', a: { color: '#818cf8' } } }
                    }
                }
            }
        }
    </script>
    <style>
        /* Thin custom scrollbar */
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #374151; border-radius: 99px; }

        :root {
            color-scheme: dark;
        }

        body {
            min-height: 100dvh;
        }

        .safe-bottom {
            padding-bottom: max(1rem, env(safe-area-inset-bottom));
        }

        /* Smooth typing-indicator dots */
        @keyframes bounce-dot {
            0%, 80%, 100% { transform: translateY(0); opacity: .4; }
            40%           { transform: translateY(-5px); opacity: 1; }
        }
        .typing-dot { animation: bounce-dot 1.2s ease-in-out infinite; }
        .typing-dot:nth-child(2) { animation-delay: .2s; }
        .typing-dot:nth-child(3) { animation-delay: .4s; }

        /* Fade-in for new messages */
        @keyframes msg-in {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .msg-animate { animation: msg-in .25s ease-out forwards; }

        /* Markdown-like pre/code styling inside assistant bubbles */
        .dana-content pre {
            background: #111827;
            border: 1px solid #374151;
            border-radius: .5rem;
            padding: .75rem 1rem;
            overflow-x: auto;
            font-size: .8125rem;
            line-height: 1.6;
            margin-top: .5rem;
        }
        .dana-content code:not(pre code) {
            background: #1f2937;
            border-radius: .25rem;
            padding: .1rem .35rem;
            font-size: .8125rem;
        }
        .dana-content p { margin-bottom: .5rem; }
        .dana-content ul { list-style: disc inside; margin-bottom: .5rem; }
        .dana-content ol { list-style: decimal inside; margin-bottom: .5rem; }
        .dana-content li { margin-bottom: .2rem; }
        .dana-content strong { color: #f3f4f6; }

        @media (max-width: 767px) {
            #messages-container {
                scroll-padding-bottom: 7rem;
            }
        }
    </style>
</head>

<body class="h-full min-h-[100dvh] bg-gray-950 text-gray-100 overflow-hidden">

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- App Shell                                                              -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div class="relative flex h-full min-h-[100dvh]">

    <button id="sidebar-backdrop"
            type="button"
            class="fixed inset-0 z-20 hidden bg-gray-950/70 backdrop-blur-sm md:hidden"
            aria-label="Close conversation history"
            onclick="closeSidebar()"></button>

    <!-- ================================================================= -->
    <!-- LEFT SIDEBAR                                                       -->
    <!-- ================================================================= -->
    <aside id="sidebar"
           class="fixed inset-y-0 left-0 z-30 flex w-[85vw] max-w-xs shrink-0 -translate-x-full flex-col bg-gray-900 border-r border-gray-800 select-none transition-transform duration-200 ease-out md:static md:z-auto md:w-64 md:max-w-none md:translate-x-0"
           aria-hidden="true">

        <!-- Brand -->
        <div class="flex items-center justify-between gap-2.5 px-4 py-4 border-b border-gray-800">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-600 shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
                    </svg>
                </div>
                <span class="font-semibold text-white text-sm tracking-tight truncate">Dana AI</span>
            </div>
            <button type="button"
                    class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-800 hover:text-white md:hidden"
                    aria-label="Close conversation history"
                    onclick="closeSidebar()">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- New Chat Button -->
        <div class="px-3 pt-3 pb-2">
            <button id="btn-new-chat" onclick="newChat()"
                    class="w-full flex items-center gap-2 rounded-xl bg-indigo-600 hover:bg-indigo-500
                           text-white text-sm font-medium px-3 py-2 transition focus:outline-none
                           focus:ring-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                New Chat
            </button>
        </div>

        <!-- Conversation List -->
        <div class="flex-1 overflow-y-auto px-2 space-y-0.5 pb-2">
            <p class="px-2 pt-2 pb-1 text-[10px] font-semibold uppercase tracking-widest text-gray-500">History</p>
            <ul id="conversation-list" class="space-y-0.5">
                <!-- Populated by JS -->
                <li class="text-xs text-gray-600 px-2 py-2 italic" id="no-chats-msg">No conversations yet.</li>
            </ul>
        </div>

        <!-- User / Logout -->
        <div class="border-t border-gray-800 px-3 py-3">
            <div class="flex items-center gap-2.5">
                <div class="flex items-center justify-center w-7 h-7 rounded-full bg-indigo-700 text-xs font-bold text-white shrink-0">
                    <?= strtoupper(substr(esc($username ?? 'U'), 0, 1)) ?>
                </div>
                <span class="text-sm text-gray-300 truncate flex-1"><?= esc($username ?? 'User') ?></span>
                <a href="/logout"
                   class="flex items-center justify-center w-7 h-7 rounded-lg text-gray-500 hover:text-white hover:bg-gray-800 transition"
                   title="Sign out">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25" />
                    </svg>
                </a>
            </div>
        </div>
    </aside>

    <!-- ================================================================= -->
    <!-- MAIN CHAT PANE                                                     -->
    <!-- ================================================================= -->
    <main class="flex min-w-0 flex-1 flex-col bg-gray-950">

        <!-- Top bar -->
        <header class="flex items-center gap-3 border-b border-gray-800 px-3 py-3 shrink-0 sm:px-4 md:px-6 md:py-3.5">
            <button type="button"
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-gray-800 bg-gray-900 text-gray-300 transition hover:border-gray-700 hover:text-white md:hidden"
                    aria-label="Open conversation history"
                    aria-controls="sidebar"
                    aria-expanded="false"
                    onclick="openSidebar()">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5m-16.5 5.25h16.5m-16.5 5.25h10.5" />
                </svg>
            </button>
            <div id="chat-header-icon"
                 class="flex items-center justify-center w-7 h-7 rounded-lg bg-indigo-600/20 text-indigo-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <h2 id="chat-title" class="text-sm font-semibold text-white leading-tight">Dana AI</h2>
                <p class="truncate text-xs text-gray-500">Your Data Analyst Agent</p>
            </div>
            <button type="button"
                    onclick="newChat()"
                    class="inline-flex h-9 shrink-0 items-center gap-2 rounded-xl border border-gray-800 bg-gray-900 px-3 text-xs font-medium text-gray-200 transition hover:border-gray-700 hover:text-white md:hidden">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                New
            </button>
        </header>

        <!-- Messages -->
        <div id="messages-container"
               class="flex-1 overflow-y-auto px-3 py-3 space-y-3 sm:px-4 md:px-5 lg:px-8 xl:px-10">

            <!-- Welcome / empty state -->
            <div id="welcome-screen" class="flex h-full flex-col items-center justify-center gap-4 py-12 text-center sm:py-16 md:py-20">
                <div class="w-16 h-16 rounded-2xl bg-indigo-600/20 flex items-center justify-center">
                    <svg class="w-8 h-8 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-white sm:text-xl">How can Dana help you today?</h3>
                    <p class="mt-1 max-w-xs text-sm text-gray-500 sm:max-w-sm">
                        Ask about your data, request analysis, generate reports, or explore insights.
                    </p>
                </div>
                <!-- <div class="flex flex-wrap justify-center gap-2 mt-2">
                    <button onclick="usePrompt('Summarize last month\'s sales trends')"
                            class="text-xs bg-gray-800 hover:bg-gray-700 text-gray-300 border border-gray-700
                                   rounded-xl px-3 py-2 transition">
                        📊 Summarize last month's sales trends
                    </button>
                    <button onclick="usePrompt('What were the top 5 products by revenue?')"
                            class="text-xs bg-gray-800 hover:bg-gray-700 text-gray-300 border border-gray-700
                                   rounded-xl px-3 py-2 transition">
                        🏆 Top 5 products by revenue
                    </button>
                    <button onclick="usePrompt('Show me a week-over-week comparison')"
                            class="text-xs bg-gray-800 hover:bg-gray-700 text-gray-300 border border-gray-700
                                   rounded-xl px-3 py-2 transition">
                        📈 Week-over-week comparison
                    </button>
                </div> -->
            </div>

        </div>

        <!-- Input bar -->
                <div class="safe-bottom shrink-0 border-t border-gray-800 bg-gray-900 px-3 py-3 sm:px-4 md:px-8 md:py-4 lg:px-16 xl:px-32">
            <form id="chat-form" onsubmit="sendMessage(event)"
                  class="flex items-end gap-3">
                <div class="flex-1 relative">
                    <textarea
                        id="user-input"
                        rows="1"
                        placeholder="Message Dana..."
                        onkeydown="handleInputKeydown(event)"
                        oninput="autoResize(this)"
                           class="w-full resize-none rounded-2xl bg-gray-800 border border-gray-700 text-white
                               placeholder-gray-500 px-4 py-3 pr-4 text-sm leading-relaxed
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                               transition max-h-40 overflow-y-auto"
                    ></textarea>
                </div>
                <button id="send-btn" type="submit"
                           class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-600
                               hover:bg-indigo-500 disabled:opacity-40 disabled:cursor-not-allowed
                               text-white transition shrink-0 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        disabled>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                    </svg>
                </button>
            </form>
            <p class="mt-2 px-1 text-center text-[10px] leading-relaxed text-gray-600">Dana may make mistakes. Always verify important data.</p>
        </div>

    </main>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- JavaScript                                                             -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<script>
'use strict';

// ── State ────────────────────────────────────────────────────────────────────
let activeConversationId = null;
let isSending = false;

// ── Init ─────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    loadConversations();

    // Enable send button only when input has content
    const input = document.getElementById('user-input');
    input.addEventListener('input', () => {
        document.getElementById('send-btn').disabled = input.value.trim() === '';
    });

    window.addEventListener('resize', syncSidebarState);
    document.addEventListener('keydown', handleEscapeKey);
    syncSidebarState();
});

// ── Sidebar ───────────────────────────────────────────────────────────────────
function isDesktopViewport() {
    return window.matchMedia('(min-width: 768px)').matches;
}

function setSidebarOpen(isOpen) {
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebar-backdrop');
    const menuButton = document.querySelector('[aria-controls="sidebar"]');

    if (!sidebar || !backdrop) {
        return;
    }

    if (isDesktopViewport()) {
        sidebar.classList.remove('-translate-x-full');
        backdrop.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        sidebar.setAttribute('aria-hidden', 'false');
        if (menuButton) menuButton.setAttribute('aria-expanded', 'false');
        return;
    }

    sidebar.classList.toggle('-translate-x-full', !isOpen);
    backdrop.classList.toggle('hidden', !isOpen);
    document.body.classList.toggle('overflow-hidden', isOpen);
    sidebar.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
    if (menuButton) menuButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
}

function syncSidebarState() {
    setSidebarOpen(false);
}

function openSidebar() {
    setSidebarOpen(true);
}

function closeSidebar() {
    setSidebarOpen(false);
}

function handleEscapeKey(event) {
    if (event.key === 'Escape' && !isDesktopViewport()) {
        closeSidebar();
    }
}

async function loadConversations() {
    try {
        const res  = await fetch('/api/conversations/list');
        const data = await res.json();
        renderSidebar(data);
    } catch (err) {
        console.error('Failed to load conversations:', err);
    }
}

function renderSidebar(conversations) {
    const list       = document.getElementById('conversation-list');
    const noChatMsg  = document.getElementById('no-chats-msg');

    // Remove existing conversation items (keep the no-chats placeholder)
    list.querySelectorAll('[data-conv-item]').forEach(el => el.remove());

    if (!conversations || conversations.length === 0) {
        noChatMsg.style.display = '';
        return;
    }

    noChatMsg.style.display = 'none';

    conversations.forEach(conv => {
        const li  = document.createElement('li');
        li.setAttribute('data-conv-item', conv.id);

        const btn = document.createElement('button');
        btn.className = 'w-full text-left flex items-center gap-2 px-3 py-2 rounded-xl text-sm transition ' +
                        'text-gray-400 hover:text-white hover:bg-gray-800 group';
        btn.setAttribute('data-conv-id', conv.id);
        btn.onclick = () => loadConversation(conv.id, conv.title);

        btn.innerHTML = `
            <svg class="w-3.5 h-3.5 shrink-0 opacity-50 group-hover:opacity-100" fill="none" viewBox="0 0 24 24"
                 stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M2.25 12.76c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
            </svg>
            <span class="truncate">${escapeHtml(conv.title)}</span>`;

        li.appendChild(btn);
        list.appendChild(li);
    });

    // Highlight the active conversation if one is open
    if (activeConversationId) {
        highlightActiveConversation(activeConversationId);
    }
}

function highlightActiveConversation(id) {
    document.querySelectorAll('[data-conv-id]').forEach(btn => {
        const isActive = btn.getAttribute('data-conv-id') === id;
        btn.classList.toggle('bg-gray-800', isActive);
        btn.classList.toggle('text-white', isActive);
        btn.classList.toggle('text-gray-400', !isActive);
    });
}

// ── Conversation Actions ──────────────────────────────────────────────────────
async function newChat() {
    try {
        closeSidebar();

        const res  = await fetch('/api/conversations/create', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ title: 'New Chat' }),
        });
        const data = await res.json();

        activeConversationId = data.id;
        clearMessages();
        updateChatTitle('New Chat');
        await loadConversations();
        highlightActiveConversation(data.id);
        document.getElementById('user-input').focus();
    } catch (err) {
        console.error('Failed to create conversation:', err);
    }
}

async function loadConversation(conversationId, title) {
    activeConversationId = conversationId;
    closeSidebar();
    clearMessages();
    updateChatTitle(title);
    highlightActiveConversation(conversationId);

    try {
        const res      = await fetch(`/api/conversations/messages/${conversationId}`);
        const messages = await res.json();

        if (Array.isArray(messages) && messages.length > 0) {
            messages.forEach(msg => appendMessage(msg.role, msg.message, false));
        }

        scrollToBottom();
        document.getElementById('user-input').focus();
    } catch (err) {
        console.error('Failed to load messages:', err);
    }
}

// ── Send Message ──────────────────────────────────────────────────────────────
async function sendMessage(event) {
    event.preventDefault();

    if (isSending) return;

    const input   = document.getElementById('user-input');
    const message = input.value.trim();

    if (!message) return;

    // Auto-create a conversation if none is active
    if (!activeConversationId) {
        await newChat();
    }

    // Optimistically render user message
    appendMessage('user', message);
    input.value = '';
    input.style.height = 'auto';
    document.getElementById('send-btn').disabled = true;

    // Show typing indicator
    const typingEl = showTypingIndicator();
    isSending = true;

    try {
        const res  = await fetch('/api/chat/send', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                conversation_id: activeConversationId,
                message:         message,
            }),
        });

        removeTypingIndicator(typingEl);

        const data = await res.json();

        if (!res.ok) {
            appendMessage('assistant', '⚠️ ' + (data.error || 'An error occurred. Please try again.'));
        } else {
            appendMessage('assistant', data.reply);
            // Update sidebar title if it was auto-set
            if (data.title) {
                updateSidebarTitle(activeConversationId, data.title);
                updateChatTitle(data.title);
            }
        }
    } catch (err) {
        removeTypingIndicator(typingEl);
        appendMessage('assistant', '⚠️ Could not reach Dana. Please check your connection and try again.');
        console.error('Send error:', err);
    } finally {
        isSending = false;
    }
}

// ── Message Rendering ─────────────────────────────────────────────────────────
function appendMessage(role, content, animate = true) {
    const container   = document.getElementById('messages-container');
    const welcomeEl   = document.getElementById('welcome-screen');

    // Hide welcome screen once we have messages
    if (welcomeEl) welcomeEl.style.display = 'none';

    const wrapper = document.createElement('div');
    wrapper.className = 'flex ' + (role === 'user' ? 'justify-end' : 'justify-start');
    if (animate) wrapper.classList.add('msg-animate');

    if (role === 'user') {
        wrapper.innerHTML = `
            <div class="max-w-[94%] sm:max-w-[92%] md:max-w-[86%] lg:max-w-[80%] w-fit ml-auto">
                <div class="rounded-2xl rounded-br-sm bg-indigo-600 text-white px-4 py-3 text-sm leading-snug
                            whitespace-pre-wrap break-words">${escapeHtml(content)}</div>
            </div>`;
    } else {
        wrapper.innerHTML = `
            <div class="flex max-w-[94%] items-start gap-2.5 sm:max-w-[90%] md:max-w-[82%]">
                <div class="flex items-center justify-center w-7 h-7 rounded-lg bg-indigo-600/20 text-indigo-400
                            shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
                    </svg>
                </div>
                <div class="rounded-2xl rounded-tl-sm bg-gray-800 border border-gray-700 text-gray-200
                            px-4 py-3 text-sm leading-relaxed break-words dana-content">
                    ${formatDanaContent(content)}
                </div>
            </div>`;
    }

    container.appendChild(wrapper);
    scrollToBottom();
}

function clearMessages() {
    const container = document.getElementById('messages-container');
    // Remove all child nodes except the welcome screen
    const welcome = document.getElementById('welcome-screen');
    container.innerHTML = '';
    if (welcome) {
        welcome.style.display = '';
        container.appendChild(welcome);
    }
}

// ── Typing Indicator ──────────────────────────────────────────────────────────
function showTypingIndicator() {
    const container = document.getElementById('messages-container');
    const welcome   = document.getElementById('welcome-screen');
    if (welcome) welcome.style.display = 'none';

    const el = document.createElement('div');
    el.id = 'typing-indicator';
    el.className = 'flex items-start gap-2.5';
    el.innerHTML = `
        <div class="flex items-center justify-center w-7 h-7 rounded-lg bg-indigo-600/20 text-indigo-400 shrink-0 mt-0.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
            </svg>
        </div>
        <div class="rounded-2xl rounded-tl-sm bg-gray-800 border border-gray-700 px-4 py-3 flex items-center gap-1.5">
            <span class="text-xs text-gray-400 mr-1">Dana is analyzing</span>
            <span class="typing-dot w-1.5 h-1.5 rounded-full bg-indigo-400 inline-block"></span>
            <span class="typing-dot w-1.5 h-1.5 rounded-full bg-indigo-400 inline-block"></span>
            <span class="typing-dot w-1.5 h-1.5 rounded-full bg-indigo-400 inline-block"></span>
        </div>`;

    container.appendChild(el);
    scrollToBottom();
    return el;
}

function removeTypingIndicator(el) {
    if (el && el.parentNode) el.parentNode.removeChild(el);
}

// ── Utilities ─────────────────────────────────────────────────────────────────
function scrollToBottom() {
    const container = document.getElementById('messages-container');
    container.scrollTop = container.scrollHeight;
}

function updateChatTitle(title) {
    const el = document.getElementById('chat-title');
    if (el) el.textContent = title || 'Dana AI';
}

function updateSidebarTitle(conversationId, title) {
    const btn = document.querySelector(`[data-conv-id="${conversationId}"] span`);
    if (btn) btn.textContent = title;
}

function handleInputKeydown(event) {
    // Submit on Enter, new line on Shift+Enter
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        if (!isSending && document.getElementById('user-input').value.trim() !== '') {
            sendMessage(event);
        }
    }
}

function autoResize(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = Math.min(textarea.scrollHeight, 160) + 'px';
}

function usePrompt(text) {
    const input = document.getElementById('user-input');
    input.value = text;
    input.focus();
    autoResize(input);
    document.getElementById('send-btn').disabled = false;
}

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

/**
 * Lightweight Markdown-like formatter for Dana's responses.
 * Handles: bold, inline code, code blocks, and line breaks.
 */
function formatDanaContent(text) {
    // Escape first, then selectively un-escape for tags we generate
    let safe = escapeHtml(text);

    // Code blocks (```...```)
    safe = safe.replace(/```([a-z]*)\n?([\s\S]*?)```/g, (_, lang, code) => {
        return `<pre><code>${code.trim()}</code></pre>`;
    });

    // Inline code (`...`)
    safe = safe.replace(/`([^`]+)`/g, '<code>$1</code>');

    // Bold (**...** or __...__)
    safe = safe.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    safe = safe.replace(/__(.+?)__/g, '<strong>$1</strong>');

    // Italic (*...* or _..._)
    safe = safe.replace(/\*([^*]+)\*/g, '<em>$1</em>');
    safe = safe.replace(/_([^_]+)_/g, '<em>$1</em>');

    // Unordered list items
    safe = safe.replace(/^[•\-\*] (.+)$/gm, '<li>$1</li>');
    safe = safe.replace(/(<li>[\s\S]+?<\/li>)/g, '<ul>$1</ul>');

    // Numbered list
    safe = safe.replace(/^\d+\. (.+)$/gm, '<li>$1</li>');

    // Paragraphs (double newline)
    safe = safe.replace(/\n{2,}/g, '</p><p>');

    // Single newlines → <br>
    safe = safe.replace(/\n/g, '<br>');

    return `<p>${safe}</p>`;
}
</script>

</body>
</html>
