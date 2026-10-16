from pathlib import Path
import re
p=Path('src/views/reportes/Confrontacion.vue');s=p.read_text(encoding='utf-8')
s=re.sub(r'    <header class="inventory-app-header confront-hero">[\s\S]*?</header>\n','',s,count=1)
s=s.replace("this.API + 'productos', { params: { filter: this.query.trim().replace(/['\"\\\\]/g, ' '), limit: 25 } }", "this.API + 'productos/puntoventa/stock/' + this.warehouseId, { params: { q: this.query.trim().replace(/['\"\\\\]/g, ' '), limit: 25 } }")
s=s.replace("this.results = []; this.searching = false; if (this.query.trim().length", "this.results = []; this.searching = false; this.message = ''; this.error = ''; if (this.query.trim().length")
s=s.replace('this.results = Array.isArray(response.data) ? response.data : []; if (!this.results.length)',"this.results = (Array.isArray(response.data) ? response.data : []).slice(0, 25); this.message = ''; if (!this.results.length)")
s=s.replace('@input="scheduleSearch" autocomplete="off"','@input="scheduleSearch" @keydown.enter.prevent="scheduleSearch" autocomplete="off"')
p.write_text(s,encoding='utf-8')
assert "this.API + 'productos/puntoventa/stock/' + this.warehouseId" in s
print('Duplicate heading removed; POS search reused')
