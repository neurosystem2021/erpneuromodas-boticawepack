<template>
  <div>
    <b-card no-body>
      <b-tabs v-model="tabActive" pills card>

        <!-- HORAS PICO -->
        <b-tab title="Horas Pico" @click="cambioOpcion(0)" active>
          <div class="p-3 w-100 d-flex align-items-stretch" style="gap:20px;">

            <!-- Izquierda: filtros + heatmap -->
            <div class="flex-grow-1 d-flex flex-column" ref="heatmapContainer">

              <!-- Filtros -->
              <div class="mb-3">
                <b-row>
                  <b-col md="2">
                    <label>Almacén:</label>
                    <b-form-select v-model="idAlmacen" :options="almacenes"
                      @change="obtenerDatosHeatmap"></b-form-select>
                  </b-col>
                  <b-col md="2">
                    <label>Desde:</label>
                    <b-form-input v-model="fechaDesde" type="date" />
                  </b-col>
                  <b-col md="2">
                    <label>Hasta:</label>
                    <b-form-input v-model="fechaHasta" type="date" />
                  </b-col>
                  <b-col md="4" class="d-flex align-items-end">
                    <b-button variant="primary" @click="obtenerDatosHeatmap">
                      Mostrar Horas Pico
                    </b-button>
                  </b-col>
                </b-row>
              </div>

              <!-- Heatmap con scroll -->
              <div class="heatmap-scroll flex-grow-1" v-if="datosHeatmap.length">
                <div class="heatmap-container">

                  <!-- Encabezado -->
                  <div class="heatmap-header">
                    <div class="cell header" :style="{ width: 48 + 'px' }"></div>
                    <div class="cell header"
                      :style="{ width: horas.length * 48 + 'px', textAlign: 'center', fontWeight: 'bold', fontSize: '18px' }">
                      ⏰ Horarios
                    </div>
                    <div class="cell header" :style="{ width: 48 + 'px' }"></div>
                  </div>

                  <!-- Encabezado de horas -->
                  <div class="heatmap-header">
                    <div class="cell header">Fecha</div>
                    <div v-for="hora in horas" :key="'h-' + hora" class="cell header">
                      {{ hora }}H
                    </div>
                    <div class="cell header">Total</div>
                  </div>

                  <!-- Agrupación por semanas -->
                  <div v-for="(semana, index) in semanasAgrupadas" :key="'sem-' + index">
                    <div class="heatmap-row semana-rango">
                      <div class="semana-label text-center"
                        :style="{ width: (horas.length + 2) * 48 + 'px', cursor: 'pointer', color: '#007bff', fontWeight: '500' }"
                        @click="cargarDetalleSemana(semana.fechas, semana.rango)">
                        🗓️ {{ semana.rango }}
                      </div>
                    </div>

                    <div class="heatmap-row" v-for="fecha in semana.fechas" :key="fecha"
                      @click="seleccionarCelda(fecha)">
                      <div class="cell header">
                        <span class="contenido-dia-fecha">
                          <span>{{ formatearDia(fecha) }}</span><br>
                          <span>{{ $moment(fecha).format("DD/MM") }}</span>
                        </span>
                      </div>
                      <div v-for="hora in horas" :key="fecha + '-' + hora" class="cell"
                        :style="{ backgroundColor: getColorPorFechaHora(fecha, hora) }"
                        :data-tooltip="'Ventas: ' + getValorPorFechaHora(fecha, hora)"
                        @click.stop="seleccionarCeldaHora(fecha, hora)">
                        {{ getValorPorFechaHora(fecha, hora) || '' }}
                      </div>
                      <div class="cell header">{{ totalPorFecha(fecha) }}</div>
                    </div>
                  </div>

                  <!-- Totales por hora -->
                  <div class="heatmap-row">
                    <div class="cell header">Total</div>
                    <div v-for="hora in horas" :key="'tot-' + hora" class="cell header">
                      {{ totalPorHora(hora) }}
                    </div>
                    <div class="cell header total-gral">{{ totalGeneral() }}</div>
                  </div>
                </div>
              </div>

              <div v-else>
                <p class="text-center text-muted">Sin datos para mostrar</p>
              </div>
            </div>

            <!-- ═══════════════════════════════════════════════════════ -->
            <!-- PANEL DERECHO: DETALLE DE VENTAS (DISEÑO MEJORADO)     -->
            <!-- ═══════════════════════════════════════════════════════ -->
            <div class="detalle-panel">

              <!-- Header azul -->
              <div class="detalle-header">
                <div class="detalle-header-left">
                  <div class="detalle-header-icon">
                    <i class="fas fa-calendar-alt"></i>
                  </div>
                  <div>
                    <div class="detalle-header-title">Detalle de ventas</div>
                    <div class="detalle-header-sub" v-if="fechaSeleccionada">
                      {{ labelDetalle }}
                    </div>
                    <div class="detalle-header-sub" v-else>Selecciona un día u hora</div>
                  </div>
                </div>
                <div class="detalle-badge" v-if="detalleAgrupado.length">
                  {{ detalleAgrupado.length }} comprobante{{ detalleAgrupado.length !== 1 ? 's' : '' }}
                </div>
              </div>

              <!-- Tira de resumen -->
              <div class="detalle-summary" v-if="detalleAgrupado.length">
                <div class="summary-item">
                  <div class="summary-label">Total vendido</div>
                  <div class="summary-value">S/ {{ totalDetalleGeneral }}</div>
                </div>
                <div class="summary-item">
                  <div class="summary-label">Productos</div>
                  <div class="summary-value">{{ totalProductosDetalle }}</div>
                </div>
                <div class="summary-item">
                  <div class="summary-label">Clientes</div>
                  <div class="summary-value">{{ clientesUnicos }}</div>
                </div>
              </div>

              <!-- Estado vacío -->
              <div class="detalle-empty" v-if="!fechaSeleccionada">
                <i class="fas fa-mouse-pointer detalle-empty-icon"></i>
                <p>Haz clic en una fila o celda del heatmap para ver el detalle</p>
              </div>

              <div class="detalle-empty" v-else-if="!detalleAgrupado.length">
                <i class="fas fa-box-open detalle-empty-icon"></i>
                <p>Sin ventas para este período</p>
              </div>

              <!-- Lista de comprobantes -->
              <div class="detalle-lista" v-if="detalleAgrupado.length">
                <div class="venta-card" v-for="venta in detalleAgrupado" :key="venta.idDocVenta">

                  <!-- Cabecera de cada comprobante -->
                  <div class="venta-card-header">
                    <div class="venta-hora">
                      <i class="fas fa-clock" style="font-size:11px;margin-right:4px;"></i>
                      {{ venta.horasimple || venta.hora }}
                    </div>
                    <div class="venta-correlativo">{{ venta.correlativo }}</div>
                    <div class="venta-total-header">S/ {{ venta.totalVenta.toFixed(2) }}</div>
                  </div>

                  <!-- Cuerpo -->
                  <div class="venta-card-body">
                    <div class="venta-cliente">
                      <i class="fas fa-user" style="font-size:11px;margin-right:4px;"></i>
                      {{ venta.cliente }}
                    </div>

                    <div class="productos-lista">
                      <div class="producto-row" v-for="(producto, i) in venta.productos" :key="i">
                        <div class="producto-dot"></div>
                        <span class="producto-nombre">{{ producto.nombre }}</span>
                        <span class="producto-cant">{{ producto.cantidad }} × S/{{
                          parseFloat(producto.precio).toFixed(2) }}</span>
                        <span class="producto-sub">S/ {{ parseFloat(producto.subtotal).toFixed(2) }}</span>
                      </div>
                    </div>
                  </div>

                  <!-- Footer del comprobante -->
                  <div class="venta-card-footer">
                    <span class="venta-footer-label">Total comprobante</span>
                    <span class="venta-footer-total">S/ {{ venta.totalVenta.toFixed(2) }}</span>
                  </div>
                </div>
              </div>
            </div>
            <!-- FIN PANEL DERECHO -->

          </div>
        </b-tab>

        <!-- MÉTODO DE PAGO -->
        <!-- MÉTODO DE PAGO -->
        <b-tab title="Método de pago" @click="cambioOpcion(1)">
          <b-container fluid class="p-3">

            <!-- Filtros -->
            <b-row class="mb-3">
              <b-col md="2">
                <b-form-group label="Vendedor:">
                  <b-form-select v-model="idVendedor" :options="opcionesVendedores" size="md" />
                </b-form-group>
              </b-col>
              <b-col md="2">
                <b-form-group label="Tipo Documento:">
                  <b-form-select v-model="idTipoDoc" :options="opcionesTipoDoc" size="md" />
                </b-form-group>
              </b-col>
              <b-col md="2">
                <b-form-group label="Almacén:">
                  <b-form-select v-model="idAlmacen" :options="almacenes" @change="setAlmacen($event)" size="md" />
                </b-form-group>
              </b-col>
              <b-col md="2">
                <b-form-group label="Fecha Inicio:">
                  <b-form-input v-model="fechaDesde" type="date" />
                </b-form-group>
              </b-col>
              <b-col md="2">
                <b-form-group label="Fecha Fin:">
                  <b-form-input v-model="fechaHasta" type="date" />
                </b-form-group>
              </b-col>
              <b-col md="2" class="d-flex align-items-end">
                <b-button @click="fechaRango" variant="info" size="lg" block>
                  Generar
                </b-button>
              </b-col>
            </b-row>

            <!-- ═══ KPI CARDS ═══ -->
            <b-row class="mb-4" v-if="lstRango.length > 0">
              <b-col md="3">
                <div class="mp-kpi-card mp-kpi-total">
                  <div class="mp-kpi-icon">💰</div>
                  <div class="mp-kpi-info">
                    <div class="mp-kpi-label">Total General</div>
                    <div class="mp-kpi-value">S/ {{ totalRango }}</div>
                  </div>
                </div>
              </b-col>
              <b-col md="3">
                <div class="mp-kpi-card mp-kpi-efectivo">
                  <div class="mp-kpi-icon">💵</div>
                  <div class="mp-kpi-info">
                    <div class="mp-kpi-label">Efectivo</div>
                    <div class="mp-kpi-value">S/ {{ totalRangoEfectivo }}</div>
                    <div class="mp-kpi-pct">{{ pctMetodo(totalRangoEfectivo) }}% del total</div>
                  </div>
                </div>
              </b-col>
              <b-col md="3">
                <div class="mp-kpi-card mp-kpi-tarjeta">
                  <div class="mp-kpi-icon">💳</div>
                  <div class="mp-kpi-info">
                    <div class="mp-kpi-label">Tarjeta</div>
                    <div class="mp-kpi-value">S/ {{ totalRangoTarjeta }}</div>
                    <div class="mp-kpi-pct">{{ pctMetodo(totalRangoTarjeta) }}% del total</div>
                  </div>
                </div>
              </b-col>
              <b-col md="3">
                <div class="mp-kpi-card mp-kpi-digital">
                  <div class="mp-kpi-icon">📱</div>
                  <div class="mp-kpi-info">
                    <div class="mp-kpi-label">Yape + Transferencia</div>
                    <div class="mp-kpi-value">S/ {{ totalDigital }}</div>
                    <div class="mp-kpi-pct">{{ pctMetodo(totalDigital) }}% del total</div>
                  </div>
                </div>
              </b-col>
            </b-row>

            <!-- ═══ GRÁFICO + PANEL DERECHO ═══ -->
            <b-row>
              <b-col cols="9">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <h4 class="font-weight-bold mb-0">Valor de venta por rango</h4>
                  <div class="mp-periodo-badge" v-if="lstRango.length">
                    {{ diasRango }} días analizados · {{ docsUnicos }} comprobantes
                  </div>
                </div>
                <!-- Gráfico más grande -->
                <div style="height: 300px;">
                  <line-chart ref="chartGraficaRango"></line-chart>
                </div>

                <!-- ═══ TABLA RESUMEN POR DÍA ═══ -->
                <div class="mt-4" v-if="resumenPorDia.length">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="font-weight-bold mb-0">Resumen diario</h6>
                    <b-button size="sm" variant="outline-success" @click="exportarCSVMetodo">
                      ⬇ Exportar CSV
                    </b-button>
                  </div>
                  <div style="max-height:280px; overflow-y:auto;">
                    <table class="table table-sm table-bordered table-hover mb-0">
                      <thead class="thead-light text-center" style="position:sticky;top:0;z-index:1;">
                        <tr>
                          <th>Fecha</th>
                          <th>Efectivo</th>
                          <th>Tarjeta</th>
                          <th>Yape</th>
                          <th>Transferencia</th>
                          <th>Total Día</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="dia in resumenPorDia" :key="dia.fecha" class="text-center">
                          <td class="font-weight-bold">{{ dia.fecha }}</td>
                          <td>{{ dia.efectivo > 0 ? 'S/ ' + dia.efectivo.toFixed(2) : '—' }}</td>
                          <td>{{ dia.tarjeta > 0 ? 'S/ ' + dia.tarjeta.toFixed(2) : '—' }}</td>
                          <td>{{ dia.yape > 0 ? 'S/ ' + dia.yape.toFixed(2) : '—' }}</td>
                          <td>{{ dia.transferencia > 0 ? 'S/ ' + dia.transferencia.toFixed(2) : '—' }}</td>
                          <td class="font-weight-bold text-primary">S/ {{ dia.total.toFixed(2) }}</td>
                        </tr>
                      </tbody>
                      <tfoot class="font-weight-bold text-center" style="background:#f5f5f5;">
                        <tr>
                          <td>TOTAL</td>
                          <td>S/ {{ totalRangoEfectivo }}</td>
                          <td>S/ {{ totalRangoTarjeta }}</td>
                          <td>S/ {{ totalRangoYape }}</td>
                          <td>S/ {{ totalRangoTransferencia }}</td>
                          <td class="text-primary">S/ {{ totalRango }}</td>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>
              </b-col>

              <!-- Panel derecho -->
              <b-col cols="3">
                <div class="mp-panel-derecho">
                  <h6 class="font-weight-bold mb-3">Filtrar por Método de Pago:</h6>
                  <div v-for="metodo in metodosPagoUI" :key="metodo.nombre" class="mp-filtro-item"
                    :class="{ 'mp-filtro-inactivo': !filtrosMetodoPago[metodo.key] }"
                    @click="toggleMetodoPago(metodo.key)">
                    <div class="mp-filtro-dot" :style="{ backgroundColor: metodo.color }"></div>
                    <div class="mp-filtro-info">
                      <div class="mp-filtro-nombre">{{ metodo.nombre }}</div>
                      <div class="mp-filtro-monto">S/ {{ metodo.total }}</div>
                    </div>
                    <div class="mp-filtro-pct" :style="{ color: metodo.color }">
                      {{ pctMetodo(metodo.total) }}%
                    </div>
                  </div>

                  <div class="mp-total-filtrado mt-3">
                    <div class="mp-total-label">Total Filtrado</div>
                    <div class="mp-total-valor">S/ {{ totalRangoFiltrado }}</div>
                  </div>

                  <b-button variant="success" block class="mt-3" @click="exportarReporteMetodo">
                    <i class="fa fa-file-excel" aria-hidden="true"></i> Excel
                  </b-button>
                </div>
              </b-col>
            </b-row>

          </b-container>
        </b-tab>

        <!-- Ranking Vendedores -->
        <b-tab title="Ranking de Vendedores" @click="cambioOpcion(2)">
          <b-container fluid class="bv-example-row p-3" id="conten">
            <b-row class="mb-3">
              <b-col md="2">
                <b-form-group label="Tipo Documento:">
                  <b-form-select v-model="idTipoDoc" :options="opcionesTipoDoc" @change="obtenerRankingVendedores"
                    size="md" />
                </b-form-group>
              </b-col>
              <b-col md="2">
                <b-form-group label="Almacén:">
                  <b-form-select v-model="idAlmacen" :options="almacenes" @change="setAlmacen($event)" size="md" />
                </b-form-group>
              </b-col>
              <b-col md="2">
                <b-form-group label="Fecha Inicio:">
                  <b-form-input v-model="fechaDesde" type="date" />
                </b-form-group>
              </b-col>
              <b-col md="2">
                <b-form-group label="Fecha Fin:">
                  <b-form-input v-model="fechaHasta" type="date" />
                </b-form-group>
              </b-col>
              <b-col md="2" class="d-flex align-items-end">
                <b-button @click="obtenerRankingVendedores" variant="info" size="lg" style="margin-right: 15px;">
                  Generar
                </b-button>
                <b-button variant="success" @click="exportarReporteRanking">
                  <i class="fa fa-file-excel" aria-hidden="true"></i> Excel
                </b-button>
              </b-col>
            </b-row>
            <b-row>
              <b-col cols="5">
                <h3>Ranking de Venta por Vendedor</h3>
                <b-card class="mt-3">
                  <div ref="rankingChart" class="ranking-chart"></div>
                </b-card>
                <b-card class="mt-3">
                  <b-row>
                    <b-col>
                      <b-card bg="light" border="primary" class="text-center">
                        <h6 class="mb-1">Producto Más Vendido</h6>
                        <h5>{{ resumenVendedor.producto_mas_vendido || '—' }}</h5>
                      </b-card>
                    </b-col>
                    <b-col>
                      <b-card bg="light" border="success" class="text-center">
                        <h6 class="mb-1">Ganancia Bruta</h6>
                        <h5>S/ {{ resumenVendedor.ganancia_total.toFixed(2) }}</h5>
                      </b-card>
                    </b-col>
                  </b-row>
                  <b-row class="mt-2">
                    <b-col>
                      <b-card bg="light" border="info" class="text-center">
                        <h6 class="mb-1">% Utilidad Promedio</h6>
                        <h5>{{ resumenVendedor.utilidad_promedio.toFixed(2) }}%</h5>
                      </b-card>
                    </b-col>
                    <b-col>
                      <b-card bg="light" border="dark" class="text-center">
                        <h6 class="mb-1">Total Productos Vendidos</h6>
                        <h5>{{ resumenVendedor.total_productos_vendidos }}</h5>
                      </b-card>
                    </b-col>
                  </b-row>
                </b-card>
              </b-col>

              <b-col cols="7">
                <b-card v-if="ventasAgrupadasPorComprobante.length > 0">
                  <h5>Detalle de ventas: {{ vendedorSeleccionado }}</h5>
                  <div class="tabla-scroll">
                    <table class="table table-bordered table-sm">
                      <thead class="thead-light text-center">
                        <tr>
                          <th>Fecha</th>
                          <th>Comprobante</th>
                          <th>Producto</th>
                          <th>Cantidad</th>
                          <th>P. Unitario</th>
                          <th>Subtotal</th>
                          <th>P. Costo</th>
                          <th>Ganancia</th>
                          <th>% Utilidad</th>
                        </tr>
                      </thead>
                      <tbody>
                        <template v-for="(venta, index) in ventasAgrupadasPorComprobante">
                          <tr v-for="(prod, idx) in venta.productos" :key="index + '-' + idx">
                            <td v-if="idx === 0" :rowspan="venta.productos.length" class="align-middle">
                              {{ $moment(venta.fecha).format('YYYY-MM-DD HH:mm') }}
                            </td>
                            <td v-if="idx === 0" :rowspan="venta.productos.length" class="align-middle">
                              {{ venta.numero_doc }}
                            </td>
                            <td>{{ prod.nombre }}</td>
                            <td class="text-center">{{ prod.cantidad }}</td>
                            <td class="text-right">S/ {{ prod.precio.toFixed(2) }}</td>
                            <td class="text-right">S/ {{ prod.subtotal.toFixed(2) }}</td>
                            <td class="text-right">S/ {{ prod.precio_costo_unitario.toFixed(2) }}</td>
                            <td class="text-right">S/ {{ (prod.subtotal - prod.precio_costo_unitario).toFixed(2) }}</td>
                            <td class="text-right">% {{ prod.porcentajeUtilidad.toFixed(2) }}</td>
                          </tr>
                        </template>
                      </tbody>
                    </table>
                  </div>
                </b-card>
              </b-col>
            </b-row>
          </b-container>
        </b-tab>
      </b-tabs>
    </b-card>
  </div>
