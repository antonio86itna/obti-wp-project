(function(){
  function qs(sel,ctx){return (ctx||document).querySelector(sel);}  
  function qsa(sel,ctx){return Array.prototype.slice.call((ctx||document).querySelectorAll(sel));}

  document.addEventListener('DOMContentLoaded', function(){
    var wrap = qs('#obti-dashboard');
    if(!wrap) return;
    var api = wrap.getAttribute('data-api');
    var tabs = qsa('a[data-tab]', wrap);
    function show(tab){
      qsa('.obti-tab', wrap).forEach(function(el){el.classList.add('hidden');});
      qsa('a[data-tab]', wrap).forEach(function(el){el.classList.remove('bg-theme-primary','text-white');});
      qs('#obti-tab-'+tab, wrap).classList.remove('hidden');
      qs('a[data-tab='+tab+']', wrap).classList.add('bg-theme-primary','text-white');
    }
    tabs.forEach(function(a){
      a.addEventListener('click', function(e){
        e.preventDefault();
        var t = a.getAttribute('data-tab');
        show(t);
        if(t==='bookings'){ loadBookings(); }
      });
    });
    var nameInput = qs('#obti-profile-name', wrap);
    var emailInput= qs('#obti-profile-email', wrap);
    try{
      nameInput.value = localStorage.getItem('obti_name')||'';
      emailInput.value= localStorage.getItem('obti_email')||'';
    }catch(e){}
    function saveProfile(){
      try{
        localStorage.setItem('obti_name', nameInput.value);
        localStorage.setItem('obti_email', emailInput.value);
      }catch(e){}
    }
    nameInput.addEventListener('input', saveProfile);
    emailInput.addEventListener('input', saveProfile);

    function loadBookings(){
      var email = emailInput.value.trim();
      if(!email) return;
      fetch(api + '/bookings?email=' + encodeURIComponent(email))
        .then(function(r){return r.json();})
        .then(function(list){
          var cont = qs('#obti-bookings-list', wrap);
          cont.innerHTML = '';
          (list||[]).forEach(function(b){
            var div = document.createElement('div');
            div.className = 'p-4 border rounded flex justify-between items-center';
            var actions = '';
            if(b.status === 'obti-pending'){
              actions = '<button class="obti-pay text-green-600 underline" data-id="'+b.id+'">Pay now</button>'+
                        '<button class="obti-cancel text-red-600 underline ml-2" data-id="'+b.id+'">Cancel</button>';
            } else {
              actions = '<span>'+b.status.replace('obti-','')+'</span>';
            }
            div.innerHTML = '<div><div class="font-bold">'+b.date+' '+b.time+'</div><div class="text-sm text-gray-600">'+b.qty+' tickets</div></div>'+
              '<div class="space-x-2">'+actions+'</div>';
            cont.appendChild(div);
          });
          qsa('.obti-cancel', cont).forEach(function(btn){
            btn.addEventListener('click', function(){
              var id = this.getAttribute('data-id');
              var b = (list||[]).find(function(x){ return String(x.id)===String(id); });
              if(!b) return;
              if(!confirm('Cancel booking?')) return;
              fetch(api + '/cancel', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({booking_id: b.id, token: b.token, email: email})
              }).then(function(r){return r.json();})
              .then(function(res){
                alert(res.message || res.error || 'done');
                loadBookings();
              }).catch(function(){});
            });
          });
          qsa('.obti-pay', cont).forEach(function(btn){
            btn.addEventListener('click', function(){
              var id = this.getAttribute('data-id');
              var b = (list||[]).find(function(x){ return String(x.id)===String(id); });
              if(!b) return;
              fetch(api + '/pay', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({booking_id: b.id, token: b.token, email: email, payment_method: 'pm_card_visa'})
              }).then(function(r){return r.json();})
              .then(function(res){
                alert(res.message || res.error || 'done');
                loadBookings();
              }).catch(function(){});
            });
          });
          var now = new Date();
          var upcoming = (list||[]).map(function(b){b.ts = new Date(b.date+'T'+b.time).getTime();return b;})
            .filter(function(b){return b.ts>now.getTime();})
            .sort(function(a,b){return a.ts-b.ts;})[0];
          if(upcoming){
            qs('#obti-upcoming', wrap).textContent = 'Next tour: '+upcoming.date+' '+upcoming.time;
          }
        }).catch(function(){});
    }

    show('dashboard');
    if(emailInput.value){ loadBookings(); }
  });
})();
