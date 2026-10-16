from pathlib import Path
import re
p=Path('src/views/Ventas.vue');s=p.read_text(encoding='utf-8');a=s.index('<td><span v-if="ventas[index].MostrarGananciaTotal">');b=s.index('</span>',a)
s=s[:a]+'''<td><span v-if="ventas[index].MostrarGananciaTotal">{{ gananciaTotal(ventas[index].productos) }}</span>'''+s[b+len('</span>'):]
i=s.index('methods: {')+len('methods: {');s=s[:i]+'''
    gananciaTotal(productos) {
      return (productos || []).reduce((sum, prod) => sum + ((Number(prod.precio) || 0) - (Number(prod.PrecioCosto) || 0)) * (Number(prod.cantidad) || 0), 0).toFixed(2);
    },
'''+s[i:];p.write_text(s,encoding='utf-8')
