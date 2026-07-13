<style>
    #ai-assistant-toggle {
        position: fixed;
        right: 24px;
        bottom: 24px;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: #0d6efd;
        color: #fff;
        border: none;
        box-shadow: 0 4px 16px rgba(0,0,0,.25);
        font-size: 24px;
        z-index: 1050;
        cursor: pointer;
    }

    #ai-assistant-panel {
        position: fixed;
        right: 24px;
        bottom: 92px;
        width: 340px;
        max-width: calc(100vw - 32px);
        height: 480px;
        max-height: calc(100vh - 140px);
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 8px 30px rgba(0,0,0,.25);
        display: none;
        flex-direction: column;
        overflow: hidden;
        z-index: 1050;
    }

    #ai-assistant-panel.open { display: flex; }

    #ai-assistant-header {
        background: #0d6efd;
        color: #fff;
        padding: 12px 16px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    #ai-assistant-messages {
        flex: 1;
        overflow-y: auto;
        padding: 12px;
        background: #f8f9fa;
    }

    .ai-msg { margin-bottom: 10px; display: flex; }
    .ai-msg.user { justify-content: flex-end; }
    .ai-msg .bubble {
        max-width: 82%;
        padding: 8px 12px;
        border-radius: 12px;
        font-size: .875rem;
        line-height: 1.4;
        white-space: pre-wrap;
        word-break: break-word;
    }
    .ai-msg.user .bubble { background: #0d6efd; color: #fff; border-bottom-right-radius: 2px; }
    .ai-msg.bot .bubble { background: #fff; border: 1px solid #e2e5ea; border-bottom-left-radius: 2px; }
    .ai-msg .bubble a { color: inherit; text-decoration: underline; }

    #ai-assistant-form {
        display: flex;
        gap: 6px;
        padding: 10px;
        border-top: 1px solid #e2e5ea;
        background: #fff;
    }
    #ai-assistant-input {
        flex: 1;
        border: 1px solid #ced4da;
        border-radius: 20px;
        padding: 6px 14px;
        font-size: .875rem;
    }
    #ai-assistant-send {
        border: none;
        background: #0d6efd;
        color: #fff;
        border-radius: 50%;
        width: 36px;
        height: 36px;
        flex-shrink: 0;
    }
</style>

<button type="button" id="ai-assistant-toggle" title="Trợ lý mua sắm">💬</button>

<div id="ai-assistant-panel">
    <div id="ai-assistant-header">
        <span>🛍️ Trợ lý mua sắm</span>
        <button type="button" id="ai-assistant-close" class="btn-close btn-close-white" style="font-size:.7rem"></button>
    </div>
    <div id="ai-assistant-messages">
        <div class="ai-msg bot">
            <div class="bubble">Chào bạn! Mình có thể giúp tìm sản phẩm, xem chi tiết hoặc thêm vào giỏ hàng. Bạn cần gì nào?</div>
        </div>
    </div>
    <form id="ai-assistant-form">
        <input type="text" id="ai-assistant-input" placeholder="Nhập tin nhắn..." autocomplete="off" maxlength="1000" required>
        <button type="submit" id="ai-assistant-send">➤</button>
    </form>
</div>

<script>
(function () {
    'use strict';

    const csrf   = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const toggle = document.getElementById('ai-assistant-toggle');
    const panel  = document.getElementById('ai-assistant-panel');
    const closeBtn = document.getElementById('ai-assistant-close');
    const messages = document.getElementById('ai-assistant-messages');
    const form   = document.getElementById('ai-assistant-form');
    const input  = document.getElementById('ai-assistant-input');

    toggle.addEventListener('click', () => panel.classList.toggle('open'));
    closeBtn.addEventListener('click', () => panel.classList.remove('open'));

    function linkify(text) {
        const escaped = text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        return escaped.replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank" rel="noopener">$1</a>');
    }

    function appendMessage(role, text) {
        const row = document.createElement('div');
        row.className = 'ai-msg ' + (role === 'user' ? 'user' : 'bot');
        const bubble = document.createElement('div');
        bubble.className = 'bubble';
        bubble.innerHTML = linkify(text);
        row.appendChild(bubble);
        messages.appendChild(row);
        messages.scrollTop = messages.scrollHeight;
        return bubble;
    }

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const text = input.value.trim();
        if (!text) return;

        appendMessage('user', text);
        input.value = '';
        input.disabled = true;

        const thinkingBubble = appendMessage('bot', 'Đang trả lời...');

        fetch('{{ route('assistant.chat') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ message: text }),
        })
        .then(async r => {
            const data = await r.json().catch(() => ({}));
            thinkingBubble.innerHTML = linkify(data.reply || 'Có lỗi xảy ra, vui lòng thử lại.');
            messages.scrollTop = messages.scrollHeight;
        })
        .catch(() => {
            thinkingBubble.innerHTML = 'Không thể kết nối. Vui lòng thử lại.';
        })
        .finally(() => {
            input.disabled = false;
            input.focus();
        });
    });
})();
</script>