</template>

<script>
import BarChart from "@/components/BarChart";
import LineChart from "@/components/LineChart";
import axios from "axios";
import config from "@/store/config.js";

export default {
  name: "VentasPorHora",
  mounted() {
    this.initDia(0);
    this.getAlmacenes();
    this.obtenerVendedores();
    this.obtenerTiposDocumento();
    this.obtenerRankingVendedores();
  },
  beforeDestroy() {
    if (this.rankingChartInstance) {
      this.rankingChartInstance.dispose();
      this.rankingChartInstance = null;
    }
  },
  data() {
    return {
      API: config.API,
      meses: [
        { value: 1,  text: "Enero",      dias: 31 },
        { value: 2,  text: "Febrero",    dias: 29 },
        { value: 3,  text: "Marzo",      dias: 31 },
        { value: 4,  text: "Abril",      dias: 30 },
        { value: 5,  text: "Mayo",       dias: 31 },
        { value: 6,  text: "Junio",      dias: 30 },
        { value: 7,  text: "Julio",      dias: 31 },
        { value: 8,  text: "Agosto",     dias: 31 },
        { value: 9,  text: "Septiembre", dias: 30 },
        { value: 10, text: "Octubre",    dias: 31 },
        { value: 11, text: "Noviembre",  dias: 30 },
        { value: 12, text: "Diciembre",  dias: 31 }
      ],
      dias: ["Lun", "Mar", "Mié", "Jue", "Vie", "Sáb", "Dom"],
      horas: Array.from({ length: 24 }, (_, i) => i),
      datosHeatmap:      [],
      fechaSeleccionada: null,
      horaSeleccionada:  null,
      detalleAgrupado:   [],
      detalleVentas:     [],
      alturaHeatmap:     0,
      lstRango:                [],
      totalRango:              0,
      totalRangoFiltrado:      0,
      totalRangoEfectivo:      0,
      totalRangoTarjeta:       0,
      totalRangoYape:          0,
      totalRangoTransferencia: 0,
      filtrosMetodoPago: {
        efectivo: true, tarjeta: true, yape: true, transferencia: true
      },
      rankingData:                [],
      rankingChartInstance:       null,
      coloresVendedores:          {},
      vendedorSeleccionado:       "",
      ventasVendedorSeleccionado: [],
      resumenVendedor: {
        producto_mas_vendido: "", ganancia_total: 0,
        utilidad_promedio: 0, total_productos_vendidos: 0
      },
      updateFrequency: 2000,
      idTipoDoc:          "TODOS",
      opcionesTipoDoc:    [{ value: "TODOS", text: "TODOS" }],
      idVendedor:         "TODOS",
      opcionesVendedores: [],
      idAlmacen:          0,
      almacenes:          [{ value: 0, text: "TODOS" }],
      fechaDesde: this.$moment().format("YYYY-MM-01"),
      fechaHasta: this.$moment().format("YYYY-MM-DD"),
      anios: [], mesSelect: 1, tabActive: 0,
      anioSelect: Number(this.$moment().format("YYYY")),
      lstDia: [], totalDia: 0, lstMes: [], totalMes: 0
    };
  },

  // ════════════════════════════════════════════════════════
  // COMPUTED  ← todos aquí, ninguno en methods
  // ════════════════════════════════════════════════════════
  computed: {
    labelDetalle() {
      if (!this.fechaSeleccionada) return "";
      const fecha = this.$moment(this.fechaSeleccionada).format("DD/MM/YYYY");
      if (this.horaSeleccionada !== null) return `${fecha} · ${this.horaSeleccionada}H`;
      return fecha;
    },
    totalDetalleGeneral() {
      return this.detalleAgrupado.reduce((s, v) => s + v.totalVenta, 0).toFixed(2);
    },
    totalProductosDetalle() {
      return this.detalleAgrupado.reduce((s, v) => s + v.productos.length, 0);
    },
    clientesUnicos() {
      return new Set(this.detalleAgrupado.map(v => v.cliente)).size;
    },

    // ── Método de pago ────────────────────────────────────
    totalDigital() {
      return (
        parseFloat(this.totalRangoYape) +
        parseFloat(this.totalRangoTransferencia)
      ).toFixed(2);
    },
    diasRango() {
      const fechas = [...new Set(
        this.lstRango.map(i => `${i.Anio}-${i.Mes}-${i.Dia}`)
      )];
      return fechas.length;
    },
    docsUnicos() {
      return this.lstRango.length;
    },
    resumenPorDia() {
      if (!this.lstRango.length) return [];
      const mapa = {};
      this.lstRango.forEach(item => {
        const key = `${item.Dia}/${item.Mes}/${item.Anio}`;
        if (!mapa[key]) mapa[key] = {
          fecha: key, efectivo: 0, tarjeta: 0,
          yape: 0, transferencia: 0, total: 0
        };
        const val = parseFloat(item.Total) || 0;
        const id  = parseInt(item.IdMetodoPago);
        if      (id === 1) mapa[key].efectivo      += val;
        else if (id === 2) mapa[key].tarjeta        += val;
        else if (id === 3) mapa[key].yape           += val;
        else if (id === 4) mapa[key].transferencia  += val;
        mapa[key].total += val;
      });
      return Object.values(mapa).sort((a, b) =>
        new Date(a.fecha) - new Date(b.fecha)
      );
    },

    // ── Ranking ───────────────────────────────────────────
    ventasAgrupadasPorComprobante() {
      const agrupadas = {};
      this.ventasVendedorSeleccionado.forEach(venta => {
        const key = venta.numero_doc;
        if (!agrupadas[key]) {
          agrupadas[key] = {
            fecha: venta.fecha, numero_doc: venta.numero_doc, productos: []
          };
        }
        agrupadas[key].productos.push({
          nombre:                venta.producto,
          cantidad:              venta.cantidad,
          precio:                parseFloat(venta.precio_unitario),
          subtotal:              parseFloat(venta.subtotal),
          precio_costo_unitario: parseFloat(venta.precio_costo_unitario),
          porcentajeUtilidad:    parseFloat(venta.PorcentajeUtilidad)
        });
      });
      return Object.values(agrupadas);
    },

    // ── Heatmap ───────────────────────────────────────────
    semanasAgrupadas() {
      const agrupadas  = [];
      const fechas     = [...new Set(this.datosHeatmap.map(i => i.Fecha))].sort(
        (a, b) => new Date(a) - new Date(b)
      );
      let semanaActual = [];
      let inicioSemana = null;
      fechas.forEach(fecha => {
        const dia = this.$moment(fecha).day();
        if (dia === 1 || semanaActual.length === 0) {
          if (semanaActual.length) {
            agrupadas.push({
              rango: `${this.$moment(inicioSemana).format("DD/MM/YYYY")} - ${this.$moment(semanaActual[semanaActual.length - 1]).format("DD/MM/YYYY")}`,
              fechas: [...semanaActual]
            });
          }
          semanaActual = [fecha];
          inicioSemana = fecha;
        } else {
          semanaActual.push(fecha);
        }
      });
      if (semanaActual.length) {
        agrupadas.push({
          rango: `${this.$moment(inicioSemana).format("DD/MM/YYYY")} - ${this.$moment(semanaActual[semanaActual.length - 1]).format("DD/MM/YYYY")}`,
          fechas: [...semanaActual]
        });
      }
      return agrupadas;
    },

    metodosPagoUI() {
      return [
        { key: "efectivo",      nombre: "Efectivo",     color: "#36A2EB", total: this.totalRangoEfectivo },
        { key: "tarjeta",       nombre: "Tarjeta",       color: "#FF6384", total: this.totalRangoTarjeta },
        { key: "yape",          nombre: "Yape",          color: "#ffc107", total: this.totalRangoYape },
        { key: "transferencia", nombre: "Transferencia", color: "#17a2b8", total: this.totalRangoTransferencia }
      ];
    }
  },

  // ════════════════════════════════════════════════════════
  // METHODS  ← solo funciones, nunca propiedades calculadas
  // ════════════════════════════════════════════════════════
  methods: {
    // ── pctMetodo como MÉTODO (no computed) ───────────────
    pctMetodo(valor) {
      const total = parseFloat(this.totalRango);
      if (!total) return '0.00';
      return ((parseFloat(valor) / total) * 100).toFixed(1);
    },

    // ── Heatmap ───────────────────────────────────────────
    async obtenerDatosHeatmap() {
      try {
        const res = await this.axios.get(this.API + "ventas/estadistica/horaspico", {
          params: { fechaDesde: this.fechaDesde, fechaHasta: this.fechaHasta, idAlmacen: this.idAlmacen }
        });
        this.datosHeatmap = res.data;
      } catch (error) {
        console.error("Error al obtener heatmap:", error);
      }
      this.$nextTick(() => {
        const el = this.$refs.heatmapContainer;
        if (el) this.alturaHeatmap = el.offsetHeight;
      });
    },
    getValorPorFechaHora(fecha, hora) {
      const item = this.datosHeatmap.find(
        el => el.Fecha === fecha && parseInt(el.Hora) === hora
      );
      return item ? item.TotalVentas : "";
    },
    getColorPorFechaHora(fecha, hora) {
      const item = this.datosHeatmap.find(
        el => el.Fecha === fecha && parseInt(el.Hora) === hora
      );
      if (!item || !item.TotalVentas) return "#f0f0f0";
      const max = Math.max(...this.datosHeatmap.map(i => i.TotalVentas));
      const p   = item.TotalVentas / max;
      return `rgb(${Math.floor(230 - p*185)},${Math.floor(241 - p*140)},${Math.floor(251 - p*100)})`;
    },
    totalPorFecha(fecha) {
      return this.datosHeatmap
        .filter(d => d.Fecha === fecha)
        .reduce((s, d) => s + parseFloat(d.TotalVentas), 0).toFixed(0);
    },
    totalPorHora(hora) {
      return this.datosHeatmap
        .filter(d => parseInt(d.Hora) === hora)
        .reduce((s, d) => s + parseFloat(d.TotalVentas), 0).toFixed(0);
    },
    totalGeneral() {
      return this.datosHeatmap
        .reduce((s, d) => s + parseFloat(d.TotalVentas), 0).toFixed(0);
    },
    formatearDia(fecha) {
      return this.dias[(this.$moment(fecha).day() + 6) % 7];
    },
    seleccionarCelda(fecha) {
      this.fechaSeleccionada = this.$moment(fecha).format("YYYY-MM-DD");
      this.horaSeleccionada  = null;
      this.cargarDetalleDia();
    },
    seleccionarCeldaHora(fecha, hora) {
      this.fechaSeleccionada = this.$moment(fecha).format("YYYY-MM-DD");
      this.horaSeleccionada  = parseInt(hora);
      this.cargarDetalleHora();
    },
    async cargarDetalleDia() {
      if (!this.fechaSeleccionada) return;
      try {
        const res = await this.axios.get(this.API + "venta/detalle/dia", {
          params: { fecha: this.fechaSeleccionada, idAlmacen: this.idAlmacen }
        });
        const ag = {};
        for (const v of res.data) {
          if (!ag[v.idDocVenta]) ag[v.idDocVenta] = {
            idDocVenta: v.idDocVenta, hora: v.hora, horasimple: v.horasimple,
            correlativo: v.correlativo, cliente: v.cliente, productos: [], totalVenta: 0
          };
          ag[v.idDocVenta].productos.push({
            nombre: v.Producto, cantidad: v.cantidad,
            precio: v.precioUnitario, subtotal: parseFloat(v.montoTotal)
          });
          ag[v.idDocVenta].totalVenta += parseFloat(v.montoTotal);
        }
        this.detalleAgrupado = Object.values(ag);
      } catch (err) { this.detalleAgrupado = []; }
    },
    async cargarDetalleHora() {
      if (!this.fechaSeleccionada || this.horaSeleccionada === null) return;
      try {
        const res = await this.axios.get(this.API + "venta/detalle/dia", {
          params: { fecha: this.fechaSeleccionada, idAlmacen: this.idAlmacen }
        });
        const hf = String(this.horaSeleccionada).padStart(2, "0");
        const ag = {};
        for (const v of res.data.filter(v => String(v.hora).padStart(2,"0") === hf)) {
          if (!ag[v.idDocVenta]) ag[v.idDocVenta] = {
            idDocVenta: v.idDocVenta, hora: v.hora, horasimple: v.horasimple,
            correlativo: v.correlativo, cliente: v.cliente, productos: [], totalVenta: 0
          };
          ag[v.idDocVenta].productos.push({
            nombre: v.Producto, cantidad: v.cantidad,
            precio: v.precioUnitario, subtotal: parseFloat(v.montoTotal)
          });
          ag[v.idDocVenta].totalVenta += parseFloat(v.montoTotal);
        }
        this.detalleAgrupado = Object.values(ag);
      } catch (err) { this.detalleAgrupado = []; }
    },
    cargarDetalleSemana(fechas) {
      if (fechas && fechas.length) {
        this.fechaSeleccionada = fechas[0];
        this.horaSeleccionada  = null;
        this.cargarDetalleDia();
      }
    },

    // ── Init ──────────────────────────────────────────────
    initDia(idAlmacen) {
      const anioActual = Number(this.$moment().format("YYYY"));
      const mesActual  = Number(this.$moment().format("MM"));
      this.anios = [{ value: anioActual, text: "" + anioActual }];
      for (let i = 1; i <= 5; i++)
        this.anios.push({ value: anioActual - i, text: "" + (anioActual - i) });
      const mesd = this.meses.find(ms => ms.value === mesActual);
      if (mesd) {
        this.mesSelect = mesActual; this.anioSelect = anioActual;
        this.getDataDias(anioActual, mesActual, mesd.dias, idAlmacen);
      }
    },
    initMes(idAlmacen) {
      const anioActual = Number(this.$moment().format("YYYY"));
      this.anios = [{ value: anioActual, text: "" + anioActual }];
      for (let i = 1; i <= 5; i++)
        this.anios.push({ value: anioActual - i, text: "" + (anioActual - i) });
      this.anioSelect = anioActual;
      this.getDataMes(anioActual, idAlmacen);
    },
    setMes(e) {
      const mes = this.meses.find(ms => ms.value === e);
      if (mes) this.getDataDias(this.anioSelect, e, mes.dias, this.idAlmacen);
    },
    setAnio(e) {
      const mes = this.meses.find(ms => ms.value === this.mesSelect);
      if (mes) this.getDataDias(e, mes.value, mes.dias, this.idAlmacen);
    },
    setAnioMes(e) { this.getDataMes(e, this.idAlmacen); },
    setAlmacen(id) {
      if      (this.tabActive === 0) this.initDia(id);
      else if (this.tabActive === 1) this.initMes(id);
    },
    getDataDias(anio, mes, dias, idAlmacen) {
      this.axios.get(this.API + "ventas/estadistica/dia", {
        params: { anio, mes, idAlmacen: idAlmacen || this.idAlmacen }
      }).then(r => {
        this.lstDia   = r.data;
        this.totalDia = r.data.reduce((s, d) => s + parseFloat(d.Total), 0).toFixed(2);
        this.$nextTick(() => {
          if (this.$refs.chartGrafica)
            this.$refs.chartGrafica.GraficarDias(mes, dias, r.data);
        });
      }).catch(e => console.log(e));
    },
    getDataMes(anio, idAlmacen) {
      this.axios.get(this.API + "ventas/estadistica/mes", {
        params: { anio, idAlmacen: idAlmacen || this.idAlmacen }
      }).then(r => {
        this.lstMes   = r.data;
        this.totalMes = r.data.reduce((s, d) => s + parseFloat(d.Total), 0).toFixed(2);
        this.$nextTick(() => {
          if (this.$refs.chartGraficaMes)
            this.$refs.chartGraficaMes.GraficarMes(r.data, this.meses);
        });
      }).catch(e => console.log(e));
    },

    // ── Método de pago ────────────────────────────────────
    fechaRango() {
      if (!this.fechaDesde) return this.$toasted.show("Ingrese fecha Inicio");
      if (!this.fechaHasta) return this.$toasted.show("Ingrese fecha Fin");
      this.axios.get(this.API + "ventas/estadistica/metodopago", {
        params: {
          fechaDesde: this.fechaDesde, fechaHasta: this.fechaHasta,
          idAlmacen: this.idAlmacen, vendedor: this.idVendedor,
          tipoDoc: this.idTipoDoc !== "TODOS" ? this.idTipoDoc : undefined
        }
      }).then(r => {
        this.lstRango = r.data;
        this.calcularTotalesMetodo();
        this.calcularTotalRangoFiltrado();
        this.$nextTick(() => {
          if (this.$refs.chartGraficaRango)
            this.$refs.chartGraficaRango.GraficarRangoMetodo(r.data);
          this.filtrarGraficoPorMetodo();
        });
      }).catch(e => console.log(e));
    },
    calcularTotalesMetodo() {
      const mapa = { efectivo: 1, tarjeta: 2, yape: 3, transferencia: 4 };
      let total = 0;
      Object.entries(mapa).forEach(([key, id]) => {
        const sub = this.lstRango
          .filter(i => parseInt(i.IdMetodoPago) === id)
          .reduce((s, i) => s + parseFloat(i.Total), 0);
        this[`totalRango${key.charAt(0).toUpperCase() + key.slice(1)}`] = sub.toFixed(2);
        total += sub;
      });
      this.totalRango = total.toFixed(2);
    },
    calcularTotalRangoFiltrado() {
      const mapa    = { efectivo: 1, tarjeta: 2, yape: 3, transferencia: 4 };
      const activos = Object.entries(this.filtrosMetodoPago)
        .filter(([, v]) => v).map(([k]) => mapa[k]);
      this.totalRangoFiltrado = this.lstRango
        .filter(i => activos.includes(parseInt(i.IdMetodoPago)))
        .reduce((s, i) => s + parseFloat(i.Total), 0).toFixed(2);
    },
    toggleMetodoPago(metodo) {
      this.filtrosMetodoPago[metodo] = !this.filtrosMetodoPago[metodo];
      this.calcularTotalRangoFiltrado();
      this.filtrarGraficoPorMetodo();
    },
    filtrarGraficoPorMetodo() {
      const mapa    = { efectivo: 1, tarjeta: 2, yape: 3, transferencia: 4 };
      const activos = Object.entries(this.filtrosMetodoPago)
        .filter(([, v]) => v).map(([k]) => mapa[k]);
      const dataFiltrada = this.lstRango.filter(i =>
        activos.includes(parseInt(i.IdMetodoPago))
      );
      this.$nextTick(() => {
        if (this.$refs.chartGraficaRango)
          this.$refs.chartGraficaRango.GraficarRangoMetodo(dataFiltrada);
      });
    },
    exportarCSVMetodo() {
      if (!this.resumenPorDia.length) return;
      const headers = 'Fecha,Efectivo,Tarjeta,Yape,Transferencia,Total';
      const rows = this.resumenPorDia.map(d =>
        `${d.fecha},${d.efectivo.toFixed(2)},${d.tarjeta.toFixed(2)},${d.yape.toFixed(2)},${d.transferencia.toFixed(2)},${d.total.toFixed(2)}`
      ).join('\n');
      const blob = new Blob([`${headers}\n${rows}`], { type: 'text/csv;charset=utf-8;' });
      const url  = URL.createObjectURL(blob);
      const a    = document.createElement('a');
      a.href = url;
      a.download = `metodo_pago_${this.$moment().format('YYYYMMDD')}.csv`;
      a.click();
      URL.revokeObjectURL(url);
    },

    // ── Catálogos ─────────────────────────────────────────
    getAlmacenes() {
      this.almacenes = [{ value: 0, text: "TODOS" }];
      this.axios.get(this.API + "/almacenes").then(r => {
        if (r.data) r.data.forEach(al =>
          this.almacenes.push({ value: al.IdAlmacen, text: al.Almacen })
        );
      });
    },
    obtenerVendedores() {
      axios.get(`${this.API}/usuarios`, { params: { q: "" } }).then(res => {
        this.opcionesVendedores = [
          { value: "TODOS", text: "TODOS" },
          ...res.data.map(u => ({ value: u.Usuario, text: u.Usuario }))
        ];
      }).catch(e => console.error(e));
    },
    obtenerTiposDocumento() {
      axios.get(`${this.API}/ventas/tipos`, { params: { q: "" } }).then(res => {
        this.opcionesTipoDoc = [
          { value: "TODOS", text: "TODOS" },
          ...res.data.map(d => ({ value: d.IdTipoDoc, text: d.TipoDoc }))
        ];
      }).catch(e => console.error(e));
    },

    // ── Ranking ───────────────────────────────────────────
    async obtenerRankingVendedores() {
      try {
        const res = await this.axios.get(this.API + "ventas/estadistica/rankingvendedores", {
          params: {
            fechaDesde: this.fechaDesde, fechaHasta: this.fechaHasta,
            idAlmacen: this.idAlmacen,
            tipoDoc: this.idTipoDoc !== "TODOS" ? this.idTipoDoc : undefined
          }
        });
        this.rankingData = res.data || [];
        this.rankingData.forEach(d => {
          if (!this.coloresVendedores[d.Vendedor])
            this.coloresVendedores[d.Vendedor] = "#" +
              Math.floor(Math.random() * 16777215).toString(16).padStart(6, "0");
        });
        this.$nextTick(() => this.generarBarRace());
      } catch (e) { console.error(e); }
    },
    generarBarRace() {
      if (!this.$refs.rankingChart) return;
      if (this.rankingChartInstance) {
        this.rankingChartInstance.dispose();
        this.rankingChartInstance = null;
      }
      this.rankingChartInstance = this.$echarts.init(this.$refs.rankingChart);
      const data    = this.rankingData;
      const anios   = [...new Set(data.map(d => d.Anio))].sort();
      const dataset = anios.map(anio => {
        const d = data.filter(d => d.Anio === anio)
          .map(d => [parseFloat(d.Total), d.Vendedor.trim(), d.Vendedor.trim()]);
        d.sort((a, b) => b[0] - a[0]);
        return { anio, datos: d.slice(0, 10) };
      });
      if (!dataset.length) return;
      const option = {
        grid: { top: 10, bottom: 30, left: 100, right: 80 },
        xAxis: { max: "dataMax", axisLabel: { formatter: n => Math.round(n).toLocaleString() } },
        yAxis: { type: "category", inverse: true, axisLabel: { show: true, fontSize: 14 } },
        graphic: [{ type: "text", right: 60, bottom: 60, z: 100,
          style: { text: String(dataset[0].anio), font: "bold 80px sans-serif", fill: "rgba(100,100,100,0.25)" }
        }],
        series: [{
          realtimeSort: true, type: "bar",
          itemStyle: { color: p => this.coloresVendedores[p.value[2]] || "#5470c6" },
          encode: { x: 0, y: 1 },
          label: { show: true, position: "right",
            formatter: p => p.value[0].toFixed(2), fontWeight: "bold", valueAnimation: true }
        }],
        animationDurationUpdate: this.updateFrequency,
        dataset: { source: dataset[0].datos }
      };
      this.rankingChartInstance.setOption(option);
      this.rankingChartInstance.on("click", params => {
        if (params.componentType === "series")
          this.cargarDetalleVendedor(params.value[1]);
      });
      let i = 0;
      const update = () => {
        if (!this.rankingChartInstance || i >= dataset.length) return;
        this.rankingChartInstance.setOption({
          dataset: { source: dataset[i].datos },
          graphic:  [{ style: { text: String(dataset[i].anio) } }]
        });
        i++;
        if (i < dataset.length) setTimeout(update, this.updateFrequency);
      };
      setTimeout(update, this.updateFrequency);
    },
    async cargarDetalleVendedor(vendedor) {
      if (this.vendedorSeleccionado === vendedor &&
          this.ventasVendedorSeleccionado.length > 0) return;
      this.vendedorSeleccionado = vendedor;
      try {
        const res = await this.axios.get(this.API + "ventas/estadistica/detallevendedor", {
          params: {
            fechaDesde: this.fechaDesde, fechaHasta: this.fechaHasta,
            idAlmacen: this.idAlmacen, vendedor,
            tipoDoc: this.idTipoDoc !== "TODOS" ? this.idTipoDoc : undefined
          }
        });
        this.ventasVendedorSeleccionado = res.data.detalle || [];
        this.resumenVendedor = res.data.resumen || {
          producto_mas_vendido: "", ganancia_total: 0,
          utilidad_promedio: 0, total_productos_vendidos: 0
        };
      } catch (e) {
        console.error(e);
        this.ventasVendedorSeleccionado = [];
      }
    },
    async exportarReporteMetodo() {
      try {
        const res = await this.axios.get(`${this.API}reporte/ListarRegVenta/excelMetodo`, {
          params: { fechaIni: this.fechaDesde, fechaFin: this.fechaHasta, declarado: 1,
            anulado: 0, almacen: this.idAlmacen, vendedor: this.idVendedor, tipoDoc: this.idTipoDoc }
        });
        let url = res.data.replace(/\\/g, "");
        if (!url.startsWith("http")) url = "http://" + url;
        window.open(url, "_blank");
      } catch (e) { console.error(e); }
    },
    async exportarReporteRanking() {
      try {
        const res = await this.axios.get(`${this.API}reporte/ListarRegVenta/excelRanking`, {
          params: { fechaIni: this.fechaDesde, fechaFin: this.fechaHasta, declarado: 1,
            anulado: 0, almacen: this.idAlmacen, vendedor: this.vendedorSeleccionado }
        });
        let url = res.data.replace(/\\/g, "");
        if (!url.startsWith("http")) url = "http://" + url;
        window.open(url, "_blank");
      } catch (e) { console.error(e); }
    },
    cambioOpcion(e) {
      if (this.rankingChartInstance) {
        this.rankingChartInstance.dispose();
        this.rankingChartInstance = null;
      }
      if (e !== 0) {
        this.datosHeatmap = []; this.fechaSeleccionada = null; this.detalleAgrupado = [];
      }
      if (e !== 2) {
        this.vendedorSeleccionado = ""; this.ventasVendedorSeleccionado = [];
        this.resumenVendedor = { producto_mas_vendido: "", ganancia_total: 0,
          utilidad_promedio: 0, total_productos_vendidos: 0 };
      }
      this.idAlmacen = 0;
      if      (e === 0) this.initDia(0);
      else if (e === 1) {
        this.fechaDesde = this.$moment().format("YYYY-MM-01");
        this.fechaHasta = this.$moment().format("YYYY-MM-DD");
      }
      else if (e === 2) this.obtenerRankingVendedores();
    }
  },

  components: { BarChart, LineChart }
};
</script>

