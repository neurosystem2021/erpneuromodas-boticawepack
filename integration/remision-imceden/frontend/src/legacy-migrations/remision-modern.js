(function () {
  'use strict';

  function isRemision() { return window.location.hash.indexOf('#/remision') === 0 && window.location.hash.indexOf('lista') === -1 && window.location.hash.indexOf('listado') === -1; }
  function clean(text) { return String(text || '').replace(/\s+/g, ' ').trim(); }
  function normalize(text) { return clean(text).normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase(); }
  var ubigeoCatalogPromise = null;

  function flattenTree(tree) {
    var result = [];
    (tree || []).forEach(function (department) {
      (department.children || []).forEach(function (province) {
        (province.children || []).forEach(function (district) {
          result.push({ code: String(district.id), department: department.name, province: province.name, district: district.name });
        });
      });
    });
    return result;
  }

  function loadUbigeos(component) {
    var local = component && flattenTree(component.datos);
    if (local && local.length) return Promise.resolve(local);
    if (ubigeoCatalogPromise) return ubigeoCatalogPromise;
    ubigeoCatalogPromise = fetch('https://jmc-software-x.github.io/public-ubigeo-pe/data/ubigeo-inei.json', { headers: { Accept: 'application/json' } })
      .then(function (response) { if (!response.ok) throw new Error('HTTP ' + response.status); return response.json(); })
      .then(function (rows) {
        var departments = {}, provinces = {};
        rows.forEach(function (row) {
          var departmentKey = String(row.departamento).padStart(2, '0');
          var provinceKey = String(row.provincia).padStart(2, '0');
          var districtKey = String(row.distrito).padStart(2, '0');
          if (provinceKey === '00' && districtKey === '00' && !departments[departmentKey]) departments[departmentKey] = row.nombre;
          if (provinceKey !== '00' && districtKey === '00') provinces[departmentKey + provinceKey] = row.nombre;
        });
        return rows.filter(function (row) { return String(row.provincia).padStart(2, '0') !== '00' && String(row.distrito).padStart(2, '0') !== '00'; }).map(function (row) {
          var departmentKey = String(row.departamento).padStart(2, '0');
          var provinceKey = String(row.provincia).padStart(2, '0');
          var districtKey = String(row.distrito).padStart(2, '0');
          return { code: departmentKey + provinceKey + districtKey, department: departments[departmentKey] || departmentKey, province: provinces[departmentKey + provinceKey] || provinceKey, district: row.nombre };
        });
      });
    return ubigeoCatalogPromise;
  }

  function setupDynamicUbigeos(form) {
    form.querySelectorAll('label').forEach(function (label) {
      if (normalize(label.textContent).indexOf('ubigeo') !== 0) return;
      var row = label.closest('.row');
      if (!row || row.dataset.dynamicUbigeo === '1') return;
      var section = row.querySelector('section');
      var nativeInput = row.querySelector('input');
      if (!section && !nativeInput) return;
      row.dataset.dynamicUbigeo = '1';
      var component = section && section.__vue__;
      var host = section ? section.parentElement : nativeInput.parentElement;
      if (section) section.style.display = 'none';
      else nativeInput.style.display = 'none';

      var box = document.createElement('div');
      box.className = 'nm-ubigeo';
      box.innerHTML = '<div class="nm-ubigeo__control"><i class="fas fa-search"></i><input type="text" autocomplete="off" placeholder="Búsqueda rápida por nombre o código"><span class="nm-ubigeo__loader"></span></div><div class="nm-ubigeo__cascade"><label><span>Departamento</span><select data-level="department"><option value="">Seleccione</option></select></label><label><span>Provincia</span><select data-level="province" disabled><option value="">Seleccione</option></select></label><label><span>Distrito</span><select data-level="district" disabled><option value="">Seleccione</option></select></label></div><div class="nm-ubigeo__selection"><span><small>Departamento</small><b data-ubigeo="department">—</b></span><i class="fas fa-chevron-right"></i><span><small>Provincia</small><b data-ubigeo="province">—</b></span><i class="fas fa-chevron-right"></i><span><small>Distrito</small><b data-ubigeo="district">—</b></span></div><div class="nm-ubigeo__results" hidden></div><small>Selecciona los tres niveles o utiliza la búsqueda rápida.</small>';
      host.appendChild(box);
      var input = box.querySelector('input');
      var results = box.querySelector('.nm-ubigeo__results');
      var loader = box.querySelector('.nm-ubigeo__loader');
      var catalog = [], visible = [], active = -1;
      input.value = component && component.Ubigeo ? component.Ubigeo : (nativeInput && nativeInput.value) || '';
      var selection = box.querySelector('.nm-ubigeo__selection');
      var departmentSelect = box.querySelector('[data-level="department"]');
      var provinceSelect = box.querySelector('[data-level="province"]');
      var districtSelect = box.querySelector('[data-level="district"]');
      function showSelection(department, province, district) {
        selection.querySelector('[data-ubigeo="department"]').textContent = department || '—';
        selection.querySelector('[data-ubigeo="province"]').textContent = province || '—';
        selection.querySelector('[data-ubigeo="district"]').textContent = district || '—';
        selection.classList.toggle('is-complete', Boolean(department && province && district));
      }
      var initialParts = input.value.split(/\s*[›>]\s*/);
      if (initialParts.length > 1) showSelection(clean(initialParts[0]), clean(initialParts[1]), clean(initialParts[2]));
      loader.textContent = 'Cargando...';
      function uniqueValues(items, property) {
        return items.map(function (item) { return item[property]; }).filter(function (value, index, values) { return value && values.indexOf(value) === index; }).sort(function (a, b) { return a.localeCompare(b, 'es'); });
      }
      function fillSelect(select, values, placeholder) {
        select.innerHTML = '<option value="">' + placeholder + '</option>' + values.map(function (value) { return '<option value="' + value.replace(/&/g, '&amp;').replace(/"/g, '&quot;') + '">' + value + '</option>'; }).join('');
        select.disabled = !values.length;
      }
      function loadProvinces(department, selected) {
        var values = uniqueValues(catalog.filter(function (item) { return item.department === department; }), 'province');
        fillSelect(provinceSelect, values, 'Seleccione provincia');
        fillSelect(districtSelect, [], 'Seleccione distrito');
        if (selected && values.indexOf(selected) !== -1) provinceSelect.value = selected;
      }
      function loadDistricts(department, province, selected) {
        var values = uniqueValues(catalog.filter(function (item) { return item.department === department && item.province === province; }), 'district');
        fillSelect(districtSelect, values, 'Seleccione distrito');
        if (selected && values.indexOf(selected) !== -1) districtSelect.value = selected;
      }
      loadUbigeos(component).then(function (items) {
        catalog = items;
        loader.textContent = items.length + ' distritos';
        fillSelect(departmentSelect, uniqueValues(items, 'department'), 'Seleccione departamento');
        if (initialParts.length > 1 && uniqueValues(items, 'department').indexOf(clean(initialParts[0])) !== -1) {
          departmentSelect.value = clean(initialParts[0]);
          loadProvinces(departmentSelect.value, clean(initialParts[1]));
          if (provinceSelect.value) loadDistricts(departmentSelect.value, provinceSelect.value, clean(initialParts[2]));
        }
      }).catch(function () { loader.textContent = 'Sin catálogo'; });

      function choose(item) {
        input.value = item.department + ' › ' + item.province + ' › ' + item.district;
        departmentSelect.value = item.department;
        loadProvinces(item.department, item.province);
        loadDistricts(item.department, item.province, item.district);
        showSelection(item.department, item.province, item.district);
        results.hidden = true;
        if (component && typeof component.guardarIdUbigeo === 'function') component.guardarIdUbigeo(item.code, item.district, item.province, item.department);
        else if (nativeInput) {
          nativeInput.value = item.code;
          nativeInput.dispatchEvent(new Event('input', { bubbles: true }));
          nativeInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
        updateRequired(form);
      }

      departmentSelect.addEventListener('change', function () {
        loadProvinces(departmentSelect.value, '');
        showSelection(departmentSelect.value, '', '');
      });
      provinceSelect.addEventListener('change', function () {
        loadDistricts(departmentSelect.value, provinceSelect.value, '');
        showSelection(departmentSelect.value, provinceSelect.value, '');
      });
      districtSelect.addEventListener('change', function () {
        var item = catalog.find(function (row) { return row.department === departmentSelect.value && row.province === provinceSelect.value && row.district === districtSelect.value; });
        if (item) choose(item);
      });

      function render() {
        var query = normalize(input.value).replace(/[›>]/g, ' ');
        active = -1;
        if (query.length < 2) { results.hidden = true; return; }
        visible = catalog.filter(function (item) { return normalize(item.code + ' ' + item.department + ' ' + item.province + ' ' + item.district).indexOf(query) !== -1; }).slice(0, 12);
        results.innerHTML = visible.length ? visible.map(function (item, index) {
          return '<button type="button" data-index="' + index + '"><span><b>' + item.district + '</b><small>' + item.department + ' › ' + item.province + '</small></span><em>' + item.code + '</em></button>';
        }).join('') : '<p>No se encontraron coincidencias.</p>';
        results.hidden = false;
      }

      input.addEventListener('input', render);
      input.addEventListener('focus', function () { if (input.value.length >= 2) render(); });
      input.addEventListener('keydown', function (event) {
        var buttons = results.querySelectorAll('button');
        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
          event.preventDefault(); active += event.key === 'ArrowDown' ? 1 : -1; active = Math.max(0, Math.min(buttons.length - 1, active));
          buttons.forEach(function (button, index) { button.classList.toggle('active', index === active); });
          if (buttons[active]) buttons[active].scrollIntoView({ block: 'nearest' });
        } else if (event.key === 'Enter' && active >= 0 && visible[active]) { event.preventDefault(); choose(visible[active]); }
        else if (event.key === 'Escape') results.hidden = true;
      });
      results.addEventListener('click', function (event) { var button = event.target.closest('button'); if (button) choose(visible[Number(button.dataset.index)]); });
      document.addEventListener('click', function (event) { if (!box.contains(event.target)) results.hidden = true; });
    });
  }

  function setupRucSearch(form) {
    Array.prototype.slice.call(form.querySelectorAll('label')).forEach(function (label) {
      if (normalize(label.textContent).replace(/\*/g, '').trim() !== 'ruc') return;
      var row = label.closest('.row');
      var rucInput = row && row.querySelector('input');
      var section = row && row.closest('fieldset');
      if (!rucInput || !section || row.dataset.rucSearch === '1') return;
      row.dataset.rucSearch = '1';
      rucInput.setAttribute('inputmode', 'numeric');
      rucInput.setAttribute('maxlength', '11');
      rucInput.placeholder = 'Ingrese los 11 dígitos del RUC';

      var control = document.createElement('div');
      control.className = 'nm-ruc-search';
      rucInput.parentNode.insertBefore(control, rucInput);
      control.appendChild(rucInput);
      var button = document.createElement('button');
      button.type = 'button';
      button.className = 'nm-ruc-search__button';
      button.innerHTML = '<i class="fas fa-search"></i><span>Buscar RUC</span>';
      control.appendChild(button);
      var status = document.createElement('small');
      status.className = 'nm-ruc-search__status';
      status.textContent = 'Consulta automática como en punto de venta';
      control.appendChild(status);

      function findField(labelText) {
        var targetLabel = Array.prototype.slice.call(section.querySelectorAll('label')).find(function (item) {
          return normalize(item.textContent).replace(/\*/g, '').trim() === labelText;
        });
        var targetRow = targetLabel && targetLabel.closest('.row');
        return targetRow && targetRow.querySelector('input, textarea');
      }
      function setField(field, value) {
        if (!field) return;
        field.value = value || '';
        field.dispatchEvent(new Event('input', { bubbles: true }));
        field.dispatchEvent(new Event('change', { bubbles: true }));
      }
      function searchRuc() {
        var ruc = rucInput.value.replace(/\D/g, '');
        rucInput.value = ruc;
        rucInput.dispatchEvent(new Event('input', { bubbles: true }));
        if (ruc.length !== 11) {
          status.className = 'nm-ruc-search__status is-error';
          status.textContent = 'El RUC debe contener exactamente 11 dígitos.';
          rucInput.focus();
          return;
        }
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Consultando...</span>';
        status.className = 'nm-ruc-search__status';
        status.textContent = 'Consultando información oficial...';
        fetch('/api/index.php/consultarRUC?type=RUC&numero=' + encodeURIComponent(ruc), { headers: { Accept: 'application/json' } })
          .then(function (response) { if (!response.ok) throw new Error('HTTP ' + response.status); return response.json(); })
          .then(function (data) {
            if (!data || !data.razonSocial) throw new Error('RUC no encontrado');
            setField(findField('razon social'), data.razonSocial);
            setField(findField('direccion'), data.direccion);
            status.className = 'nm-ruc-search__status is-success';
            status.innerHTML = '<i class="fas fa-check-circle"></i> ' + clean(data.estado || 'RUC encontrado') + ' · ' + clean(data.condicion || 'Datos completados');
            updateRequired(form);
          })
          .catch(function () {
            status.className = 'nm-ruc-search__status is-error';
            status.textContent = 'No se encontró el RUC. Verifique el número e intente nuevamente.';
          })
          .then(function () {
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-search"></i><span>Buscar RUC</span>';
          });
      }
      button.addEventListener('click', searchRuc);
      rucInput.addEventListener('keydown', function (event) { if (event.key === 'Enter') { event.preventDefault(); searchRuc(); } });
    });
  }

  function updateRequired(form) {
    var required = Array.prototype.slice.call(form.querySelectorAll('[required]'));
    var pending = required.filter(function (field) { return !field.value || !field.checkValidity(); }).length;
    var completed = required.length - pending;
    required.forEach(function (field) {
      field.classList.toggle('nm-autofilled', Boolean(field.value && field.checkValidity()));
      field.classList.toggle('nm-pending', Boolean(!field.value || !field.checkValidity()));
    });
    var status = document.querySelector('.nm-guide-status strong');
    var detail = document.querySelector('.nm-guide-actionbar__info span');
    if (status) status.textContent = pending ? completed + ' autocompletados · ' + pending + ' pendientes' : 'Lista para guardar';
    if (detail) detail.textContent = pending ? 'Los campos verdes ya fueron completados; revisa solamente los pendientes' : 'Información obligatoria completa';
  }

  function enhance() {
    document.body.classList.toggle('nm-remision-page', isRemision());
    if (!isRemision()) {
      document.querySelectorAll('.nm-guide-actionbar').forEach(function (element) { element.remove(); });
      return;
    }
    var form = document.querySelector('form');
    if (!form || form.dataset.modernized === '1') return;
    form.dataset.modernized = '1';

    var hero = document.createElement('header');
    hero.className = 'nm-guide-hero';
    var fromDocument = window.location.hash.indexOf('/remision/ver/') !== -1;
    hero.innerHTML = '<div class="nm-guide-hero__title"><span class="nm-guide-hero__icon"><i class="fas fa-truck"></i></span><div><small>LOGÍSTICA / EMISIÓN ELECTRÓNICA</small><h1>' + (fromDocument ? 'Guía desde comprobante' : 'Nueva guía de remisión') + '</h1><p>' + (fromDocument ? 'Los datos del comprobante ya fueron cargados. Completa solo lo pendiente.' : 'Completa el traslado, destinatario, transporte y productos.') + '</p></div></div><div class="nm-guide-status"><span>ESTADO DEL FORMULARIO</span><strong>Revisando campos...</strong></div>';
    form.parentNode.insertBefore(hero, form);

    var fieldsets = Array.prototype.slice.call(form.querySelectorAll('fieldset.scheduler-border'));
    var steps = document.createElement('nav');
    steps.className = 'nm-guide-steps';
    fieldsets.forEach(function (fieldset, index) {
      var title = clean((fieldset.querySelector('legend') || {}).textContent) || 'Sección';
      fieldset.id = 'nm-guide-section-' + (index + 1);
      var button = document.createElement('button');
      button.type = 'button';
      button.className = 'nm-guide-step';
      button.innerHTML = '<b>' + (index + 1) + '</b><span>' + title + '</span>';
      button.addEventListener('click', function () { fieldset.scrollIntoView({ behavior: 'smooth', block: 'start' }); });
      steps.appendChild(button);
    });
    form.parentNode.insertBefore(steps, form);

    form.querySelectorAll('[required]').forEach(function (field) {
      var row = field.closest('.row');
      var label = row && row.querySelector('label');
      if (label) label.classList.add('nm-required');
    });

    var bar = document.createElement('div');
    bar.className = 'nm-guide-actionbar';
    bar.innerHTML = '<div class="nm-guide-actionbar__info"><strong><i class="fas fa-magic"></i> Autollenado activo</strong><span>Comprobando los datos importados...</span></div><button type="button" class="nm-guide-pending"><i class="fas fa-arrow-circle-right"></i> Ver pendiente</button><button type="button" class="nm-guide-save"><i class="fas fa-save"></i> Guardar guía</button>';
    bar.querySelector('.nm-guide-pending').addEventListener('click', function () {
      var pending = Array.prototype.slice.call(form.querySelectorAll('[required]')).find(function (field) { return !field.value || !field.checkValidity(); });
      if (pending) { pending.scrollIntoView({ behavior: 'smooth', block: 'center' }); setTimeout(function () { pending.focus(); }, 350); }
    });
    bar.querySelector('.nm-guide-save').addEventListener('click', function () {
      var nativeSubmit = form.querySelector('button[type="submit"], input[type="submit"]');
      if (nativeSubmit) nativeSubmit.click();
      else if (form.requestSubmit) form.requestSubmit();
    });
    document.body.appendChild(bar);

    form.addEventListener('input', function () { updateRequired(form); });
    form.addEventListener('change', function () { updateRequired(form); });
    setupDynamicUbigeos(form);
    setupRucSearch(form);
    updateRequired(form);
    var checks = 0;
    var autofillWatcher = window.setInterval(function () {
      if (!document.body.contains(form) || checks++ > 20) { window.clearInterval(autofillWatcher); return; }
      updateRequired(form);
    }, 500);
  }

  var queued = false;
  new MutationObserver(function () {
    if (queued) return;
    queued = true;
    requestAnimationFrame(function () { queued = false; enhance(); });
  }).observe(document.documentElement, { childList: true, subtree: true });
  window.addEventListener('hashchange', enhance);
  document.addEventListener('DOMContentLoaded', enhance);
  enhance();
})();
