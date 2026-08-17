(function(){
  function active(){return String(location.hash||'').toLowerCase().indexOf('/usuario')!==-1;}
  function enhance(){
    if(!active()){document.body.classList.remove('security-modern');return;}
    document.body.classList.add('security-modern');var app=document.getElementById('app');if(!app)return;
    if(!document.querySelector('.security-hero')){var hero=document.createElement('header');hero.className='security-hero';hero.innerHTML='<div class="security-hero-copy"><span class="security-hero-icon"><i class="fas fa-user-shield"></i></span><div><h1>Administración de seguridad</h1><p>Gestiona usuarios, perfiles y permisos de acceso desde un solo lugar.</p></div></div><span class="security-hero-badge"><i class="fas fa-shield-alt"></i> Acceso protegido</span>';var container=app.querySelector('.container');if(container)container.parentNode.insertBefore(hero,container);}
    Array.prototype.forEach.call(document.querySelectorAll('button'),function(btn){var text=(btn.textContent||'').trim().toLowerCase();if(text==='nuevo usuario'&&!btn.dataset.modernized){btn.dataset.modernized='1';btn.innerHTML='<i class="fas fa-user-plus"></i> Nuevo usuario';}});
    Array.prototype.forEach.call(document.querySelectorAll('table tbody tr'),function(row){var cells=row.querySelectorAll('td');if(cells.length<3)return;var user=(cells[2].textContent||'').trim().toLowerCase();if(user==='superadmin'&&!row.classList.contains('superadmin-row')){row.classList.add('superadmin-row');var tag=document.createElement('span');tag.className='superadmin-label';tag.innerHTML='<i class="fas fa-lock"></i> Protegido';cells[2].appendChild(tag);}});
  }
  var timer;function schedule(){clearTimeout(timer);timer=setTimeout(enhance,60)}window.addEventListener('hashchange',schedule);new MutationObserver(schedule).observe(document.documentElement,{childList:true,subtree:true});schedule();
})();
