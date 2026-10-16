from pathlib import Path
p=Path('src/views/Habitaciones.vue');s=p.read_text(encoding='utf-8')
for ref in ['cboClientePrincipal','cboEmpleado']:
    marker='ref="'+ref+'"'
    s=s.replace(marker,marker+'\n                :on-focus="() => listarProyectoCampo(\''+ref+'\')"\n                @mouseenter.native="listarProyectoCampo(\''+ref+'\')"\n                @click.native="abrirListaProyecto($event, \''+ref+'\')"',1)
s=s.replace('  methods: {','''  methods: {
    abrirListaProyecto(event, ref) {
      if (event.target && event.target.tagName === 'INPUT') this.listarProyectoCampo(ref);
    },
    listarProyectoCampo(ref) {
      const entry=this.$refs[ref];
      const field=Array.isArray(entry) ? entry[0] : entry;
      if (!field || field.showList) return;
      ['cboClientePrincipal','cboEmpleado'].filter(name => name !== ref).forEach(name => {
        const other=this.$refs[name];
        const component=Array.isArray(other) ? other[0] : other;
        if (component) component.showList=false;
      });
      field.handleDoubleClick();
    },''',1)
p.write_text(s,encoding='utf-8')
