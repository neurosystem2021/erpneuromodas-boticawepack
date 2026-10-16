from pathlib import Path
p=Path('src/components/ProductosCarritoNuevo.vue');s=p.read_text(encoding='utf-8')
s=s.replace("producto.Imagen == null || producto.Imagen == '' ? 'static/no-photo.png' : '/resources/images/productos/' + producto.Imagen",'productoImagenUrl(producto.Imagen)')
s=s.replace("productos[data.index].Imagen == null || productos[data.index].Imagen==''?'static/no-photo.png': '/resources/images/productos/' + productos[data.index].Imagen",'productoImagenUrl(productos[data.index].Imagen)')
a=s.index('        const imagen =',s.index('    renderChild(data)'));b=s.index('        const stock =',a);s=s[:a]+'''        const imagen = this.productoImagenUrl(data.Imagen).replace(/&/g, '&amp;').replace(/"/g, '&quot;');
'''+s[b:]
s=s.replace('src="${imagen}" alt="Imagen del producto"','src="${imagen}" onerror="this.onerror=null;this.src=\'/static/no-photo.png\'" alt="Imagen del producto"')
s=s.replace('    setAltImg(event) {\n      event.target.src = "static/no-photo.png";\n    },', '''    productoImagenUrl(value) {
      const image = String(value || '').trim().replace(/\\\\/g, '/');
      if (!image) return '/static/no-photo.png';
      if (/^https?:\\/\\//i.test(image)) return image;
      const relative = image.replace(/^\\/+/, '');
      const path = relative.indexOf('resources/images/') === 0 ? relative : 'resources/images/productos/' + relative;
      return '/' + path.split('/').map(part=>encodeURIComponent(decodeURIComponent(part))).join('/');
    },
    setAltImg(event) {
      if (!event.target.src.endsWith('/static/no-photo.png')) event.target.src = '/static/no-photo.png';
    },''')
p.write_text(s,encoding='utf-8')
