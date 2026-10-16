<template>
<b-container fluid class="px-5 mb-5 nst-screen nst-screen--reporteregmovimiento">
  <section class="nst-panel nst-panel--filters"><b-row>
     <b-col cols="12">
      <b-breadcrumb :items="items"></b-breadcrumb>
     </b-col>
      <b-col cols="2">
          Fecha
          <input type="date" class="form-control" v-model="fechaInicio">
      </b-col>
      <b-col cols="2">
          Fecha
          <input type="date" class="form-control" v-model="fechaFin">
      </b-col>
      <b-col cols="2">
        <b-form-checkbox
          class="mt-3"
          id="checkbox-1"
          v-model="declarado"
          name="checkbox-1"
          value="true"
          unchecked-value="false"
        >
          Declarado
        </b-form-checkbox>

      </b-col>
      <b-col cols="2">
        Buscar detalle del movimiento (buscar serie)
          <input type="text" class="form-control" v-model="descripcion">
      </b-col>
      <b-col cols="4" class="pt-4">
          <b-button variant="info" @click="generarReporte()"><i class="fa fa-search" aria-hidden="true"></i> Generar</b-button>
          <b-button variant="success" :disabled="activarBotones" @click="exportarReporte()"><i class="fa fa-file-excel" aria-hidden="true"></i> Excel</b-button>
          <!-- <b-button variant="secondary" :disabled="activarBotones" @click="exportarXls()"><i class="fa fa-file" aria-hidden="true"></i> Text</b-button> -->
           <b-button variant="default" @click="exportarSunat(1)"><i class="fa fa-file" aria-hidden="true"></i> txt</b-button>
          <b-button variant="secondary" @click="exportarSunat(2)"><i class="fa fa-file-archive" aria-hidden="true"></i> zip</b-button>
          <b-button variant="outline-info" @click="abrirHistorial">HISTORIAL DE CAMBIOS</b-button>
      </b-col>
      <b-col cols="12" class="mt-4">
          <div class="nst-table-scroll" tabindex="0" role="region" aria-label="Tabla de resultados"><table ref="miTabla" class="table table-hover table-striped ">
              <thead>
                  <tr>
                      <th># Movimiento</th>
                      <th>Vendedor</th>
                      <th>Origen</th>
                      <th>Destino</th>
                      <th>FechaMov</th>
                      <th># Tipo</th>
                      <th>Tipo</th>
                      <th>Serie</th>
                      <th>Numero</th>
                      <th>Proveedor</th>
                      <th style="text-align: center;">SubTotal</th>	
                      <th style="text-align: center;">ISC</th>	
                      <th style="text-align: center;">IGV</th>	
                      <th style="text-align: center;">FLETE</th>	
                      <th style="text-align: center;">PERCEPCION</th>	
                      <th style="text-align: center;">MONEDA</th>	
                      <th style="text-align: center;">Total</th>
                      <th></th>
                  </tr>
              </thead>
              <tbody>
                  <tr v-for="(reporte,index) in lstReporte" :key="index" :class="{ 'movement-annulled': Number(reporte.Anulado) === 1 }" @click="abrirDedetalle(reporte)">
                      <td style="text-align: center;">{{reporte.IdMovimiento}} <strong v-if="Number(reporte.Anulado) === 1">ANULADO</strong></td>
                      <td>{{reporte.UsuarioReg}}</td>
                      <td>{{reporte.AlmacenOrigen}}</td>
                      <td>{{reporte.AlmacenDestino}}</td>
                      <td>{{reporte.MovimientoFecha}}</td>
                      <td>{{reporte.IdMovimientoTipo}}</td>
                      <td>{{reporte.TipoMovimiento}}</td>
                      <td>{{reporte.Serie}}</td>
                      <td>{{reporte.Numero}}</td>
                      <td>{{reporte.Proveedor}}</td>
                      <td style="text-align: center;">{{parseFloat(reporte.SUBTOTAL).toFixed(2)}}</td>
                      <td style="text-align: center;">{{parseFloat(reporte.ISC).toFixed(2)}}</td>
                      <td style="text-align: center;">{{parseFloat(reporte.IGV).toFixed(2)}}</td>
                      <td style="text-align: center;">{{parseFloat(reporte.FLETE).toFixed(2)}}</td>
                      <td style="text-align: center;">{{parseFloat(reporte.Percepcion).toFixed(2)}}</td>
                      <td style="text-align: center;">{{reporte.Moneda}}</td>
                      <td style="text-align: center;">{{parseFloat(reporte.TOTAL).toFixed(2)}}</td>
                      <td>
                        <b-button v-if="Number(reporte.Anulado) !== 1 && puedeEditar && tiposCorreccion.some(tipo => Number(tipo.IdMovimientoTipo) === Number(reporte.IdMovimientoTipo))" size="sm" class="mx-1 my-1" @click.stop="abrirCorreccion(reporte)" variant="warning" title="CORREGIR COMPROBANTE"><i class="fa fa-pencil-alt" aria-hidden="true"></i></b-button>
                        <b-button v-if="puedeEliminar && Number(reporte.Anulado) !== 1" size="sm" class="mx-1 my-1" @click.stop="elimimnarRegistro(reporte)" variant="danger" title="ANULAR CON MOTIVO"><i class="fa fa-trash" aria-hidden="true"></i></b-button>
                        <b-button size="sm" class="mx-1 my-1" @click.stop="imprimirMovimiento(reporte)" variant="info"><i class="fa fa-print" aria-hidden="true"></i></b-button>
                      </td>
                  </tr>
              </tbody>
          </table></div>
      </b-col>
      <b-col cols="3" offset="9">
          <b-row>
            <b-col cols="6"> <b>Sub Total(PEN)</b> <input type="number" class="form-control" v-model="SubTotalPEN" readonly name="" id=""></b-col>
            <b-col cols="6"> <b>Sub Total(USD)</b> <input type="number" class="form-control" v-model="SubTotalUSD" readonly name="" id=""></b-col>
            <b-col cols="6"> <b>IGV(PEN)</b> <input type="number" class="form-control" v-model="IgvlPEN" readonly name="" id=""></b-col>
            <b-col cols="6"> <b>IGV(USD)</b> <input type="number" class="form-control" v-model="IgvlUSD" readonly name="" id=""></b-col>
            <b-col cols="6"> <b>Total(PEN)</b> <input type="number" class="form-control" v-model="TotalPEN" readonly name="" id=""></b-col>
            <b-col cols="6"> <b>Total(USD)</b> <input type="number" class="form-control" v-model="TotalUSD" readonly name="" id=""></b-col>
          </b-row>
      </b-col>
  </b-row></section>
  <b-modal v-model="detalleMovimiento" no-close-on-backdrop no-close-on-esc hide-footer modal-class="nst-dialog" body-class="nst-dialog-body" footer-class="nst-dialog-footer" centered="">
      <b-row>
        <b-col cols="6">
          <b-input-group>
            <input type="text" class="form-control" v-model="FechaPeriodoTributario">
            <b-button v-if="puedeEditar" variant="warning" @click="actualizarPeriodo()">Actualizar Periodo</b-button>
          </b-input-group>
        </b-col>
        <b-col cols="6">
          Periodo Tributario:
          El valor debe ser AAAAMM
        </b-col>
        

        <b-col v-if="puedeEditar" cols="12"><b-form-textarea v-model="motivoPeriodo" placeholder="MOTIVO DE LA CORRECCIÓN DEL PERIODO (OBLIGATORIO)" rows="2" maxlength="1000" /></b-col>
        <b-col cols="12" class="mt-2">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>#</th>
                <th>Cod. Barra</th>
                <th>Marca</th>
                <th>Producto</th>
                <th>Descripcion</th>
                <th>Medida</th>
                <th>Cantidad</th>
                <th>Flete (S/.)</th>
                <th>Precio</th>
                <th>IGV</th>
                <th>Total</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(detalle,index) in lstDetalle" :key="index" >
                <td>{{ detalle.hashMovimiento }}</td>
                <td>{{ detalle.CodigoBarra }}</td>
                <td>{{ detalle.ProductoMarca }}</td>
                <td>{{ detalle.Producto }}</td>
                <td>{{ detalle.Descripcion }}</td>
                <td>{{ detalle.ProductoMedicion }}</td>
                <td>{{ detalle.Cantidad }}</td>
                <td>{{ detalle.FLETE }}</td>
                <td>{{ detalle.Precio }}</td>
                <td>{{ montoIGV(detalle)}}</td>
                <td>{{ montoTotal(detalle)}}</td>
              </tr>
            </tbody>
          </table>
        </b-col>
        <b-col cols="12" style="text-align: end;">
          <b-button variant="warning" @click="generarCodigoBarra()">Código de Barra</b-button>
          <b-button variant="danger" @click="detalleMovimiento=!detalleMovimiento">Cerrar</b-button>
        </b-col>
      </b-row>
  </b-modal>
  <b-modal v-model="controlModal" :title="controlAccion === 'EDITAR' ? 'CORREGIR COMPROBANTE · SOLO SUPERADMIN' : 'ANULAR MOVIMIENTO CON MOTIVO'" hide-footer :no-close-on-backdrop="controlBusy" :no-close-on-esc="controlBusy" :hide-header-close="controlBusy" modal-class="nst-dialog" centered>
    <b-form @submit.prevent="guardarControl">
      <p><strong>{{ controlRegistro.Proveedor }}</strong><br>{{ controlRegistro.TipoMovimiento }} {{ controlRegistro.Serie }}-{{ controlRegistro.Numero }}</p>
      <template v-if="controlAccion === 'EDITAR'">
        <b-form-group label="TIPO DE COMPROBANTE"><b-form-select v-model="correccion.tipo" :options="tiposCorreccion" value-field="IdMovimientoTipo" text-field="TipoMovimiento" required :disabled="controlBusy" /></b-form-group>
        <b-row><b-col cols="6"><b-form-group label="SERIE"><b-form-input v-model="correccion.serie" required maxlength="255" :disabled="controlBusy" @input="correccion.serie = correccion.serie.toUpperCase()" /></b-form-group></b-col><b-col cols="6"><b-form-group label="NÚMERO"><b-form-input v-model="correccion.numero" type="number" min="1" max="2147483647" step="1" required :disabled="controlBusy" /></b-form-group></b-col></b-row>
      </template>
      <b-alert v-else show variant="warning">EL MOVIMIENTO SE CONSERVARÁ COMO ANULADO EN ROJO. SE REGISTRARÁN EL USUARIO Y EL MOTIVO.</b-alert>
      <b-form-group label="MOTIVO OBLIGATORIO"><b-form-textarea v-model="correccion.motivo" required minlength="5" maxlength="1000" rows="3" :disabled="controlBusy" /></b-form-group>
      <b-alert :show="!!controlError" variant="danger">{{ controlError }}</b-alert>
      <b-button type="submit" :variant="controlAccion === 'EDITAR' ? 'primary' : 'danger'" :disabled="controlBusy">{{ controlBusy ? 'PROCESANDO...' : controlAccion === 'EDITAR' ? 'GUARDAR CORRECCIÓN' : 'ANULAR Y REGISTRAR MOTIVO' }}</b-button>
      <b-button :disabled="controlBusy" variant="outline-secondary" @click="controlModal = false">CANCELAR</b-button>
    </b-form>
  </b-modal>
  <b-modal v-model="historialModal" title="CONTROL DE CAMBIOS Y ELIMINACIONES" size="xl" hide-footer modal-class="nst-dialog" centered>
    <b-form @submit.prevent="cargarHistorial"><b-row>
      <b-col md="4"><b-form-group label="FECHA DEL CAMBIO · DESDE"><b-form-input v-model="historialDesde" type="date" required /></b-form-group></b-col>
      <b-col md="4"><b-form-group label="HASTA"><b-form-input v-model="historialHasta" type="date" required /></b-form-group></b-col>
      <b-col md="4"><b-button type="submit" variant="primary" :disabled="historialBusy">CONSULTAR</b-button> <b-button variant="success" :disabled="historialBusy || !auditoriaFiltrada.length" @click="exportarHistorial">EXPORTAR CSV</b-button></b-col>
    </b-row></b-form>
    <b-form-input v-model="historialFiltro" placeholder="BUSCAR USUARIO, PROVEEDOR, COMPROBANTE O MOTIVO" class="mb-3" />
    <b-alert :show="!!historialError" variant="danger">{{ historialError }}</b-alert>
    <b-alert :show="historialLimitado" variant="warning">SE MUESTRAN LOS ÚLTIMOS 5000 CAMBIOS. REDUCE EL RANGO DE FECHAS.</b-alert>
    <b-table small responsive :busy="historialBusy" :items="auditoriaFiltrada" :fields="historialFields" show-empty empty-text="NO HAY CAMBIOS REGISTRADOS EN ESTE PERIODO." />
    <small>EL HISTORIAL REGISTRA LAS OPERACIONES REALIZADAS DESDE LA ACTIVACIÓN DE ESTE CONTROL.</small>
  </b-modal>
