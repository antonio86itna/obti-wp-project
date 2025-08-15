(function(){
  document.addEventListener('DOMContentLoaded', function(){
    var wrap = document.getElementById('obti-chatbot');
    if(!wrap) return;
    var cfg = window.obtiConfig || {};
    var apiKey = wrap.getAttribute('data-api-key') || cfg.chatbot_api_key || '';

    var toggleBtn = document.createElement('button');
    toggleBtn.className = 'obti-chatbot-toggle';
    toggleBtn.innerHTML = '<i data-lucide="message-circle"></i>';
    document.body.appendChild(toggleBtn);

    var box = document.createElement('div');
    box.className = 'obti-chatbot-box hidden';
    box.innerHTML = '\
<div class="obti-chat-header">\
  <span>Chatbot</span>\
  <button type="button" class="obti-chat-close">&times;</button>\
</div>\
<div class="obti-chat-messages"></div>\
<form class="obti-chat-form">\
  <input type="text" placeholder="Chiedi..." class="obti-chat-input" />\
  <button type="submit">Invia</button>\
</form>';
    document.body.appendChild(box);

    function toggle(){
      box.classList.toggle('hidden');
      if(window.lucide){ lucide.createIcons(); }
    }

    toggleBtn.addEventListener('click', toggle);
    box.querySelector('.obti-chat-close').addEventListener('click', toggle);

    var messages = box.querySelector('.obti-chat-messages');
    var form = box.querySelector('.obti-chat-form');

    function addMessage(role, text){
      var div = document.createElement('div');
      div.className = 'obti-chat-' + role;
      div.textContent = text;
      messages.appendChild(div);
      messages.scrollTop = messages.scrollHeight;
    }

    form.addEventListener('submit', function(e){
      e.preventDefault();
      var input = box.querySelector('.obti-chat-input');
      var text = input.value.trim();
      if(!text) return;
      addMessage('user', text);
      input.value = '';
      fetch('https://example.com/chat', {
        method: 'POST',
        headers: { 'Content-Type':'application/json', 'Authorization': 'Bearer ' + apiKey },
        body: JSON.stringify({ message: text })
      })
      .then(function(r){ return r.json(); })
      .then(function(d){ addMessage('bot', d.answer || 'Nessuna risposta'); })
      .catch(function(){ addMessage('bot', 'Errore di rete'); });
    });
  });
})();
