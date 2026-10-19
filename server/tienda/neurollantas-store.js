(function () {
  'use strict';

  var STORE_NAME = 'IMCEDEN';
  var FALLBACK_LOGO = '/resources/images/imceden-store.svg?v=20261018-1';
  var SALES_TAGLINE = 'VENTA DE BATERIAS, AROS Y LLANTAS AL POR MAYOR Y MENOR';
  var storeCompany = null;
  var storeNavigationPromise = null;

  function assetUrl(value) {
    var path = String(value || '').trim().replace(/\\/g, '/');
    if (!path) return FALLBACK_LOGO;
    if (/^(?:https?:)?\/\//i.test(path) || /^data:/i.test(path)) return path;
    return '/' + path.replace(/^\/+/, '');
  }

  function replaceLegacyCopy() {
    var replacements = [
      ['Buscando prendas...', 'Buscando productos...'],
      ['No encontramos prendas con esos datos.', 'No encontramos productos con esos datos.'],
      ['Prenda disponible en nuestra tienda.', 'Producto disponible en nuestra tienda.'],
      ['Conoce esta prenda y consulta su disponibilidad.', 'Conoce este producto y consulta su disponibilidad.'],
      ['VENTA DE ROPA PARA NIÑOS', SALES_TAGLINE],
      ['Baterías, aros, llantas y repuestos para tu vehículo', SALES_TAGLINE]
    ];
    var walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
    var node;
    while ((node = walker.nextNode())) {
      replacements.forEach(function (pair) {
        if (node.nodeValue.indexOf(pair[0]) !== -1) node.nodeValue = node.nodeValue.split(pair[0]).join(pair[1]);
      });
    }
  }

  async function applyBrand() {
    var brandLogo = document.querySelector('.brand-logo');
    if (brandLogo) { brandLogo.src = FALLBACK_LOGO; brandLogo.alt = STORE_NAME; }
    var brandTitle = document.getElementById('brand');
    if (brandTitle) brandTitle.textContent = STORE_NAME;
    document.title = 'Tienda virtual | ' + STORE_NAME;
    try {
      var response = await fetch('/api/tienda/index.php?action=empresa', { cache: 'no-store' });
      if (!response.ok) throw new Error('No se pudo cargar la identidad de la empresa.');
      var company = await response.json();
      storeCompany = company;
      var logo = document.querySelector('.brand-logo');
      if (logo) {
        logo.src = FALLBACK_LOGO;
        logo.alt = STORE_NAME;
        logo.onerror = function () {
          this.onerror = null;
          this.src = FALLBACK_LOGO;
        };
      }
      var title = document.getElementById('brand');
      if (title) title.textContent = STORE_NAME;
      var tagline = document.getElementById('tagline');
      if (tagline) tagline.textContent = SALES_TAGLINE;
      document.title = 'Tienda virtual | ' + (STORE_NAME);
    } catch (error) {
      var fallback = document.querySelector('.brand-logo');
      if (fallback) fallback.src = FALLBACK_LOGO;
    }
    replaceLegacyCopy();
    var stableTagline = document.getElementById('tagline');
    if (stableTagline) stableTagline.textContent = SALES_TAGLINE;
  }

  var promoIndex = 0;
  var promoTimer = 0;

  function promoDefaultSlide() {
    var slide = document.createElement('article');
    slide.className = 'promo-slide is-active promo-default';
    slide.innerHTML = '<div class="promo-default-copy">' +
      '<span class="promo-kicker">Potencia para cada ruta</span>' +
      '<h2>Baterias, llantas y aros<br><strong>para seguir avanzando</strong></h2>' +
      '<p>Consulta nuestras promociones y equipa tu vehiculo con productos disponibles en tienda.</p>' +
      '<div class="promo-tags"><span>Baterias</span><span>Llantas</span><span>Aros</span><span>Repuestos</span></div>' +
      '</div><div class="promo-default-visual"><img src="/resources/images/tienda/producto-sin-imagen.svg?v=20261001-5" alt="Bateria, llanta y aro"></div>';
    return slide;
  }

  function showPromo(section, nextIndex) {
    var slides = Array.prototype.slice.call(section.querySelectorAll('.promo-slide'));
    var dots = Array.prototype.slice.call(section.querySelectorAll('.promo-dot'));
    if (!slides.length) return;
    promoIndex = (nextIndex + slides.length) % slides.length;
    slides.forEach(function (slide, index) {
      slide.classList.toggle('is-active', index === promoIndex);
      slide.setAttribute('aria-hidden', index === promoIndex ? 'false' : 'true');
    });
    dots.forEach(function (dot, index) {
      dot.classList.toggle('is-active', index === promoIndex);
      dot.setAttribute('aria-current', index === promoIndex ? 'true' : 'false');
    });
  }

  function startPromo(section) {
    clearInterval(promoTimer);
    if (section.querySelectorAll('.promo-slide').length < 2) return;
    promoTimer = setInterval(function () { showPromo(section, promoIndex + 1); }, 6000);
  }

  function loadStoreNavigationData() {
    if (!storeNavigationPromise) {
      storeNavigationPromise = fetch('/api/tienda/index.php?action=filtros', { cache: 'no-store' })
        .then(function (response) {
          if (!response.ok) throw new Error('No se pudieron cargar los filtros.');
          return response.json();
        })
        .catch(function (error) {
          storeNavigationPromise = null;
          throw error;
        });
    }
    return storeNavigationPromise;
  }

  function closeStoreNavigation(intro) {
    var panel = intro.querySelector('.store-nav-panel');
    if (panel) {
      panel.hidden = true;
      panel.innerHTML = '';
    }
    intro.querySelectorAll('.store-main-navigation a').forEach(function (link) {
      link.classList.remove('is-active');
      link.setAttribute('aria-expanded', 'false');
    });
  }

  function selectStoreFilter(type, item, intro) {
    var search = document.querySelector('.search-wrap input');
    if (type === 'categorias') {
      var categoryButton = document.querySelector('.cats button[data-id="' + String(item.id || 0).replace(/[^0-9]/g, '') + '"]');
      if (categoryButton) categoryButton.click();
      else if (search) {
        search.value = item.nombre || '';
        search.dispatchEvent(new Event('input', { bubbles: true }));
      }
    } else if (search) {
      search.value = item.nombre || '';
      search.dispatchEvent(new Event('input', { bubbles: true }));
    }
    closeStoreNavigation(intro);
    var tools = document.querySelector('.tools');
    if (tools) tools.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  function renderStoreFilterPanel(panel, intro, type, title, items) {
    panel.innerHTML = '';
    var head = document.createElement('div');
    head.className = 'store-nav-panel-head';
    var heading = document.createElement('strong');
    heading.textContent = title + ' (' + items.length + ')';
    var close = document.createElement('button');
    close.type = 'button';
    close.className = 'store-nav-close';
    close.setAttribute('aria-label', 'Cerrar menu');
    close.textContent = '\u00d7';
    close.onclick = function () { closeStoreNavigation(intro); };
    head.appendChild(heading);
    head.appendChild(close);

    var finder = document.createElement('input');
    finder.type = 'search';
    finder.className = 'store-nav-search';
    finder.placeholder = 'Buscar en ' + title.toLowerCase() + '...';
    finder.setAttribute('aria-label', finder.placeholder);

    var list = document.createElement('div');
    list.className = 'store-nav-options';
    var all = document.createElement('button');
    all.type = 'button';
    all.className = 'store-nav-option store-nav-all';
    all.textContent = 'Ver todos los productos';
    all.onclick = function () {
      var search = document.querySelector('.search-wrap input');
      if (search) search.value = '';
      var allCategories = document.querySelector('.cats button[data-id="0"]');
      if (allCategories) allCategories.click();
      else if (search) search.dispatchEvent(new Event('input', { bubbles: true }));
      closeStoreNavigation(intro);
    };
    list.appendChild(all);

    items.forEach(function (item) {
      var option = document.createElement('button');
      option.type = 'button';
      option.className = 'store-nav-option';
      option.dataset.filterText = String(item.nombre || '').toLowerCase();
      var name = document.createElement('span');
      name.textContent = item.nombre || 'Sin nombre';
      var count = document.createElement('small');
      count.textContent = String(item.cantidad || 0);
      option.appendChild(name);
      option.appendChild(count);
      option.onclick = function () { selectStoreFilter(type, item, intro); };
      list.appendChild(option);
    });
    finder.oninput = function () {
      var query = finder.value.trim().toLowerCase();
      list.querySelectorAll('.store-nav-option:not(.store-nav-all)').forEach(function (option) {
        option.hidden = query !== '' && option.dataset.filterText.indexOf(query) === -1;
      });
    };

    panel.appendChild(head);
    panel.appendChild(finder);
    panel.appendChild(list);
    window.setTimeout(function () { finder.focus(); }, 40);
  }

  function renderCompanyPanel(panel, intro) {
    var company = storeCompany || {};
    panel.innerHTML = '';
    var head = document.createElement('div');
    head.className = 'store-nav-panel-head';
    var heading = document.createElement('strong');
    heading.textContent = STORE_NAME;
    var close = document.createElement('button');
    close.type = 'button';
    close.className = 'store-nav-close';
    close.textContent = '\u00d7';
    close.onclick = function () { closeStoreNavigation(intro); };
    head.appendChild(heading);
    head.appendChild(close);
    var description = document.createElement('p');
    description.textContent = SALES_TAGLINE;
    var details = document.createElement('div');
    details.className = 'store-company-details';
    [company.DIRECCION, company.TELEFONO || company.CELULAR, company.EMAIL].filter(Boolean).forEach(function (value) {
      var item = document.createElement('span');
      item.textContent = value;
      details.appendChild(item);
    });
    panel.appendChild(head);
    panel.appendChild(description);
    panel.appendChild(details);
  }

  async function openStoreNavigation(label, link, intro) {
    var panel = intro.querySelector('.store-nav-panel');
    var isCurrent = link.classList.contains('is-active') && !panel.hidden;
    closeStoreNavigation(intro);
    if (isCurrent) return;
    link.classList.add('is-active');
    link.setAttribute('aria-expanded', 'true');
    panel.hidden = false;
    if (label === 'Nosotros') {
      renderCompanyPanel(panel, intro);
      return;
    }
    panel.innerHTML = '<div class="store-nav-loading">Cargando datos...</div>';
    try {
      var data = await loadStoreNavigationData();
      var type = label === 'Marcas' ? 'marcas' : (label === 'Modelos' ? 'modelos' : 'categorias');
      renderStoreFilterPanel(panel, intro, type, label, Array.isArray(data[type]) ? data[type] : []);
    } catch (error) {
      panel.innerHTML = '<div class="store-nav-loading">No se pudieron cargar los datos.</div>';
    }
  }

  function createStoreNavigation(pageHeader) {
    var intro = document.createElement('section');
    intro.className = 'store-promo-heading';
    intro.setAttribute('aria-label', 'Presentacion y navegacion de la tienda');

    var message = document.createElement('div');
    message.className = 'store-promo-message';
    message.innerHTML = '<span>IMCEDEN</span>';

    var navigation = document.createElement('nav');
    navigation.className = 'store-main-navigation';
    navigation.setAttribute('aria-label', 'Menu principal de la tienda');
    ['Nosotros', 'Marcas', 'Modelos', 'Categorias'].forEach(function (label, index) {
      var link = document.createElement('a');
      link.href = '#';
      link.textContent = label;
      link.setAttribute('aria-expanded', 'false');
      link.onclick = function (event) {
        event.preventDefault();
        openStoreNavigation(label, link, intro);
      };
      navigation.appendChild(link);
    });

    var panel = document.createElement('div');
    panel.className = 'store-nav-panel';
    panel.hidden = true;

    intro.appendChild(message);
    intro.appendChild(navigation);
    intro.appendChild(panel);
    return intro;
  }

  function mountFlyers(items) {
    if (document.querySelector('.promo-carousel') || document.querySelector('.store-promo-heading')) return;
    var pageHeader = document.querySelector('body > header');
    if (!pageHeader) return;
    pageHeader.id = pageHeader.id || 'nosotros';
    var tools = document.querySelector('.tools');
    if (tools) tools.id = tools.id || 'catalogo';
    var intro = createStoreNavigation(pageHeader);
    var section = document.createElement('section');
    section.className = 'promo-carousel';
    section.setAttribute('aria-label', 'Publicidad y promociones');
    var track = document.createElement('div');
    track.className = 'promo-track';

    if (items.length) {
      items.forEach(function (item, index) {
        var slide = document.createElement('article');
        slide.className = 'promo-slide' + (index === 0 ? ' is-active' : '');
        slide.setAttribute('aria-hidden', index === 0 ? 'false' : 'true');
        var backdrop = document.createElement('div');
        backdrop.className = 'promo-backdrop';
        backdrop.style.backgroundImage = 'url("' + item.url.replace(/"/g, '%22') + '")';
        var image = document.createElement('img');
        image.className = 'promo-image';
        image.src = item.url;
        image.alt = 'Publicidad ' + (index + 1) + ' de ' + STORE_NAME;
        image.loading = index === 0 ? 'eager' : 'lazy';
        image.onerror = function () { slide.remove(); };
        var badge = document.createElement('span');
        badge.className = 'promo-corner-label';
        badge.textContent = STORE_NAME;
        slide.appendChild(backdrop);
        slide.appendChild(image);
        slide.appendChild(badge);
        track.appendChild(slide);
      });
    } else {
      track.appendChild(promoDefaultSlide());
    }

    var previous = document.createElement('button');
    previous.type = 'button';
    previous.className = 'promo-arrow promo-prev';
    previous.setAttribute('aria-label', 'Publicidad anterior');
    previous.innerHTML = '&#8249;';
    var next = document.createElement('button');
    next.type = 'button';
    next.className = 'promo-arrow promo-next';
    next.setAttribute('aria-label', 'Siguiente publicidad');
    next.innerHTML = '&#8250;';
    var dots = document.createElement('div');
    dots.className = 'promo-dots';
    Array.prototype.slice.call(track.children).forEach(function (_, index) {
      var dot = document.createElement('button');
      dot.type = 'button';
      dot.className = 'promo-dot' + (index === 0 ? ' is-active' : '');
      dot.setAttribute('aria-label', 'Ver publicidad ' + (index + 1));
      dot.onclick = function () { showPromo(section, index); startPromo(section); };
      dots.appendChild(dot);
    });
    section.appendChild(track);
    section.appendChild(previous);
    section.appendChild(next);
    section.appendChild(dots);
    if (track.children.length < 2) section.classList.add('is-single');
    pageHeader.insertAdjacentElement('afterend', intro);
    intro.insertAdjacentElement('afterend', section);

    previous.onclick = function () { showPromo(section, promoIndex - 1); startPromo(section); };
    next.onclick = function () { showPromo(section, promoIndex + 1); startPromo(section); };
    section.addEventListener('mouseenter', function () { clearInterval(promoTimer); });
    section.addEventListener('mouseleave', function () { startPromo(section); });
    section.addEventListener('focusin', function () { clearInterval(promoTimer); });
    section.addEventListener('focusout', function () { startPromo(section); });
    startPromo(section);
  }

  async function initFlyerCarousel() {
    try {
      var response = await fetch('/api/tienda-flyers.php', { cache: 'no-store' });
      if (!response.ok) throw new Error('No se pudieron cargar los flyers.');
      var data = await response.json();
      mountFlyers(Array.isArray(data.items) ? data.items : []);
    } catch (error) {
      mountFlyers([]);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', applyBrand, { once: true });
    document.addEventListener('DOMContentLoaded', initFlyerCarousel, { once: true });
  } else {
    applyBrand();
    initFlyerCarousel();
  }

  var observer = new MutationObserver(replaceLegacyCopy);
  if (document.body) observer.observe(document.body, { childList: true, subtree: true });
}());