<style scoped>
/* ══════════════════════════════════════════════════════════
   HEATMAP
══════════════════════════════════════════════════════════ */
.heatmap-scroll {
  width: 100%;
  overflow-x: auto;
}

.heatmap-container {
  display: inline-block;
  background: #fff;
  padding: 20px;
  border-radius: 12px;
  border: 1px solid #ddd;
  box-sizing: border-box;
}

.cell {
  width: 48px;
  height: 55px;
  line-height: 45px;
  font-size: 14px;
  text-align: center;
  border: 1px solid #ddd;
  position: relative;
  flex-shrink: 0;
  transition: background-color 0.3s ease, transform 0.2s ease;
}

.cell:hover {
  transform: scale(1.05);
}

.heatmap-header,
.heatmap-row {
  display: flex;
}

.cell.header {
  background-color: #f5f5f5;
  font-weight: bold;
  position: sticky;
  left: 0;
  z-index: 2;
}

.contenido-dia-fecha {
  font-size: 13px;
  font-weight: bold;
  white-space: normal;
  text-align: center;
  line-height: 1.5;
  display: inline-block;
}

.cell.header.total-gral {
  background-color: #000;
  color: #fff;
}

.cell[data-tooltip]:hover::after {
  content: attr(data-tooltip);
  position: absolute;
  background-color: #333;
  color: #fff;
  font-size: 11px;
  padding: 4px 8px;
  border-radius: 4px;
  top: -35px;
  left: 50%;
  transform: translateX(-50%);
  white-space: nowrap;
  z-index: 3;
  opacity: 0.9;
}

