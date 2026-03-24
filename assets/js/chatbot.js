const form = document.getElementById('chat-form');
const input = document.getElementById('chat-input');
const chatBox = document.getElementById('chat-messages');

if (form && input && chatBox) {
  form.addEventListener('submit', async function(e) {
    e.preventDefault();
    const msg = input.value.trim();
    if (!msg) return;
    appendMessage(msg, 'user');
    input.value = '';
    const typing = appendTyping();
    try {
      const res = await fetch('chatbot-api.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'message=' + encodeURIComponent(msg)
      });
      const data = await res.json();
      typing.remove();
      appendMessage(data.reply || 'No response.', 'ai');
    } catch (err) {
      typing.remove();
      appendMessage('Network issue. Please try again.', 'ai');
    }
  });
}

function appendMessage(text, who) {
  const div = document.createElement('div');
  div.className = who === 'ai' ? 'msg-ai' : 'msg-user';
  div.textContent = text;
  chatBox.appendChild(div);
  chatBox.scrollTop = chatBox.scrollHeight;
  return div;
}

function appendTyping() {
  const d = document.createElement('div');
  d.className = 'msg-ai typing';
  d.textContent = '...';
  chatBox.appendChild(d);
  chatBox.scrollTop = chatBox.scrollHeight;
  return d;
}

document.querySelectorAll('[data-q]').forEach((el) => {
  el.addEventListener('click', () => {
    input.value = el.getAttribute('data-q') || '';
    input.focus();
  });
});
