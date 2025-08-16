(function(){
  function qs(sel,ctx){return (ctx||document).querySelector(sel);}
  function qsa(sel,ctx){return Array.from((ctx||document).querySelectorAll(sel));}

  document.addEventListener('DOMContentLoaded', function(){
    var wrap = qs('#obti-dashboard');
    if(!wrap) return;
    var api = wrap.getAttribute('data-api');

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
        if(t === 'bookings'){ loadBookings(); }
      });
    });

    var avatarBtn = qs('#obti-avatar-btn', wrap);
    var avatarMenu = qs('#obti-avatar-menu', wrap);
    if(avatarBtn && avatarMenu){
      avatarBtn.addEventListener('click', function(e){
        e.stopPropagation();
        avatarMenu.classList.toggle('hidden');
      });
      document.addEventListener('click', function(){ avatarMenu.classList.add('hidden'); });
    }

    var modal = qs('#obti-booking-modal');
    var closeModal = function(){ modal.classList.add('hidden'); };
    qsa('[data-close]', modal).forEach(function(btn){ btn.addEventListener('click', closeModal); });
    if(modal){
      modal.addEventListener('click', function(e){ if(e.target === modal) closeModal(); });
    }

    function openModal(b){
      qs('#obti-booking-details', modal).innerHTML =
        '<p class="font-bold">'+(b.title||'')+'</p>'+
        '<p>'+(b.date||'')+'</p>'+
        '<p>'+(b.status||'')+'</p>';
      qs('#obti-booking-qr', modal).innerHTML = b.qr ? '<img src="'+b.qr+'" alt="QR" class="w-40 h-40" />' : '';
      var refundBtn = qs('#obti-refund-btn', modal);
      var ts = b.date ? new Date(b.date+'T'+(b.time||'00:00')).getTime() : 0;
      if(ts - Date.now() > 72*3600*1000){
        refundBtn.classList.remove('hidden');
        refundBtn.onclick = function(){ alert('Richiesta di rimborso inviata'); };
      }else{
        refundBtn.classList.add('hidden');
        refundBtn.onclick = null;
      }
      modal.classList.remove('hidden');
    }

    var nameInput = qs('#obti-profile-name', wrap);
    var emailInput = qs('#obti-profile-email', wrap);
    var saveBtn = qs('#obti-profile-save', wrap);
    if(saveBtn){
      saveBtn.addEventListener('click', function(){
        try{
          localStorage.setItem('obti_name', nameInput.value);
          localStorage.setItem('obti_email', emailInput.value);
        }catch(e){}
        alert('Profilo aggiornato');
      });
    }

    function loadBookings(){
      var email = emailInput.value.trim();
      if(!email) return;
      fetch(api + '/bookings?email=' + encodeURIComponent(email))
        .then(function(r){ return r.json(); })
        .then(function(list){
          var cont = qs('#obti-bookings-list', wrap);
          cont.innerHTML = '';
          var active = 0, completed = 0;
          (list||[]).forEach(function(b){
            if(b.status && b.status.indexOf('completed') !== -1){ completed++; } else { active++; }
            var li = document.createElement('li');
            li.className = 'p-4 border rounded flex justify-between items-center';
            li.innerHTML = '<div><div class="font-semibold">'+(b.title||'')+'</div><div class="text-sm text-gray-600">'+(b.date||'')+'</div></div>'+
              '<div class="flex items-center space-x-2"><span class="text-sm">'+(b.status||'')+'</span><button class="obti-detail text-theme-primary underline" data-id="'+b.id+'">Dettagli</button></div>';
            cont.appendChild(li);
          });
          qs('#obti-active-count', wrap).textContent = active;
          qs('#obti-completed-count', wrap).textContent = completed;
          qsa('.obti-detail', cont).forEach(function(btn){
            btn.addEventListener('click', function(){
              var id = btn.getAttribute('data-id');
              var b = (list||[]).find(function(x){ return String(x.id)===String(id); });
              if(b) openModal(b);
            });
          });
        }).catch(function(){});
    }

    show('dashboard');
  });
})();