.heatmap-row.semana-rango .cell {
  background-color: #e0e0e0;
  font-weight: bold;
  font-size: 14px;
  text-align: left;
  padding-left: 10px;
}

.semana-rango {
  display: flex;
  justify-content: center;
  margin: 6px 0 4px 0;
}

.semana-label {
  background-color: #f0f0f0;
  border-radius: 8px;
  font-size: 18px;
  padding: 4px 10px;
  text-align: center;
  font-weight: 500;
  color: #333;
  border: 1px solid #ccc;
}

/* ══════════════════════════════════════════════════════════
   PANEL DERECHO — DETALLE DE VENTAS
══════════════════════════════════════════════════════════ */
.detalle-panel {
  flex: 0 0 520px;
  display: flex;
  flex-direction: column;
  border: 1px solid #dde3ec;
  border-radius: 12px;
  overflow-y: auto;
  overflow-x: hidden;
  background: #fff;
  box-shadow: 0 2px 12px rgba(24, 95, 165, 0.07);
  max-height: 142vh;
  min-height: 200px;
}

/* Header azul */
.detalle-header {
  background: #185FA5;
  padding: 14px 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-shrink: 0;
}

.detalle-header-left {
  display: flex;
  align-items: center;
  gap: 10px;
}

.detalle-header-icon {
  width: 34px;
  height: 34px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.18);
  display: flex;
  align-items: center;
  justify-content: center;
}

