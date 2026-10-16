from pathlib import Path
p=Path('src/views/CajaBancos.vue');s=p.read_text(encoding='utf-8');s=s.replace('    abrirModalNuevaOperacion() {\n      this.modalRegistro = true;', '    abrirModalNuevaOperacion() {\n      this.errorMovimiento = "";\n      this.modalRegistro = true;',1)
s=s.replace("        await this.cargarOperaciones();\n        this.limpiarLuegoPago();\n        this.$toasted.success('Movimiento guardado correctamente.');", "        this.limpiarLuegoPago();\n        this.$toasted.success('Movimiento guardado correctamente.');\n        try { await this.cargarOperaciones(); } catch (refreshError) { this.$toasted.show('Movimiento guardado. Actualice la lista para visualizarlo.'); }",1)
p.write_text(s,encoding='utf-8')
