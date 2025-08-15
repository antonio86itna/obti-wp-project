(function(){
  function qs(sel,ctx){ return (ctx||document).querySelector(sel); }
  function qsa(sel,ctx){ return Array.prototype.slice.call((ctx||document).querySelectorAll(sel)); }
  function fmtEUR(n){ try{ return new Intl.NumberFormat('it-IT',{style:'currency',currency:'EUR'}).format(n); }catch(e){ return '€'+n.toFixed(2); } }

  document.addEventListener('DOMContentLoaded', function(){
    var form = qs('#obti-booking-form');
    if (!form) return;
    var api = form.getAttribute('data-api');
    var date = qs("#date-picker", form);
    var time = qs('select[name="time"]', form);
    var qty  = qs('input[name="qty"]', form);
    var name = qs('input[name="name"]', form);
    var email= qs('input[name="email"]', form);
    var sumQty = qs('#obti-sum-qty');
    var sumTotal = qs('#obti-sum-total');
    var availLabel = qs('#obti-availability-label');
    var payBtn = qs('#obti-pay-btn');

    flatpickr('#date-picker', { minDate: 'today', dateFormat: 'Y-m-d' });

    function updateAvailability(){
      if (!date.value) return;
      fetch(api + '/availability?date=' + encodeURIComponent(date.value))
      .then(r=>r.json())
      .then(d=>{
        var slot = (d.slots||[]).find(s => s.time === time.value);
        if (!slot){ availLabel.textContent = ''; return; }
        var avail = slot.available;
        qty.max = avail > 0 ? avail : 1;
        if (parseInt(qty.value||'1',10) > avail) qty.value = Math.max(1, avail);
        availLabel.textContent = slot.cutoff_passed ? 'Booking closed for this time' : (avail + ' seats left');
        payBtn.disabled = slot.cutoff_passed || avail <= 0;
        recalc();
      }).catch(()=>{});
    }

    function recalc(){
      var q = parseInt(qty.value||'1',10);
      sumQty.textContent = q;
      var unit = parseFloat((sumTotal.getAttribute('data-unit')||'').replace(',','.'));
      if (!unit){
        var el = document.querySelector('.text-2xl.font-bold + div .flex.justify-between:nth-child(2) span:last-child');
        if (el){
          unit = parseFloat(el.textContent.replace('€','').replace(',','.'));
        } else {
          unit = 20.00;
        }
      }
      var total = q * unit;
      sumTotal.textContent = fmtEUR(total).replace('€','€');
    }

    date.addEventListener('change', updateAvailability);
    time.addEventListener('change', updateAvailability);
    qty.addEventListener('input', recalc);
    recalc();
    updateAvailability();

    payBtn.addEventListener('click', function(e){
      e.preventDefault();
      var payload = {
        date: date.value,
        time: time.value,
        qty: parseInt(qty.value||'1',10),
        name: name.value.trim(),
        email: email.value.trim()
      };
      if (!payload.date || !payload.time || !payload.qty || !payload.name || !payload.email){ alert('Please fill all fields'); return; }
      payBtn.disabled = true;
      fetch(api + '/checkout', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify(payload)
      }).then(r=>r.json()).then(d=>{
        if (d.checkout_url){ window.location.href = d.checkout_url; }
        else { alert(d.error || 'Error'); payBtn.disabled = false; }
      }).catch(()=>{ alert('Network error'); payBtn.disabled = false; });
    });
  });
})();
