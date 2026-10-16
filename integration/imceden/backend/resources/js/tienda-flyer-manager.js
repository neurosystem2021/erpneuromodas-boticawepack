(function(){
  'use strict';
  var api='/api/tienda-flyers.php';
  var manager=document.getElementById('flyerManager');
  var list=document.getElementById('flyerList');
  var message=document.getElementById('flyerMessage');
  var files=document.getElementById('flyerFiles');
  var items=[];

  function setMessage(text,error){
    message.textContent=text||'';
    message.className='flyer-message'+(error?' error':'');
  }
  function refreshStore(){
    var frame=document.getElementById('storeFrame');
    if(frame&&frame.contentWindow)frame.contentWindow.location.reload();
  }
  function request(body){
    return fetch(api,{method:'POST',body:body,credentials:'same-origin'}).then(function(response){
      return response.json().then(function(data){
        if(!response.ok||data.error)throw new Error(data.error||'No se pudo completar la operación.');
        return data;
      });
    });
  }
  function saveOrder(){
    var body=new FormData();
    body.append('action','order');
    body.append('names',JSON.stringify(items.map(function(item){return item.name;})));
    return request(body);
  }
  function render(){
    list.innerHTML='';
    if(!items.length){
      list.innerHTML='<div class="flyer-empty"><strong>Aún no hay flyers publicados.</strong><br>La tienda mostrará un encabezado automotriz hasta que subas la primera publicidad.</div>';
      return;
    }
    items.forEach(function(item,index){
      var card=document.createElement('article');
      card.className='flyer-item';
      var preview=document.createElement('div');
      preview.className='flyer-preview';
      var image=document.createElement('img');
      image.src=item.url;
      image.alt='Flyer '+(index+1);
      preview.appendChild(image);
      var footer=document.createElement('footer');
      var name=document.createElement('span');
      name.textContent=(index+1)+'. '+item.name;
      var controls=document.createElement('div');
      controls.className='flyer-controls';
      var up=document.createElement('button');
      up.type='button';up.textContent='↑';up.title='Subir';up.disabled=index===0;
      up.onclick=function(){
        var moved=items.splice(index,1)[0];items.splice(index-1,0,moved);render();
        saveOrder().then(refreshStore).catch(function(error){setMessage(error.message,true);});
      };
      var down=document.createElement('button');
      down.type='button';down.textContent='↓';down.title='Bajar';down.disabled=index===items.length-1;
      down.onclick=function(){
        var moved=items.splice(index,1)[0];items.splice(index+1,0,moved);render();
        saveOrder().then(refreshStore).catch(function(error){setMessage(error.message,true);});
      };
      var remove=document.createElement('button');
      remove.type='button';remove.textContent='×';remove.title='Eliminar';remove.className='delete';
      remove.onclick=function(){
        if(!confirm('¿Eliminar este flyer de la tienda?'))return;
        var body=new FormData();body.append('action','delete');body.append('name',item.name);
        request(body).then(function(data){
          items=data.items||[];render();refreshStore();setMessage('Flyer eliminado.');
        }).catch(function(error){setMessage(error.message,true);});
      };
      controls.appendChild(up);controls.appendChild(down);controls.appendChild(remove);
      footer.appendChild(name);footer.appendChild(controls);
      card.appendChild(preview);card.appendChild(footer);list.appendChild(card);
    });
  }
  function load(){
    setMessage('Cargando publicidad...');
    fetch(api,{cache:'no-store',credentials:'same-origin'}).then(function(response){return response.json();}).then(function(data){
      items=data.items||[];render();setMessage(items.length?items.length+' flyer(s) publicados.':'');
    }).catch(function(error){setMessage(error.message,true);});
  }

  document.getElementById('manageFlyers').onclick=function(){
    manager.classList.add('open');manager.setAttribute('aria-hidden','false');load();
  };
  document.getElementById('closeFlyers').onclick=function(){
    manager.classList.remove('open');manager.setAttribute('aria-hidden','true');
  };
  manager.onclick=function(event){
    if(event.target===manager)document.getElementById('closeFlyers').click();
  };
  document.getElementById('copyStoreLink').onclick=function(){
    var button=this;
    navigator.clipboard.writeText(location.origin+button.dataset.url).then(function(){button.textContent='Enlace copiado';});
  };
  files.onchange=function(){
    document.getElementById('flyerFileLabel').textContent=files.files.length?files.files.length+' archivo(s) seleccionados':'Seleccionar flyers en JPG, PNG, GIF o WEBP';
  };
  document.getElementById('flyerForm').onsubmit=function(event){
    event.preventDefault();
    if(!files.files.length){setMessage('Selecciona al menos una imagen.',true);return;}
    var body=new FormData(event.currentTarget);body.append('action','upload');setMessage('Publicando flyers...');
    request(body).then(function(data){
      items=data.items||[];files.value='';files.onchange();render();refreshStore();setMessage(data.saved+' flyer(s) publicados correctamente.');
    }).catch(function(error){setMessage(error.message,true);});
  };
}());
