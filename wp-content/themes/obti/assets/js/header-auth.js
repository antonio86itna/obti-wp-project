(function(){
  document.addEventListener('DOMContentLoaded', function(){
    var dropdowns = [];
    document.querySelectorAll('[data-dropdown-toggle]').forEach(function(btn){
      var id = btn.getAttribute('data-dropdown-toggle');
      var menu = document.getElementById(id);
      if (!menu) return;
      dropdowns.push({btn: btn, menu: menu});
      btn.addEventListener('click', function(e){
        e.preventDefault();
        e.stopPropagation();
        dropdowns.forEach(function(obj){ if (obj.menu !== menu) obj.menu.classList.add('hidden'); });
        menu.classList.toggle('hidden');
      });
    });
    document.addEventListener('click', function(e){
      dropdowns.forEach(function(obj){
        if (!obj.menu.classList.contains('hidden') &&
            !obj.menu.contains(e.target) &&
            !obj.btn.contains(e.target)){
          obj.menu.classList.add('hidden');
        }
      });
    });
  });
})();