.detalle-header-icon i {
  color: #fff;
  font-size: 16px;
}

.detalle-header-title {
  font-size: 14px;
  font-weight: 600;
  color: #fff;
}

.detalle-header-sub {
  font-size: 11px;
  color: rgba(255, 255, 255, 0.75);
  margin-top: 2px;
}

.detalle-badge {
  background: rgba(255, 255, 255, 0.2);
  color: #fff;
  font-size: 11px;
  font-weight: 500;
  padding: 3px 10px;
  border-radius: 20px;
  border: 1px solid rgba(255, 255, 255, 0.35);
  white-space: nowrap;
}

/* Tira de resumen */
.detalle-summary {
  display: flex;
  border-bottom: 1px solid #e8edf4;
  flex-shrink: 0;
}

.summary-item {
  flex: 1;
  padding: 10px 12px;
  text-align: center;
  border-right: 1px solid #e8edf4;
}

.summary-item:last-child {
  border-right: none;
}

.summary-label {
  font-size: 10px;
  color: #8a9ab5;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 2px;
}

.summary-value {
  font-size: 16px;
  font-weight: 600;
  color: #185FA5;
}

/* Estado vacío */
.detalle-empty {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 30px 20px;
  text-align: center;
  color: #aab4c8;
}

.detalle-empty-icon {
  font-size: 36px;
  margin-bottom: 10px;
  opacity: 0.5;
}

