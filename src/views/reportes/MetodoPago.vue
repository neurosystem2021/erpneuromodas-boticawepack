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
                    <b-form-select
                      v-model="idAlmacen"
                      :options="almacenes"
                      @change="obtenerDatosHeatmap"
                    ></b-form-select>
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
                    <div
                      class="cell header"
                      :style="{ width: horas.length * 48 + 'px', textAlign: 'center', fontWeight: 'bold', fontSize: '18px' }"
                    >
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
                    
                    <!-- Rango de semana -->
                    <div class="heatmap-row semana-rango">
                      <div
                        class="semana-label text-center"
                        :style="{ width: (horas.length + 2) * 48 + 'px', cursor: 'pointer', color: '#007bff', fontWeight: '500' }"
                        @click="cargarDetalleSemana(semana.fechas, semana.rango)"
                      >
                        🗓️ {{ semana.rango }}
                      </div>
                    </div>

                    <!-- Días individuales -->
                    <div
                      class="heatmap-row"
                      v-for="fecha in semana.fechas"
                      :key="fecha"
                      @click="seleccionarCelda(fecha)"
                    >
                      <div class="cell header">
                        <span class="contenido-dia-fecha">
                          <span>{{ formatearDia(fecha) }}</span><br>
                          <span>{{ $moment(fecha).format("DD/MM") }}</span>
                        </span>
                      </div>
                      <div
                        v-for="hora in horas"
                        :key="fecha + '-' + hora"
                        class="cell"
                        :style="{ backgroundColor: getColorPorFechaHora(fecha, hora) }"
                        :data-tooltip="'Ventas: ' + getValorPorFechaHora(fecha, hora)"
                        @click.stop="seleccionarCeldaHora(fecha, hora)"
                      >
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

            <!-- Derecha: detalle del día -->
            <div
              class="detalle-dia"
              style="
                flex: 0 0 500px;
                overflow-y: auto;
                background: #fafafa;
                padding: 20px;
                border: 1px solid #ddd;
                border-radius: 8px;"
              :style="{ height: `${alturaHeatmap}px` }"
            >
              <h5 class="text-center" v-if="fechaSeleccionada">
                📅 Detalle de {{ fechaSeleccionada }}
              </h5>
              <p v-else class="text-muted">Haz clic en un día para ver detalles</p>

              <div v-if="detalleAgrupado.length">
                <div
                  v-for="venta in detalleAgrupado"
                  :key="venta.idDocVenta"
                  style="margin-bottom: 12px; border-bottom: 1px solid #ddd; padding-bottom: 8px;"
                >
                  <strong>{{ venta.hora }}:00h</strong> <br />
                  – Cliente: {{ venta.cliente }}<br />
                  <strong>Total:</strong> S/ {{ venta.totalVenta.toFixed(2) }}
                  <ul style="margin-top: 4px; padding-left: 15px;">
                    <li v-for="(producto, i) in venta.productos" :key="i">
                      {{ producto.nombre }} || {{ producto.cantidad }} × S/ {{ parseFloat(producto.precio).toFixed(2) }} =
                      <strong>S/ {{ parseFloat(producto.subtotal).toFixed(2) }}</strong>
                    </li>
                  </ul>
                </div>
              </div>

              <p v-else-if="fechaSeleccionada" class="text-center text-muted">
                Cargando o sin ventas para esta fecha.
              </p>
            </div>
          </div>
        </b-tab>
        <!-- MÉTODO DE PAGO -->
        <b-tab title="Método de pago" @click="cambioOpcion(1)">
          <b-container class="bv-example-row p-3" id="conten">
            <b-row>
              <b-col md="2">
                <b-form-group label="Vendedor:">
                  <b-form-select
                    v-model="idVendedor"
                    :options="opcionesVendedores"
                    class="mb-3"
                    size="md"
                  />
                </b-form-group>
              </b-col>
              <b-col md="2">
                <b-form-group label="Tipo Documento:">
                  <b-form-select
                    v-model="idTipoDoc"
                    :options="opcionesTipoDoc"
                    class="mb-3"
                    size="md"
                  />
                </b-form-group>
              </b-col>
              <b-col md="2">
                <b-form-group>
                  <label>Almacén:</label>
                  <b-form-select v-model="idAlmacen" :options="almacenes" @change="setAlmacen($event)" class="mb-3" size="md" />
                </b-form-group>
              </b-col>
              <b-col md="2">
                <b-form-group>
                  <label>Fecha Inicio:</label>
                  <b-form-input v-model="fechaDesde" type="date" placeholder="Fecha Inicio" />
                </b-form-group>
              </b-col>
              <b-col md="2">
                <b-form-group>
                  <label>Fecha Fin:</label>
                  <b-form-input v-model="fechaHasta" type="date" placeholder="Fecha Fin" />
                </b-form-group>
              </b-col>
              <b-col md="1" class="text-center">
                <b-form-group>
                  <br />
                  <b-button @click="fechaRango" variant="info" size="lg" style="margin-right: 15px;">Generar</b-button>
                </b-form-group>
              </b-col>
            </b-row>

            <b-row>
              <b-col cols="9">
                <h3>Valor de venta por rango</h3>
                <line-chart ref="chartGraficaRango"></line-chart>
              </b-col>

              <b-col cols="3">
                <div class="mb-3">
                  <h5>Filtrar por Método de Pago:</h5>
                  <div
                    v-for="metodo in metodosPagoUI"
                    :key="metodo.nombre"
                    class="d-flex align-items-center mb-2"
                    style="cursor: pointer;"
                    @click="toggleMetodoPago(metodo.key)"
                  >
                    <div
                      :style="{
                        width: '15px',
                        height: '15px',
                        borderRadius: '50%',
                        backgroundColor: metodo.color,
                        opacity: filtrosMetodoPago[metodo.key] ? 1 : 0.2,
                        marginRight: '10px',
                        border: '2px solid ' + metodo.color
                      }"
                    ></div>
                    <div class="flex-grow-1">
                      {{ metodo.nombre }} - <strong>S/ {{ metodo.total }}</strong>
                    </div>
                  </div>
                </div>

                <b-input-group prepend="Total Filtrado:" class="mb-2">
                  <input type="number" disabled :value="totalRangoFiltrado" class="form-control" />
                </b-input-group><br>
                <b-button  variant="success" @click="exportarReporteMetodo"><i class="fa fa-file-excel" aria-hidden="true"></i> Excel</b-button>

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
                  <b-form-select
                    v-model="idTipoDoc"
                    :options="opcionesTipoDoc"
                    @change="obtenerRankingVendedores"
                    size="md"
                  />
                </b-form-group>
              </b-col>
              <b-col md="2">
                <b-form-group label="Almacén:">
                  <b-form-select
                    v-model="idAlmacen"
                    :options="almacenes"
                    @change="setAlmacen($event)"
                    size="md"
                  />
                </b-form-group>
              </b-col>
              <b-col md="2">
                <b-form-group label="Fecha Inicio:">
                  <b-form-input
                    v-model="fechaDesde"
                    type="date"
                  />
                </b-form-group>
              </b-col>
              <b-col md="2">
                <b-form-group label="Fecha Fin:">
                  <b-form-input
                    v-model="fechaHasta"
                    type="date"
                  />
                </b-form-group>
              </b-col>
              <b-col md="2" class="d-flex align-items-end">
                <b-button
                  @click="obtenerRankingVendedores"
                  variant="info"
                  size="lg"
                  style="margin-right: 15px;"
                >
                  Generar
                </b-button>
                <b-button  variant="success" @click="exportarReporteRanking"><i class="fa fa-file-excel" aria-hidden="true"></i> Excel</b-button>
              </b-col>
            </b-row>
            <b-row>
              <b-col cols="5">
                <h3>Ranking de Venta por Vendedor</h3>
                <b-card class="mt-3" >
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

              <!-- PANEL LATERAL MEJORADO -->
        <b-col cols="7">
          <b-card v-if="ventasAgrupadasPorComprobante.length > 0">
            <h5>Detalle de ventas: {{ vendedorSeleccionado }}</h5>
            <div class="tabla-scroll"> <!-- Agregado -->
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
                    <td class="text-right">% {{ prod.porcentajeUtilidad }}</td>
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
    this.coloresVendedores = {};
    this.rankingData.forEach(d => {
      if (!this.coloresVendedores[d.Vendedor]) {
        this.coloresVendedores[d.Vendedor] =
          "#" + Math.floor(Math.random() * 16777215).toString(16);
      }
    });
  },
  beforeDestroy() {
    // 🔹 aquí limpias todo antes de que el componente se destruya
    if (this.chartGrafica) {
      this.chartGrafica.dispose();
      this.chartGrafica = null;
    }
    if (this.chartGraficaMes) {
      this.chartGraficaMes.dispose();
      this.chartGraficaMes = null;
    }
    if (this.chartGraficaRango) {
      this.chartGraficaRango.dispose();
      this.chartGraficaRango = null;
    }
    if (this.rankingChart) {
      this.rankingChart.dispose();
      this.rankingChart = null;
    }
  },
  data() {
    return {
      API: config.API,
      meses: [
        { value: 1, text: "Enero", dias: 31 },
        { value: 2, text: "Febrero", dias: 29 },
        { value: 3, text: "Marzo", dias: 31 },
        { value: 4, text: "Abril", dias: 30 },
        { value: 5, text: "Mayo", dias: 31 },
        { value: 6, text: "Junio", dias: 30 },
        { value: 7, text: "Julio", dias: 31 },
        { value: 8, text: "Agosto", dias: 31 },
        { value: 9, text: "Septiembre", dias: 30 },
        { value: 10, text: "Octubre", dias: 31 },
        { value: 11, text: "Noviembre", dias: 30 },
        { value: 12, text: "Diciembre", dias: 31 }
      ],
      chartData: {
        labels: [],
        datasets: []
      },
      metodosPagoDisplay: {
        efectivo: {
          nombre: "Efectivo",
          color: "#28a745", // Verde
          total: 0
        },
        tarjeta: {
          nombre: "Tarjeta",
          color: "#007bff", // Azul
          total: 0
        },
        yape: {
          nombre: "Yape",
          color: "#17a2b8", // Celeste
          total: 0
        },
        transferencia: {
          nombre: "Transferencia",
          color: "#ffc107", // Amarillo
          total: 0
        }
      },
      options: {
        /* opciones del gráfico */
      },
      filtrosMetodoPago: {
        efectivo: true,
        tarjeta: true,
        yape: true,
        transferencia: true
      },
      datosHeatmap: [],
      dias: ["Lun", "Mar", "Mié", "Jue", "Vie", "Sáb", "Dom"],
      horas: Array.from({ length: 24 }, (_, i) => i),
      ventasVendedorSeleccionado: [],
      vendedorSeleccionado: "",
      detalleVendedor: [],
      resumenVendedor: {
      producto_mas_vendido: '',
      ganancia_total: 0,
      utilidad_promedio: 0,
      total_productos_vendidos: 0
    },
      rankingData: [],
      rankingChart: null,
      chartGraficaRango: null,
      chartHeatmap: null,
      coloresVendedores: {},
      updateFrequency: 2000,
      idTipoDoc: "TODOS",
      opcionesTipoDoc: [{ value: "TODOS", text: "TODOS" }],
      fechaSeleccionada: null,
      horaSeleccionada: null,
      detalleVentas: [],
      ventasAgrupadasPorId: [],
      anios: [],
      detalleAgrupado: [],
      diaSeleccionado: null,
      detalleVentas: [],
      mesSelect: 1,
      tabActive: 0,
      alturaHeatmap: 0,
      anioSelect: Number(this.$moment().format("YYYY")),
      fechaDesde: this.$moment().format("YYYY-MM-01"),
      fechaHasta: this.$moment().format("YYYY-MM-DD"),
      idVendedor: "TODOS", // por defecto "TODOS"
      opcionesVendedores: [], // opciones para el select
      datosHeatmap: [],
      idAlmacen: 0,
      almacenes: [{ value: 0, text: "TODOS" }],
      lstDia: [],
      totalDia: 0,
      lstMes: [],
      totalMes: 0,
      totalRangoFiltrado: 0,
      lstRango: [],
      totalRango: 0,
      totalRangoEfectivo: 0,
      totalRangoTarjeta: 0,
      totalRangoYape: 0,
      totalRangoTransferencia: 0
    };
  },
  computed: {
    ventasAgrupadasPorComprobante() {
      const agrupadas = {};

      this.ventasVendedorSeleccionado.forEach(venta => {
        const key = venta.numero_doc;
        if (!agrupadas[key]) {
          agrupadas[key] = {
            fecha: venta.fecha,
            numero_doc: venta.numero_doc,
            productos: []
          };
        }

        agrupadas[key].productos.push({
          nombre: venta.producto,
          cantidad: venta.cantidad,
          precio: parseFloat(venta.precio_unitario),
          subtotal: parseFloat(venta.subtotal),
          precio_costo_unitario: parseFloat(venta.precio_costo_unitario),
          porcentajeUtilidad: parseFloat(venta.PorcentajeUtilidad),
        });
      });

      return Object.values(agrupadas);
    },
    maxVentas() {
      if (!this.datosHeatmap.length) return 0;
      return Math.max(...this.datosHeatmap.map(i => i.TotalVentas));
    },
    semanasAgrupadas() {
      const agrupadas = [];

      const fechas = [...new Set(this.datosHeatmap.map(i => i.Fecha))].sort(
        (a, b) => new Date(a) - new Date(b)
      );

      let semanaActual = [];
      let inicioSemana = null;

      fechas.forEach(fecha => {
        const dia = this.$moment(fecha).day(); // 0 domingo ... 6 sábado

        // Si es lunes, comienza una nueva semana
        if (dia === 1 || semanaActual.length === 0) {
          if (semanaActual.length) {
            agrupadas.push({
              rango: `${this.$moment(inicioSemana).format(
                "DD/MM/YYYY"
              )} - ${this.$moment(semanaActual[semanaActual.length - 1]).format(
                "DD/MM/YYYY"
              )}`,
              fechas: [...semanaActual]
            });
          }
          semanaActual = [fecha];
          inicioSemana = fecha;
        } else {
          semanaActual.push(fecha);
        }
      });

      // Última semana
      if (semanaActual.length) {
        agrupadas.push({
          rango: `${this.$moment(inicioSemana).format(
            "DD/MM/YYYY"
          )} - ${this.$moment(semanaActual[semanaActual.length - 1]).format(
            "DD/MM/YYYY"
          )}`,
          fechas: [...semanaActual]
        });
      }

      return agrupadas;
    },
    fechasUnicas() {
      const fechas = [...new Set(this.datosHeatmap.map(item => item.Fecha))];
      return fechas.sort((a, b) => new Date(a) - new Date(b));
    },
    diasUnicos() {
      return [...new Set(this.datosHeatmap.map(item => item.Fecha))];
    },
    metodosPagoUI() {
      return [
        {
          key: "efectivo",
          nombre: "Efectivo",
          color: "#36A2EB", // verde
          total: this.totalRangoEfectivo
        },
        {
          key: "tarjeta",
          nombre: "Tarjeta",
          color: "#FF6384", // azul
          total: this.totalRangoTarjeta
        },
        {
          key: "yape",
          nombre: "Yape",
          color: "#ffc107", // amarillo
          total: this.totalRangoYape
        },
        {
          key: "transferencia",
          nombre: "Transferencia",
          color: "#17a2b8", // celeste
          total: this.totalRangoTransferencia
        }
      ];
    }
  },
  methods: {
    seleccionarCeldaHora(fecha, hora) {
      this.fechaSeleccionada = this.$moment(fecha).format("YYYY-MM-DD");
      this.horaSeleccionada = parseInt(hora); // Guarda la hora seleccionada
      this.cargarDetalleHora();
    },
    async cargarDetalleHora() {
      if (!this.fechaSeleccionada || this.horaSeleccionada === null) return;

      try {
        const res = await this.axios.get(this.API + "venta/detalle/dia", {
          params: {
            fecha: this.fechaSeleccionada,
            idAlmacen: this.idAlmacen
          }
        });

        // Filtra por fecha y hora seleccionada
        const horaFormateada = String(this.horaSeleccionada).padStart(2, "0");
        const ventasFiltradas = res.data.filter(
          v =>
            this.$moment(v.fecha).format("YYYY-MM-DD") ===
              this.fechaSeleccionada &&
            String(v.hora).padStart(2, "0") === horaFormateada
        );

        // Agrupar por venta
        const agrupadas = {};
        for (const venta of ventasFiltradas) {
          const id = venta.idDocVenta;
          if (!agrupadas[id]) {
            agrupadas[id] = {
              idDocVenta: id,
              hora: venta.hora,
              cliente: venta.cliente,
              productos: [],
              totalVenta: 0
            };
          }

          agrupadas[id].productos.push({
            nombre: venta.Producto,
            cantidad: venta.cantidad,
            precio: venta.precioUnitario,
            subtotal: parseFloat(venta.montoTotal)
          });

          agrupadas[id].totalVenta += parseFloat(venta.montoTotal);
        }

        this.detalleAgrupado = Object.values(agrupadas);
      } catch (err) {
        this.detalleAgrupado = [];
      }
    },
    seleccionarCelda(fecha) {
      this.fechaSeleccionada = this.$moment(fecha).format("YYYY-MM-DD");
      this.cargarDetalleDia();
    },

    // Obtener el valor (ventas) por fecha y hora
    getValorPorFechaHora(fecha, hora) {
      const item = this.datosHeatmap.find(
        el => el.Fecha === fecha && parseInt(el.Hora) === hora
      );
      return item ? item.TotalVentas : "";
    },

    // Obtener el color del heatmap por intensidad
    getColorPorFechaHora(fecha, hora) {
      const item = this.datosHeatmap.find(
        el => el.Fecha === fecha && parseInt(el.Hora) === hora
      );
      if (!item || !item.TotalVentas) return "#f0f0f0";

      const max = Math.max(...this.datosHeatmap.map(i => i.TotalVentas));
      const porcentaje = item.TotalVentas / max;

      const rojo = Math.floor(255 - porcentaje * 255);
      const verde = 230;
      const azul = Math.floor(255 - porcentaje * 200);

      return `rgb(${rojo},${verde},${azul})`;
    },

    // Total de ventas por fecha (día completo)
    totalPorFecha(fecha) {
      return this.datosHeatmap
        .filter(d => d.Fecha === fecha)
        .reduce((sum, d) => sum + parseFloat(d.TotalVentas), 0)
        .toFixed(0);
    },
    // Cuando el usuario clickea una fila completa (día)
    seleccionarDia(diaAbreviado) {
      // Buscar la primera fecha que coincida con ese día abreviado
      const fechaEncontrada = this.datosHeatmap.find(
        item => this.formatearDia(item.Fecha) === diaAbreviado
      );

      if (fechaEncontrada) {
        this.fechaSeleccionada = this.$moment(fechaEncontrada.Fecha).format(
          "YYYY-MM-DD"
        );
        this.cargarDetalleDia();
      } else {
        this.fechaSeleccionada = null;
        this.detalleVentas = [];
      }
    },
    async cargarDetalleDia() {
      if (!this.fechaSeleccionada) return;

      try {
        const res = await this.axios.get(this.API + "venta/detalle/dia", {
          params: {
            fecha: this.fechaSeleccionada,
            idAlmacen: this.idAlmacen
          }
        });

        this.detalleVentas = res.data.filter(
          v =>
            this.$moment(v.fecha).format("YYYY-MM-DD") ===
            this.fechaSeleccionada
        );

        // Agrupar por venta
        const agrupadas = {};

        for (const venta of res.data) {
          const id = venta.idDocVenta;

          if (!agrupadas[id]) {
            agrupadas[id] = {
              idDocVenta: id,
              hora: venta.hora,
              cliente: venta.cliente,
              productos: [],
              totalVenta: 0
            };
          }

          agrupadas[id].productos.push({
            nombre: venta.Producto,
            cantidad: venta.cantidad,
            precio: venta.precioUnitario,
            subtotal: parseFloat(venta.montoTotal)
          });

          agrupadas[id].totalVenta += parseFloat(venta.montoTotal);
        }

        this.detalleAgrupado = Object.values(agrupadas);
      } catch (err) {
        this.detalleVentas = [];
        this.detalleAgrupado = [];
      }
    },
    async obtenerDatosHeatmap() {
      try {
        const res = await this.axios.get(
          this.API + "ventas/estadistica/horaspico",
          {
            params: {
              fechaDesde: this.fechaDesde,
              fechaHasta: this.fechaHasta,
              idAlmacen: this.idAlmacen
            }
          }
        );
        this.datosHeatmap = res.data;
      } catch (error) {
        console.error("Error al obtener heatmap:", error);
      }
      this.$nextTick(() => {
        const el = this.$refs.heatmapContainer;
        if (el) {
          this.alturaHeatmap = el.offsetHeight;
        }
      });
    },
    
    async obtenerRankingVendedores() {
      try {
        const res = await this.axios.get(
          this.API + "ventas/estadistica/rankingvendedores",
          {
            params: {
              fechaDesde: this.fechaDesde,
              fechaHasta: this.fechaHasta,
              idAlmacen: this.idAlmacen,
              tipoDoc: this.idTipoDoc !== "TODOS" ? this.idTipoDoc : undefined
            }
          }
        );
        this.rankingData = res.data || [];
        this.generarBarRace(); // Llama al gráfico después de cargar data
        
      } catch (error) {
        console.error("Error al obtener ranking:", error);
      }
    },

    getValor(dia, hora) {
      const item = this.datosHeatmap.find(
        el => this.formatearDia(el.Fecha) === dia && parseInt(el.Hora) === hora
      );
      return item ? item.TotalVentas : "";
    },
    getColor(dia, hora) {
      const item = this.datosHeatmap.find(
        el => this.formatearDia(el.Fecha) === dia && parseInt(el.Hora) === hora
      );
      if (!item || !item.TotalVentas) return "#f0f0f0";

      const max = Math.max(...this.datosHeatmap.map(i => i.TotalVentas));
      const porcentaje = item.TotalVentas / max;

      const rojo = 255;
      const verde = Math.floor(255 - porcentaje * 200);
      const azul = Math.floor(255 - porcentaje * 255);

      return `rgb(${rojo},${verde},${azul})`;
    },
    formatearDia(fecha) {
      const dia = this.$moment(fecha).day(); // 0 = domingo ... 6 = sábado
      return this.dias[(dia + 6) % 7]; // Ajustamos para que lunes sea 0
    },
    totalPorHora(hora) {
      return this.datosHeatmap
        .filter(d => parseInt(d.Hora) === hora)
        .reduce((sum, d) => sum + parseFloat(d.TotalVentas), 0)
        .toFixed(0);
    },
    totalPorDia(dia) {
      return this.datosHeatmap
        .filter(d => this.formatearDia(d.Fecha) === dia)
        .reduce((sum, d) => sum + parseFloat(d.TotalVentas), 0)
        .toFixed(2);
    },
    totalGeneral() {
      return this.datosHeatmap
        .reduce((sum, d) => sum + parseFloat(d.TotalVentas), 0)
        .toFixed(0);
    },
    initDia(idAlmacen) {
      let anioActual = Number(this.$moment().format("YYYY"));
      let mesActual = Number(this.$moment().format("MM"));
      this.anios = [];
      this.anios.push({ value: anioActual, text: "" + anioActual });
      for (let index = 1; index <= 5; index++) {
        this.anios.push({
          value: anioActual - index,
          text: "" + (anioActual - index)
        });
      }
      let mesd = this.meses.find(ms => ms.value == mesActual);
      if (mesd != undefined) {
        this.mesSelect = mesActual;
        this.anioSelect = anioActual;
        this.getDataDias(anioActual, mesActual, mesd.dias, idAlmacen);
      }
    },
    toggleMetodoPago(metodo) {
      this.filtrosMetodoPago[metodo] = !this.filtrosMetodoPago[metodo];
      this.calcularTotalRangoFiltrado();

      // Actualizar gráfico también
      this.filtrarGraficoPorMetodo();
    },
    initMes(idAlmacen) {
      let anioActual = Number(this.$moment().format("YYYY"));
      this.anios = [];
      this.anios.push({ value: anioActual, text: "" + anioActual });
      for (let index = 1; index <= 5; index++) {
        this.anios.push({
          value: anioActual - index,
          text: "" + (anioActual - index)
        });
      }
      this.anioSelect = anioActual;
      this.getDataMes(anioActual, idAlmacen);
    },
    setMes(e) {
      let mes = this.meses.find(ms => ms.value == e);
      if (mes != undefined) {
        this.getDataDias(this.anioSelect, e, mes.dias, this.idAlmacen);
      }
    },
    setAnio(e) {
      let mes = this.meses.find(ms => ms.value == this.mesSelect);
      if (mes != undefined) {
        this.getDataDias(e, mes.value, mes.dias, this.idAlmacen);
      }
    },
    setAnioMes(e) {
      this.getDataMes(e, this.idAlmacen);
    },
    setAlmacen(id) {
      switch (this.tabActive) {
        case 0:
          this.initDia(id);
          break;
        case 1:
          this.initMes(id);
          break;
        case 2:
          break;

        default:
          break;
      }
    },
    async exportarReporteMetodo() {
      const params = {
        fechaIni: this.fechaDesde,
        fechaFin: this.fechaHasta,
        declarado: 1,
        anulado: 0,
        almacen: this.idAlmacen,
        vendedor: this.idVendedor,
        tipoDoc: this.idTipoDoc
      };

      try {
        const res = await this.axios.get(
          `${this.API}reporte/ListarRegVenta/excelMetodo`,
          {
            params
          }
        );

        let url = res.data;
        url = url.replace(/\\/g, "");
        if (!url.startsWith("http://") && !url.startsWith("https://")) {
          url = "http://" + url;
        }

        window.open(url, "_blank");
      } catch (error) {
        console.error("Error al exportar Excel:", error);
        if (this.$toasted) {
          this.$toasted.show("Error al exportar Excel");
        }
      }
    },
    async exportarReporteRanking() {
      const params = {
        fechaIni: this.fechaDesde,
        fechaFin: this.fechaHasta,
        declarado: 1,
        anulado: 0,
        almacen: this.idAlmacen,
        vendedor: this.vendedorSeleccionado
      };

      try {
        const res = await this.axios.get(
          `${this.API}reporte/ListarRegVenta/excelRanking`,
          {
            params
          }
        );

        let url = res.data;
        url = url.replace(/\\/g, "");
        if (!url.startsWith("http://") && !url.startsWith("https://")) {
          url = "http://" + url;
        }

        window.open(url, "_blank");
      } catch (error) {
        console.error("Error al exportar Excel:", error);
        if (this.$toasted) {
          this.$toasted.show("Error al exportar Excel");
        }
      }
    },
    getAlmacenes() {
      this.almacenes = [];
      this.almacenes.push({ value: 0, text: "TODOS" });
      this.axios.get(this.API + "/almacenes").then(r => {
        if (r.data) {
          r.data.map(al => {
            this.almacenes.push({ value: al.IdAlmacen, text: al.Almacen });
          });
        }
      });
    },
    obtenerVendedores() {
      axios
        .get(`${this.API}/usuarios`, {
          params: { q: "" } // traer todos
        })
        .then(res => {
          this.opcionesVendedores = [
            { value: "TODOS", text: "TODOS" }, // opción predeterminada
            ...res.data.map(usuario => ({
              value: usuario.Usuario, // 👈 esto se compara con UsuarioReg
              text: usuario.Usuario
            }))
          ];
        })
        .catch(err => {
          console.error("Error al cargar vendedores:", err);
        });
    },
    obtenerTiposDocumento() {
      axios
        .get(`${this.API}/ventas/tipos`, {
          params: { q: "" } // traer todos
        })
        .then(res => {
          this.opcionesTipoDoc = [
            { value: "TODOS", text: "TODOS" },
            ...res.data.map(doc => ({
              value: doc.IdTipoDoc,
              text: doc.TipoDoc
            }))
          ];
        })
        .catch(err => {
          console.error("Error al cargar tipos de documento:", err);
        });
    },
    generarBarRace() {
  const data = this.rankingData;
  this.rankingChart = this.$echarts.init(this.$refs.rankingChart); // ✅ guardar en data()
  const vendedores = [...new Set(data.map(d => d.Vendedor.trim()))];
  const anios = [...new Set(data.map(d => d.Anio))].sort();

  const dataset = [];
  for (let anio of anios) {
    const datosAnio = data
      .filter(d => d.Anio === anio)
      .map(d => [parseFloat(d.Total), d.Vendedor.trim(), d.Vendedor.trim()]);
    datosAnio.sort((a, b) => b[0] - a[0]);
    dataset.push({ anio, datos: datosAnio.slice(0, 10) });
  }

  const option = {
    grid: { top: 10, bottom: 30, left: 100, right: 80 },
    xAxis: { max: "dataMax", axisLabel: { formatter: n => Math.round(n).toLocaleString() } },
    yAxis: { type: "category", inverse: true, axisLabel: { show: true, fontSize: 14 } },
    series: [
      {
        realtimeSort: true,
        type: "bar",
        itemStyle: { color: param => this.coloresVendedores[param.value[2]] || "#5470c6" },
        encode: { x: 0, y: 1 },
        label: {
          show: true,
          position: "right",
          formatter: param => param.value[0].toFixed(2),
          fontWeight: "bold",
          valueAnimation: true
        }
      }
    ],
    animationDurationUpdate: this.updateFrequency || 2000,
    dataset: { source: dataset[0].datos }
  };

  this.rankingChart.setOption(option); // ✅

  this.rankingChart.on("click", params => {
    if (params.componentType === "series") {
      const vendedorSeleccionado = params.value[1];
      this.cargarDetalleVendedor(vendedorSeleccionado);
    }
  });

  let i = 0;
  const update = () => {
    if (i >= dataset.length) return;
    option.series[0].data = dataset[i].datos;
    option.graphic.elements[0].style.text = dataset[i].anio;
    this.rankingChart.setOption(option);
    i++;
    setTimeout(update, this.updateFrequency || 2000);
  };

  update();
},
    getDataDias(anio, mes, dias, idAlmacen) {
      this.lstDia = [];
      this.axios
        .get(this.API + "ventas/estadistica/dia", {
          params: {
            anio: anio,
            mes: mes,
            idAlmacen: idAlmacen || this.idAlmacen
          }
        })
        .then(response => {
          this.lstDia = response.data;
          this.$refs.chartGrafica.GraficarDias(mes.value, dias, response.data);
          this.calcularTotalDia();
        })
        .catch(error => {
          console.log(error);
        });
    },
    async cargarDetalleVendedor(vendedor) {
  // ✅ Previene llamadas repetidas si ya es el vendedor seleccionado
  if (this.vendedorSeleccionado === vendedor && this.ventasVendedorSeleccionado.length > 0) {
    return;
  }

  this.vendedorSeleccionado = vendedor;

  try {
    const res = await this.axios.get(
      this.API + "ventas/estadistica/detallevendedor",
      {
        params: {
          fechaDesde: this.fechaDesde,
          fechaHasta: this.fechaHasta,
          idAlmacen: this.idAlmacen,
          vendedor: vendedor,
          tipoDoc: this.tipoDoc // Asegúrate que lo pasas si ahora lo usas
        }
      }
    );

    this.ventasVendedorSeleccionado = res.data.detalle || [];
    this.resumenVendedor = res.data.resumen || {
      producto_mas_vendido: '',
      ganancia_total: 0,
      utilidad_promedio: 0,
      total_productos_vendidos: 0
    };
  } catch (err) {
    console.error("Error al obtener detalle del vendedor:", err);
    this.ventasVendedorSeleccionado = [];
    this.resumenVendedor = {
      producto_mas_vendido: '',
      ganancia_total: 0,
      utilidad_promedio: 0,
      total_productos_vendidos: 0
    };
  }
},
    calcularTotalDia() {
      let totalDia = this.lstDia.reduce((suma, listado) => {
        return suma + parseFloat(listado.Total);
      }, 0);

      this.totalDia = totalDia.toFixed(2);
    },
    getDataMes(anio, idAlmacen) {
      this.lstMes = [];
      this.axios
        .get(this.API + "ventas/estadistica/mes", {
          params: {
            anio: anio,
            idAlmacen: idAlmacen || this.idAlmacen
          }
        })
        .then(response => {
          this.lstMes = response.data;
          this.$refs.chartGraficaMes.GraficarMes(response.data, this.meses);
          this.calcularTotalMes();
        })
        .catch(error => {
          console.log(error);
        });
    },
    calcularTotalMes() {
      let totalMes = this.lstMes.reduce((suma, listado) => {
        return suma + parseFloat(listado.Total);
      }, 0);

      this.totalMes = totalMes.toFixed(2);
    },

    seleccionarDiaPorFecha(fecha) {
      this.fechaSeleccionada = this.$moment(fecha).format("YYYY-MM-DD");
      this.cargarDetalleDia();
    },
    cambioOpcion(e) {
  // 🔹 Destruir gráficos ECharts
  if (this.chartGrafica) {
    this.chartGrafica.dispose();
    this.chartGrafica = null;
  }
  if (this.chartGraficaMes) {
    this.chartGraficaMes.dispose();
    this.chartGraficaMes = null;
  }
  if (this.chartGraficaRango) {
    this.chartGraficaRango.dispose();
    this.chartGraficaRango = null;
  }
  if (this.rankingChart) {
    this.rankingChart.dispose();
    this.rankingChart = null;
  }

  // 🔹 Limpiar datos de Horas Pico
  if (e !== 0) { 
    this.datosHeatmap = [];
    this.fechaSeleccionada = null;
    this.detalleDia = [];
    this.detalleAgrupado = []; 
    this.ventasPorHoraSeleccionada = [];
  }

  // 🔹 Limpiar datos de Ranking
  if (e !== 2) {
    this.vendedorSeleccionado = null;
    this.ventasVendedorSeleccionado = [];
    this.resumenVendedor = {
      producto_mas_vendido: '',
      ganancia_total: 0,
      utilidad_promedio: 0,
      total_productos_vendidos: 0
    };
    this.detalleVendedor = [];
  }

  // 🔹 Ahora sí, ejecutar la lógica de cada tab
  switch (e) {
    case 0:
      this.initDia(0);
      this.idAlmacen = 0;
      break;
    case 1:
      this.initMes(0);
      this.idAlmacen = 0;
      break;
    case 2:
      this.idAlmacen = 0;
      this.fechaDesde = this.$moment().format("YYYY-MM-01");
      this.fechaHasta = this.$moment().format("YYYY-MM-DD");
      break;
    default:
      break;
  }
},
    fechaRango() {
      this.lstRango = [];
      if (this.fechaDesde == "") {
        this.$toasted.show("Ingrese una fecha validad en fecha Inicio");
        return;
      }
      if (this.fechaHasta == "") {
        this.$toasted.show("Ingrese una fecha validad en fecha fin");
        return;
      }
      this.axios
        .get(this.API + "ventas/estadistica/metodopago", {
          params: {
            fechaDesde: this.fechaDesde,
            fechaHasta: this.fechaHasta,
            idAlmacen: this.idAlmacen,
            vendedor: this.idVendedor,
            tipoDoc: this.idTipoDoc !== "TODOS" ? this.idTipoDoc : undefined
          }
        })
        .then(response => {
          this.lstRango = response.data;
          this.$refs.chartGraficaRango.GraficarRangoMetodo(response.data);
          this.calcularTotalRango();
          this.calcularTotalRangoFiltrado();
          this.filtrarGraficoPorMetodo(); // Esto actualiza el gráfico según los botones activos
          this.calcularTotalRangoEfectivo();
          this.calcularTotalRangoTarjeta();
          this.calcularTotalRangoYape();
          this.calcularTotalRangoTransferencia();
        })
        .catch(error => {
          console.log(error);
        });
    },
    async obtenerDetalleVendedor() {
      try {
        const res = await this.axios.get(this.API + "ventas/estadistica/detallevendedor", {
          params: {
            fechaDesde: this.fechaDesde,
            fechaHasta: this.fechaHasta,
            idAlmacen: this.idAlmacen,
            vendedor: this.vendedorSeleccionado
          }
        });

        this.detalleVendedor = res.data.detalle || [];
        this.resumenVendedor = res.data.resumen || {
          producto_mas_vendido: '',
          ganancia_total: 0,
          utilidad_promedio: 0,
          total_productos_vendidos: 0
        };
      } catch (error) {
        console.error("Error al obtener detalle del vendedor:", error);
      }
    },
    calcularTotalRangoFiltrado() {
      const metodos = {
        efectivo: 1,
        tarjeta: 2,
        yape: 3,
        transferencia: 4
      };

      const activos = Object.entries(this.filtrosMetodoPago)
        .filter(([key, activo]) => activo)
        .map(([key]) => metodos[key]);

      let total = this.lstRango
        .filter(item => activos.includes(parseInt(item.IdMetodoPago)))
        .reduce((suma, item) => suma + parseFloat(item.Total), 0);

      this.totalRangoFiltrado = total.toFixed(2);
    },
    calcularTotalRango() {
      let totalRango = this.lstRango.reduce((suma, listado) => {
        return suma + parseFloat(listado.Total);
      }, 0);

      this.totalRango = totalRango.toFixed(2);
    },
    calcularTotalRangoEfectivo() {
      let total = this.lstRango
        .filter(item => parseInt(item.IdMetodoPago) === 1)
        .reduce((suma, item) => suma + parseFloat(item.Total), 0);
      this.totalRangoEfectivo = total.toFixed(2);
      this.metodosPagoDisplay.efectivo.total = total.toFixed(2);
    },
    calcularTotalRangoTarjeta() {
      let total = this.lstRango
        .filter(item => parseInt(item.IdMetodoPago) === 2)
        .reduce((suma, item) => suma + parseFloat(item.Total), 0);
      this.totalRangoTarjeta = total.toFixed(2);
    },
    calcularTotalRangoYape() {
      let total = this.lstRango
        .filter(item => parseInt(item.IdMetodoPago) === 3)
        .reduce((suma, item) => suma + parseFloat(item.Total), 0);
      this.totalRangoYape = total.toFixed(2);
    },
    calcularTotalRangoTransferencia() {
      let total = this.lstRango
        .filter(item => parseInt(item.IdMetodoPago) === 4)
        .reduce((suma, item) => suma + parseFloat(item.Total), 0);
      this.totalRangoTransferencia = total.toFixed(2);
    },
    filtrarGraficoPorMetodo() {
      const metodosSeleccionados = Object.entries(this.filtrosMetodoPago)
        .filter(([key, activo]) => activo)
        .map(([key]) => {
          switch (key) {
            case "efectivo":
              return 1;
            case "tarjeta":
              return 2;
            case "yape":
              return 3;
            case "transferencia":
              return 4;
            default:
              return null;
          }
        });

      const dataFiltrada = this.lstRango.filter(item =>
        metodosSeleccionados.includes(parseInt(item.IdMetodoPago))
      );

      // Actualiza ambos gráficos (si ambos se usan visualmente)
      if (this.$refs.lineChartRango) {
        this.$refs.lineChartRango.GraficarRangoMetodo(dataFiltrada);
      }
      if (this.$refs.chartGraficaRango) {
        this.$refs.chartGraficaRango.GraficarRangoMetodo(dataFiltrada);
      }
    }
  },
  components: {
    BarChart,
    LineChart
  }
};
</script>
<style scoped>
.heatmap-scroll {
  width: 100%;
  overflow-x: auto;
}

