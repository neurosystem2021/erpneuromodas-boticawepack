from pathlib import Path
p=Path('src/views/reportes/ReporteProyectos.vue');s=p.read_text(encoding='utf-8');s=s.replace('          :per-page="perPage"','          no-provider-paging\n          :per-page="perPage"',1)
a=s.index('    this.axios.get(this.API + "proyectos/count")');b=s.index('    this.getAlmacenes();',a);s=s[:a]+s[b:]
a=s.index('    actualizarTabla() {');b=s.index('    getAlmacenes() {',a)
s=s[:a]+'''    actualizarTabla() {
      if (this.fechaIni && this.fechaFin && this.fechaIni > this.fechaFin) {
        this.$toasted.error('La fecha de inicio debe ser anterior a la fecha final.'); return;
      }
      this.currentPage = 1;
      this.$nextTick(() => { this.$refs.tablaVentas.refresh(); });
    },
    setAlmacen() { this.actualizarTabla(); },
    myProvider(ctx) {
      return this.axios.get(this.API + 'reporte/proyectos', {params:{
        FechaIni:this.fechaIni, FechaFin:this.fechaFin,
        idAlmacen:this.idAlmacen, filter:ctx.filter || '',
        sortBy:ctx.sortBy || 'IdPreOrden', sortDesc:ctx.sortDesc ? 1 : 0
      }}).then(result => {
        this.items = Array.isArray(result.data) ? result.data : [];
        this.totalRows = this.items.length;
        return this.items;
      }).catch(() => {
        this.totalRows = 0;
        this.$toasted.error('No se pudo cargar la búsqueda de proyectos. Intente nuevamente.');
        return [];
      });
    },
'''+s[b:]
p.write_text(s,encoding='utf-8')
