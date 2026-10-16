from pathlib import Path
p=Path('src/views/Habitaciones.vue');s=p.read_text(encoding='utf-8');s=s.replace('selectCliente: {},','selectCliente: {FechaAlquilerInicio:this.$moment().format("YYYY-MM-DD"),FechaAlquilerFin:this.$moment().format("YYYY-MM-DD")},',1)
s=s.replace('this.selectCliente = {};','this.selectCliente = {FechaAlquilerInicio:this.$moment().format("YYYY-MM-DD"),FechaAlquilerFin:this.$moment().format("YYYY-MM-DD")};')
s=s.replace('this.selectCliente = obj;','this.selectCliente = Object.assign({}, obj, {FechaAlquilerInicio:this.selectCliente.FechaAlquilerInicio || this.$moment().format("YYYY-MM-DD"),FechaAlquilerFin:this.selectCliente.FechaAlquilerFin || this.$moment().format("YYYY-MM-DD")});',1)
p.write_text(s,encoding='utf-8')
p=Path('src/components/ClientesForm.vue');s=p.read_text(encoding='utf-8').replace('modal-class="nst-dialog"','modal-class="nst-dialog nst-client-dialog"',1);p.write_text(s,encoding='utf-8')