</b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      puedeEditar: false, puedeEliminar: false, tiposCorreccion: [], motivoPeriodo: '',
      controlModal: false, controlBusy: false, controlError: '', controlAccion: 'EDITAR', controlRegistro: {}, correccion: { tipo: '', serie: '', numero: '', motivo: '' },
      historialModal: false, historialBusy: false, historialError: '', historialDesde: '', historialHasta: '', historialFiltro: '', historialLimitado: false, auditoria: [],
      historialFields: [{ key: 'Fecha', label: 'FECHA / HORA' }, { key: 'Usuario', label: 'USUARIO' }, { key: 'Accion', label: 'ACCIÓN' }, { key: 'HashMovimiento', label: 'MOVIMIENTO' }, { key: 'Proveedor', label: 'PROVEEDOR' }, { key: 'DocumentoAnterior', label: 'ANTES' }, { key: 'DocumentoNuevo', label: 'DESPUÉS' }, { key: 'Motivo', label: 'MOTIVO' }],
      detalleMovimiento: false,
      fechaInicio: "",
      fechaFin: "",
      descripcion: "",
      declarado: true,
      API: config.API,
      lstReporte: [],
      lstDetalle: [],
      items: [
        {
          text: "MOVIMIENTO",
          href: "#"
        },
        {
          text: "REPORTE",
          href: "#"
        },
        {
          text: "REGISTRO DE MOVIMIENTO",
          active: true
        }
      ],
      header: [
        { key: "IdProducto" },
        { key: "Producto" },
        { key: "Cantidad" },
        { key: "PrecioCosto" },
        { key: "PrecioVenta" },
        { key: "TotalVenta" },
        { key: "TotalCosto" },
        { key: "UtilidadBruta" },
        { key: "PorcentajeUti" }
      ],
      lstAlmances: [],
      SubTotalPEN: 0,
      SubTotalUSD: 0,
      IgvlPEN: 0,
      IgvlUSD: 0,
      TotalPEN: 0,
      TotalUSD: 0,
      FechaPeriodoTributario: "",
      IdMovimiento: ""
    };
  },
  mounted() {
    this.axios.get(this.API + 'movimiento/control/permisos').then(response => {
      this.puedeEditar = response.data.editar; this.puedeEliminar = response.data.eliminar; this.tiposCorreccion = response.data.tipos;
    }).catch(error => this.$toasted.error((error.response && error.response.data.error) || 'NO SE PUDIERON CONSULTAR LOS PERMISOS.'));
    this.fechaInicio = this.$moment()
      .startOf("month")
      .format("YYYY-MM-DD");
    this.fechaFin = this.$moment()
      .endOf("month")
      .format("YYYY-MM-DD");
    this.generarReporte();
    // this.obtenerAlmance();
  },
  methods: {
    abrirCorreccion(item) { this.prepararControl(item, 'EDITAR'); },
    prepararControl(item, accion) {
      this.controlRegistro = Object.assign({}, item); this.controlAccion = accion; this.controlError = '';
      this.correccion = { tipo: item.IdMovimientoTipo, serie: item.Serie || '', numero: item.Numero, motivo: '' }; this.controlModal = true;
    },
    async guardarControl() {
      if (this.controlBusy) return;
      this.controlError = '';
      if (this.correccion.motivo.trim().length < 5) { this.controlError = 'INGRESA EL MOTIVO (MÍNIMO 5 CARACTERES).'; return; }
      this.controlBusy = true;
      try {
        const path = this.controlAccion === 'EDITAR' ? 'movimiento/control/editar' : 'movimiento/eliminar';
        await this.axios.post(this.API + path, Object.assign({}, this.correccion, { idMov: this.controlRegistro.IdMovimiento }));
        this.controlModal = false; this.$toasted.success('OPERACIÓN REGISTRADA EN EL HISTORIAL.'); await this.generarReporte();
      } catch (error) { this.controlError = (error.response && error.response.data.error) || 'NO SE PUDO COMPLETAR LA OPERACIÓN.'; }
      finally { this.controlBusy = false; }
    },
    abrirHistorial() { this.historialDesde = this.fechaInicio; this.historialHasta = this.fechaFin; this.historialModal = true; this.cargarHistorial(); },
    async cargarHistorial() {
      this.historialBusy = true; this.historialError = ''; this.auditoria = [];
      try { const response = await this.axios.get(this.API + 'movimiento/control/historial', { params: { desde: this.historialDesde, hasta: this.historialHasta } }); this.auditoria = response.data.rows; this.historialLimitado = response.data.limitado; }
      catch (error) { this.historialError = (error.response && error.response.data.error) || 'NO SE PUDO CONSULTAR EL HISTORIAL.'; }
      finally { this.historialBusy = false; }
    },
    exportarHistorial() {
      const escape = value => '"' + String(value == null ? '' : value).replace(/^[=+@-]/, "'$&").replace(/"/g, '""') + '"';
      const lines = [this.historialFields.map(field => field.label)].concat(this.auditoriaFiltrada.map(row => this.historialFields.map(field => row[field.key])));
      const url = URL.createObjectURL(new Blob(['\ufeff' + lines.map(row => row.map(escape).join(';')).join('\r\n')], { type: 'text/csv;charset=utf-8' }));
      const anchor = document.createElement('a'); anchor.href = url; anchor.download = 'historial-compras-' + this.historialDesde + '.csv'; document.body.appendChild(anchor); anchor.click(); anchor.remove(); setTimeout(() => URL.revokeObjectURL(url), 1000);
    },
    async obtenerAlmance() {
      await this.axios(`${this.API}almacenes`).then(res => {
        if (res.data) {
          this.lstAlmances = res.data;
          this.lstAlmances.unshift({
            Almacen: "TODOS",
            IdAlmacen: 0
          });
        }
      });
    },
    imprimirMovimiento(reporte) {
      window.open(
        `/imprimir/index.php?hashMovimiento=${reporte.IdMovimiento}`,
        "_blank"
      );
    },
    generarCodigoBarra(reporte) {
      window.open(`/views/codigoBarra.php?hash=${this.IdMovimiento}`, "_blank");
    },
    async exportarXls() {
      const table = this.$refs.miTabla; // Obtener la referencia de la tabla
      let txt = "";

      // Recorrer las filas del tbody
      table.querySelectorAll("tbody tr").forEach(tr => {
        let rowData = [];
        tr.querySelectorAll("td").forEach(td => {
          rowData.push(td.textContent.trim()); // Obtener el texto y limpiar espacios
        });
        txt += rowData.join("|") + "\r\n"; // Separar con | y agregar salto de línea
      });

      // Crear un Blob con los datos en formato TXT
      const blob = new Blob([txt], { type: "text/plain" });

      // Crear un enlace para descargar el archivo
      const a = document.createElement("a");
      a.href = URL.createObjectURL(blob);
      a.download = "tabla_exportada.txt";
      a.click();

      // Liberar el objeto URL
      URL.revokeObjectURL(a.href);
    },
    async exportarReporte() {
      window.open(
        `/views/ReporteExcel6.php?&fechaIni=${this.fechaInicio}&fechaFin=${
          this.fechaFin
        }&descripcion=${this.descripcion}&declarado=${this.declarado}`,
        "_blank"
      );
    },

    elimimnarRegistro(item) { this.prepararControl(item, 'ELIMINAR'); },
    async generarReporte() {
      let params = {
        fechaInicio: this.fechaInicio,
        fechaFin: this.fechaFin,
        descripcion: this.descripcion,
        declarado: this.declarado
      };
      await this.axios(`${this.API}reporte/listarregmov`, {
        params: params
      }).then(res => {
        if (res.data) {
          let data = res.data;
          this.lstReporte = data;
          let SubTotalPEN = 0;
          let SubTotalUSD = 0;
          let IgvlPEN = 0;
          let IgvlUSD = 0;
          let TotalPEN = 0;
          let TotalUSD = 0;
          data.forEach(element => {
            if (Number(element.Anulado) === 1) return;
            if (element.Moneda == "PEN") {
              SubTotalPEN += element.SUBTOTAL;
              IgvlPEN += element.IGV;
              TotalPEN += element.TOTAL;
            }
            if (element.Moneda == "USD") {
              SubTotalUSD = element.SUBTOTAL;
              IgvlUSD = element.IGV;
              TotalUSD = element.TOTAL;
            }
          });
          this.SubTotalPEN = parseFloat(SubTotalPEN).toFixed(2);
          this.SubTotalUSD = parseFloat(SubTotalUSD).toFixed(2);
          this.IgvlPEN = parseFloat(IgvlPEN).toFixed(2);
          this.IgvlUSD = parseFloat(IgvlUSD).toFixed(2);
          this.TotalPEN = parseFloat(TotalPEN).toFixed(2);
          this.TotalUSD = parseFloat(TotalUSD).toFixed(2);
        }
      });
    },
    async exportarSunat(tipo) {
      const params = {
        fechaIni: this.fechaInicio,
        fechaFin: this.fechaFin,
        declarado: this.declarado,
        almacen: this.almacen
      };

      try {
        const res = await this.axios.get(`${this.API}exportar/compras/zip`, {
          params
        });
        console.log(res.data);

        let url = tipo == 1 ? res.data.txt : res.data.zip;

        if (!url) return alert("No se generó el archivo correctamente.");

        // limpiar barras invertidas
        url = url.replace(/\\/g, "");

        // asegurar protocolo HTTPS
        if (!/^https?:\/\//i.test(url)) {
          url = "https://" + url;
        }

        // descargar (abrir en nueva pestaña)
        window.open(url, "_blank");
      } catch (err) {
        console.error("Error exportando a SUNAT:", err);
        alert("Error al exportar. Revisa la consola para más detalles.");
      }
    },
    async abrirDedetalle(reporte) {
      console.log(reporte);
      this.motivoPeriodo = '';
      this.FechaPeriodoTributario = reporte.FechaPeriodoTributario;
      this.IdMovimiento = reporte.IdMovimiento;
      this.lstDetalle = [];
      let params = {
        IdMovimiento: reporte.IdMovimiento
      };
      await this.axios(`${this.API}movmiento/detalles`, {
        params: params
      }).then(res => {
        if (res.data) {
          this.lstDetalle = res.data;
        }
      });
      this.detalleMovimiento = true;
    },
    async actualizarPeriodo() {
      if (!this.puedeEditar) return;
      if (this.motivoPeriodo.trim().length < 5) { this.$toasted.error('INGRESA EL MOTIVO DE LA CORRECCIÓN (MÍNIMO 5 CARACTERES).'); return; }
      const regex = /^(20[0-9]{2})(0[1-9]|1[0-2])$/;
      const match = String(this.FechaPeriodoTributario).match(regex);

      if (!match) {
        this.$toasted.error(
          "El Periodo Tributario debe ser un año entre 2000 y 2099 con un mes válido (Ejemplo: 202403)."
        );
        return false;
      }
      let params = {
        FechaPeriodoTributario: this.FechaPeriodoTributario,
        IdMovimiento: this.IdMovimiento,
        motivo: this.motivoPeriodo
      };
      await this.axios
        .post(`${this.API}movmiento/actualizarperiodo`, params)
        .then(res => {
          if (res.data) {
            if (res.data.updated) {
              this.detalleMovimiento = false;
              this.$swal({
                icon: "success",
                title: "Actualización Correcta"
              });
            }
          }
        }).catch(error => this.$toasted.error((error.response && error.response.data.error) || 'NO SE PUDO ACTUALIZAR EL PERIODO.'));
      // this.detalleMovimiento = false;
    },
    montoIGV(item) {
      let monto = 0;
      if (item.TieneIgv == 1) {
        monto = item.Cantidad * item.Precio * 0.18;
      }
      return parseFloat(monto).toFixed(2);
    },
    montoTotal(item) {
      let igv = 0;
      let monto = 0;
      if (item.TieneIgv == 1) {
        igv = item.Cantidad * item.Precio * 0.18;
      }
      monto = item.Cantidad * item.Precio + igv;
      return parseFloat(monto).toFixed(2);
    }
  },
  computed: {
    auditoriaFiltrada() { const search = this.historialFiltro.trim().toLowerCase(); return this.auditoria.filter(row => !search || Object.keys(row).some(key => String(row[key] || '').toLowerCase().includes(search))); },
    activarBotones() {
      if (this.lstReporte.length == 0) {
        return true;
      }
      return false;
    }
  }
};
</script>

<style>
.nst-screen--reporteregmovimiento .movement-annulled td { background: #ffe4e6 !important; color: #b91c1c !important; }
.modal-dialog {
  max-width: 60% !important;
  width: 60% !important;
}
</style>
