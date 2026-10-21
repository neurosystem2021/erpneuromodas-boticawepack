(function () {
  'use strict';
  const logo = '/resources/images/wuanuko-mix-logo-transparente.png';
  const aliases = new Set([
    '/resources/images/banner-proforma.jpg', '/resources/images/logo.png',
    '/resources/images/logo-ticket.png', '/resources/images/wuanuko-mix-logo.png',
    '/resources/images/wuanuko-mix-logo.jpeg',
    '/nuevo/static/images/proforma-logo-color.png', '/nuevo/static/images/proforma-logo-color.webp',
    '/nuevo-imceden/static/images/proforma-logo-color.png', '/nuevo-imceden/static/images/proforma-logo-color.webp'
  ]);
  function update(node) {
    const images = node.matches && node.matches('img') ? [node] : node.querySelectorAll ? node.querySelectorAll('img') : [];
    for (const img of images) {
      let url; try { url = new URL(img.getAttribute('src') || '', location.href); } catch (_) { continue; }
      if (url.origin === location.origin && aliases.has(url.pathname)) img.src = logo;
    }
  }
  update(document);
  new MutationObserver(records => {
    for (const record of records) {
      if (record.type === 'attributes') update(record.target);
      else for (const node of record.addedNodes) update(node);
    }
  }).observe(document.documentElement, {subtree: true, childList: true, attributes: true, attributeFilter: ['src']});
}());