.detalle-empty p {
  font-size: 13px;
  line-height: 1.5;
}

/* Lista de comprobantes con scroll */
.detalle-lista {
  overflow-y: visible;
  padding: 12px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  background: #f5f7fb;
}

/* Tarjeta de cada comprobante */
.venta-card {
  background: #fff;
  border: 1px solid #dde3ec;
  border-radius: 10px;
  overflow: hidden;
}

.venta-card-header {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  background: #f0f4fa;
  border-bottom: 1px solid #dde3ec;
}

.venta-hora {
  font-size: 11px;
  font-weight: 600;
  color: #185FA5;
}

.venta-correlativo {
  font-size: 11px;
  font-weight: 500;
  background: #e0ebf8;
  color: #185FA5;
  padding: 2px 8px;
  border-radius: 20px;
  border: 1px solid #b5d4f4;
}

.venta-total-header {
  margin-left: auto;
  font-size: 13px;
  font-weight: 700;
  color: #185FA5;
}

.venta-card-body {
  padding: 10px 12px;
}

.venta-cliente {
  font-size: 11px;
  color: #6b7a99;
  margin-bottom: 8px;
  padding-bottom: 7px;
  border-bottom: 1px dashed #e0e6f0;
}

.productos-lista {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.producto-row {
  display: flex;
  align-items: flex-start;
  gap: 6px;
  font-size: 11px;
}

.producto-dot {
  width: 5px;
  height: 5px;
  border-radius: 50%;
  background: #85B7EB;
  margin-top: 4px;
  flex-shrink: 0;
}

.producto-nombre {
  flex: 1;
  color: #5a6a88;
  line-height: 1.4;
}

.producto-cant {
  color: #8a9ab5;
  white-space: nowrap;
  font-size: 10px;
}

.producto-sub {
  font-weight: 600;
  color: #185FA5;
  white-space: nowrap;
}

.venta-card-footer {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  padding: 7px 12px;
  background: #f0f4fa;
  border-top: 1px solid #dde3ec;
}

.venta-footer-label {
  font-size: 11px;
  color: #8a9ab5;
  margin-right: 8px;
}

.venta-footer-total {
  font-size: 14px;
  font-weight: 700;
  color: #185FA5;
}

/* ══════════════════════════════════════════════════════════
   RANKING / OTROS
══════════════════════════════════════════════════════════ */
.ranking-chart {
  width: 700px;
  height: 500px;
  max-width: 100%;
}

.tabla-scroll {
  max-height: 780px;
  overflow-y: auto;
  overflow-x: auto;
  border: 1px solid #ddd;
}

/* ══════════════════════════════════════════════════════════
   MÉTODO DE PAGO — KPI CARDS
══════════════════════════════════════════════════════════ */
.mp-kpi-card {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 16px 18px;
  border-radius: 12px;
  margin-bottom: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.mp-kpi-total       { background: linear-gradient(135deg, #185FA5, #2980d9); color: #fff; }
.mp-kpi-efectivo    { background: linear-gradient(135deg, #1a9e5c, #27ae60); color: #fff; }
.mp-kpi-tarjeta     { background: linear-gradient(135deg, #c0392b, #e74c3c); color: #fff; }
.mp-kpi-digital     { background: linear-gradient(135deg, #d4920a, #f39c12); color: #fff; }
.mp-kpi-icon        { font-size: 28px; }
.mp-kpi-label       { font-size: 11px; opacity: 0.85; text-transform: uppercase; letter-spacing: 0.04em; }
.mp-kpi-value       { font-size: 1.5rem; font-weight: 700; line-height: 1.2; }
.mp-kpi-pct         { font-size: 11px; opacity: 0.8; margin-top: 2px; }

/* Badge período */
.mp-periodo-badge {
  font-size: 12px;
  background: #e8f0fb;
  color: #185FA5;
  padding: 4px 12px;
  border-radius: 20px;
  font-weight: 500;
}

/* Panel derecho filtros */
.mp-panel-derecho {
  background: #fafbfd;
  border: 1px solid #dde3ec;
  border-radius: 12px;
  padding: 18px;
  height: 100%;
}
.mp-filtro-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  border-radius: 8px;
  margin-bottom: 8px;
  cursor: pointer;
  border: 1px solid #e0e8f4;
  background: #fff;
  transition: opacity 0.2s, transform 0.1s;
}
.mp-filtro-item:hover     { transform: translateX(2px); }
.mp-filtro-inactivo       { opacity: 0.35; }
.mp-filtro-dot {
  width: 14px; height: 14px;
  border-radius: 50%;
  flex-shrink: 0;
}
.mp-filtro-info  { flex: 1; }
.mp-filtro-nombre { font-size: 13px; font-weight: 600; color: #2c3e50; }
.mp-filtro-monto  { font-size: 12px; color: #7f8c9a; margin-top: 1px; }
.mp-filtro-pct    { font-size: 13px; font-weight: 700; }

.mp-total-filtrado {
  background: #e8f0fb;
  border-radius: 8px;
  padding: 12px 14px;
  text-align: center;
}
.mp-total-label { font-size: 11px; color: #7a8fa8; text-transform: uppercase; }
.mp-total-valor { font-size: 1.4rem; font-weight: 700; color: #185FA5; }

</style>