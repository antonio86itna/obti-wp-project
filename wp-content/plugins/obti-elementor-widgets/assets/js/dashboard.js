(function(){
  function qs(sel,ctx){return (ctx||document).querySelector(sel);}
  function qsa(sel,ctx){return Array.from((ctx||document).querySelectorAll(sel));}

  document.addEventListener('DOMContentLoaded', function(){
    var wrap = qs('#obti-dashboard');
    if(!wrap) return;
    var api = wrap.getAttribute('data-api');

    function statusInfo(s){
      var info = {text:'Confirmed', cls:'bg-blue-100 text-blue-800'};
      if(s && s.indexOf('completed') !== -1){ info.text='Completed'; info.cls='bg-green-100 text-green-800'; }
      else if(s && s.indexOf('cancelled') !== -1){ info.text='Cancelled'; info.cls='bg-red-100 text-red-800'; }
      return info;
    }

    function show(tab){
      qsa('.obti-tab', wrap).forEach(function(el){el.classList.add('hidden');});
      qsa('a[data-tab]', wrap).forEach(function(el){el.classList.remove('bg-theme-primary','text-white');});
      qs('#obti-tab-'+tab, wrap).classList.remove('hidden');
      qs('a[data-tab='+tab+']', wrap).classList.add('bg-theme-primary','text-white');
    }
    qsa('a[data-tab]', wrap).forEach(function(a){
      a.addEventListener('click', function(e){
        e.preventDefault();
        var t = a.getAttribute('data-tab');
        show(t);
        history.replaceState(null, '', '?tab=' + t);
        if(t === 'bookings'){ loadBookings(); }
      });
    });

    var modal = qs('#obti-booking-modal');
    var closeModal = function(){ modal.classList.add('hidden'); };
    qsa('[data-close]', modal).forEach(function(btn){ btn.addEventListener('click', closeModal); });
    if(modal){
      modal.addEventListener('click', function(e){ if(e.target === modal) closeModal(); });
    }

    function openModal(b){
      var si = statusInfo(b.status);
      qs('#obti-booking-details', modal).innerHTML =
        '<p class="font-bold">'+(b.title||'')+'</p>'+
        '<p>'+(b.date||'')+'</p>'+
        '<p>'+si.text+'</p>';
      var qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?data=' + encodeURIComponent(b.id);
      qs('#obti-booking-qr', modal).innerHTML = '<img src="'+qrUrl+'" alt="QR" class="w-40 h-40" />';
      var refundBtn = qs('#obti-refund-btn', modal);
      var ts = b.date ? new Date(b.date+'T'+(b.time||'00:00')).getTime() : 0;
      if(ts - Date.now() < 72*3600*1000){
        refundBtn.classList.remove('hidden');
        refundBtn.onclick = function(){
          fetch(api + '/cancel', {
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify({id:b.id})
          }).then(function(){ closeModal(); loadBookings(); }).catch(function(){});
        };
      }else{
        refundBtn.classList.add('hidden');
        refundBtn.onclick = null;
      }
      modal.classList.remove('hidden');
    }

    var nameInput = qs('#obti-profile-name', wrap);
    var emailInput = qs('#obti-profile-email', wrap);
    var saveBtn = qs('#obti-profile-save', wrap);
    var newPassInput = qs('#obti-new-password', wrap);
    var confirmPassInput = qs('#obti-confirm-password', wrap);
    var passSaveBtn = qs('#obti-password-save', wrap);

    function loadProfile(){
      fetch(api + '/me')
        .then(function(r){ return r.json(); })
        .then(function(u){
          if(nameInput){ nameInput.value = [u.first_name||'', u.last_name||''].join(' ').trim(); }
          if(emailInput){ emailInput.value = u.email||''; }
        }).catch(function(){});
    }
    loadProfile();

    if(saveBtn){
      saveBtn.addEventListener('click', function(){
        var full = nameInput.value.trim().split(' ');
        var first = full.shift()||'';
        var last = full.join(' ');
        fetch(api + '/me', {
          method: 'POST',
          headers: {'Content-Type':'application/json'},
          body: JSON.stringify({first_name:first,last_name:last,email:emailInput.value.trim()})
        }).then(function(){ alert('Profilo aggiornato'); }).catch(function(){});
      });
    }
    if(passSaveBtn){
      passSaveBtn.addEventListener('click', function(){
        var p1 = newPassInput.value;
        var p2 = confirmPassInput.value;
        if(p1 !== p2 || !p1){ alert('Le password non coincidono'); return; }
        fetch(api + '/password', {
          method: 'POST',
          headers: {'Content-Type':'application/json'},
          body: JSON.stringify({password:p1})
        }).then(function(){
          alert('Password aggiornata');
          newPassInput.value='';
          confirmPassInput.value='';
        }).catch(function(){});
      });
    }

    function loadBookings(){
      fetch(api + '/my-bookings')
        .then(function(r){ return r.json(); })
        .then(function(list){
          var cont = qs('#obti-bookings-list', wrap);
          cont.innerHTML = '';
          var active = 0, completed = 0, upcoming = null, upTs = null;
          (list||[]).forEach(function(b){
            var si = statusInfo(b.status);
            if(b.status && b.status.indexOf('completed') !== -1){ completed++; }
            else if(b.status && b.status.indexOf('cancelled') !== -1){ }
            else { active++; }
            var li = document.createElement('li');
            li.className = 'p-4 border rounded flex justify-between items-center';
            li.innerHTML = '<div><div class="font-semibold">'+(b.title||'')+'</div><div class="text-sm text-gray-600">'+(b.date||'')+'</div></div>'+
              '<div class="flex items-center space-x-2"><span class="px-2 py-1 rounded text-xs font-semibold '+si.cls+'">'+si.text+'</span><button class="obti-detail text-theme-primary underline" data-id="'+b.id+'">Dettagli</button></div>';
            cont.appendChild(li);
            var ts = b.date ? new Date(b.date+'T'+(b.time||'00:00')).getTime() : null;
            if(ts && (!upTs || ts < upTs) && si.text === 'Confirmed'){
              upcoming = b; upTs = ts;
            }
          });
          qs('#obti-active-count', wrap).textContent = active;
          qs('#obti-completed-count', wrap).textContent = completed;
          if(upcoming){
            var card = qs('#obti-upcoming-card', wrap);
            qs('#obti-upcoming-title', wrap).textContent = upcoming.title || '';
            qs('#obti-upcoming-date', wrap).textContent = upcoming.date || '';
            var qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?data='+encodeURIComponent(upcoming.id);
            qs('#obti-upcoming-qr', wrap).innerHTML = '<img src="'+qrUrl+'" alt="QR" class="w-20 h-20" />';
            card.classList.remove('hidden');
          }
          qsa('.obti-detail', cont).forEach(function(btn){
            btn.addEventListener('click', function(){
              var id = btn.getAttribute('data-id');
              var b = (list||[]).find(function(x){ return String(x.id)===String(id); });
              if(b) openModal(b);
            });
          });
        }).catch(function(){});
    }

    loadBookings();
    var initialTab = new URLSearchParams(location.search).get('tab') || 'dashboard';
    show(initialTab);
  });
})();
