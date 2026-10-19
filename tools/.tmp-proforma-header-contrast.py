from pathlib import Path
p=Path('src/views/reportes/Proforma.vue');s=p.read_text(encoding='utf8');i=s.index('</style>',s.index('<style scoped>'));s=s[:i]+'\n.quote-products th.money{color:#fff!important}\n'+s[i:];p.write_text(s,encoding='utf8')