/* El contenedor real se comporta como una “tabla” de ancho según su contenido */
.heatmap-container {
  display: inline-block; /* o display: table; */
  background: #fff;
  padding: 20px;
  border-radius: 12px;
  border: 1px solid #ddd;
  box-sizing: border-box;
}

/* Aumentamos el tamaño de las celdas para que se vean más grandes */
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

/* Header y filas siguen con display flex para alinear las celdas */
.heatmap-header,
.heatmap-row {
  display: flex;
}

/* Sticky en la primera columna */
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
  line-height: 1.5; /* 🔽 reduce separación vertical */
  display: inline-block;
}

/* Total general */
.cell.header.total-gral {
  background-color: #000;
  color: #fff;
}

/* Tooltip */
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

/*FILTRO DE DIA - MAPA DE CALOR*/
.heatmap-wrapper {
  height: 100%;
}

.detalle-dia {
  background: #fafafa;
  overflow-y: auto;
}

.detalle-dia h5 {
  margin-bottom: 0.5rem;
}

.detalle-dia ul {
  list-style: none;
  padding: 0;
}

.detalle-dia li {
  padding: 6px;
  border-bottom: 1px solid #eee;
  font-size: 14px;
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

.ranking-chart {
  width: 700px;
  height: 500px;
  max-width: 100%;
}
.tabla-scroll {
  max-height: 780px; /* Puedes ajustar el alto que desees */
  overflow-y: auto;
  overflow-x: auto;
  border: 1px solid #ddd;
}
</style>