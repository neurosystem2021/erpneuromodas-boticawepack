from pathlib import Path
p=Path('src/views/Habitaciones.vue');s=p.read_text(encoding='utf-8');a=s.index('<clientes-form v-if="selectCliente.IdCliente"');b=s.index('</clientes-form>',a);part=s[a:b].replace('setCliente(obj);','setCliente(Object.assign({}, selectCliente, obj));');s=s[:a]+part+s[b:];p.write_text(s,encoding='utf-8')
