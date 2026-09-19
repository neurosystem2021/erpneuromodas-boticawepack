<template>
  <div class="mantenimiento-container">

    <!-- ENCABEZADO -->
    <div class="page-header">
      <div>
        <h2>
          <i class="fa fa-cogs"></i>
          Mantenimiento del sistema
        </h2>

        <p>
          Administración y limpieza de archivos temporales del sistema.
        </p>
      </div>

      <div class="estado-general">
        <span
          class="estado-dot"
          :class="{ activo: automaticoActivo }"
        ></span>

        {{ automaticoActivo ? 'Automático activo' : 'Automático detenido' }}
      </div>
    </div>


    <!-- RESUMEN -->
    <div class="row">

      <!-- ARCHIVOS -->
      <div class="col-md-4">
        <div class="info-card">
          <div class="icon-box archivos">
            <i class="fa fa-file-pdf-o"></i>
          </div>

          <div>
            <span class="card-label">ARCHIVOS</span>
            <h3>PDF</h3>
            <p>Comprobantes electrónicos almacenados</p>
          </div>
        </div>
      </div>


      <!-- ANTIGÜEDAD -->
      <div class="col-md-4">
        <div class="info-card">
          <div class="icon-box tiempo">
            <i class="fa fa-clock-o"></i>
          </div>

          <div>
            <span class="card-label">ANTIGÜEDAD</span>
            <h3>{{ diasRetencion }} días</h3>
            <p>Los archivos anteriores serán eliminados</p>
          </div>
        </div>
      </div>


      <!-- ESTADO -->
      <div class="col-md-4">
        <div class="info-card">
          <div class="icon-box estado">
            <i class="fa fa-shield"></i>
          </div>

          <div>
            <span class="card-label">ESTADO</span>

            <h3>
              {{ ejecutando ? 'Ejecutando...' : 'Sistema disponible' }}
            </h3>

            <p>
              Última ejecución:
              {{ ultimaEjecucion || 'Sin ejecuciones' }}
            </p>
          </div>
        </div>
      </div>

    </div>


    <!-- CONTENIDO PRINCIPAL -->
    <div class="row">

      <!-- LIMPIEZA AUTOMÁTICA -->
      <div class="col-md-12">

        <div class="panel">

          <div class="panel-header automatico">

            <div class="panel-icon">
              <i class="fa fa-refresh"></i>
            </div>

            <div>
              <h4>Limpieza automática</h4>

              <p>
                Ejecuta la limpieza periódicamente.
              </p>
            </div>

          </div>


          <div class="panel-body">

            <div class="automatico-status">

              <div class="status-circle">
                <i
                  class="fa"
                  :class="
                    automaticoActivo
                      ? 'fa-play'
                      : 'fa-pause'
                  "
                ></i>
              </div>

              <div>

                <strong>
                  {{
                    automaticoActivo
                      ? 'Limpieza automática activa'
                      : 'Limpieza automática detenida'
                  }}
                </strong>

                <p>
                  Se ejecuta cada {{ intervaloMinutos }} minutos
                  mientras este módulo permanezca abierto.
                </p>

              </div>

            </div>


            <div class="automatico-controls">

              <button
                v-if="!automaticoActivo"
                class="btn btn-success"
                @click="activarAutomatico"
              >
                <i class="fa fa-play"></i>
                Activar automático
              </button>

              <button
                v-else
                class="btn btn-danger"
                @click="detenerAutomatico"
              >
                <i class="fa fa-stop"></i>
                Detener automático
              </button>

            </div>


            <div class="next-run">

              <i class="fa fa-clock-o"></i>

              <span>
                Próxima ejecución:
              </span>

              <strong>
                {{ proximaEjecucion || 'No programada' }}
              </strong>

            </div>

          </div>

        </div>

      </div>

    </div>


    <!-- RESULTADO DE LA ÚLTIMA LIMPIEZA -->
    <div class="panel resultado-panel">

      <div class="panel-header">

        <div class="panel-icon">
          <i class="fa fa-bar-chart"></i>
        </div>

        <div>
          <h4>Resultado de limpieza</h4>

          <p>
            Información de la última ejecución.
          </p>
        </div>

      </div>


      <div class="estadisticas">

        <div class="estadistica">

          <span class="numero">
            {{ estadisticas.eliminados }}
          </span>

          <span class="texto">
            PDFs eliminados
          </span>

        </div>


        <div class="estadistica">

          <span class="numero">
            {{ estadisticas.espacio }}
          </span>

          <span class="texto">
            Espacio liberado
          </span>

        </div>


        <div class="estadistica">

          <span class="numero">
            {{ estadisticas.empresas }}
          </span>

          <span class="texto">
            Empresas procesadas
          </span>

        </div>

      </div>

    </div>


    <!-- INFORMACIÓN -->
    <div class="security-info">

      <i class="fa fa-shield"></i>

      <div>

        <strong>Protección del sistema</strong>

        <p>
          La limpieza solo debe afectar archivos PDF que hayan
          superado el período de retención configurado.
          Los archivos XML no serán eliminados mediante este proceso.
        </p>

      </div>

    </div>

  </div>
</template>


<script>
import config from "@/store/config.js";
export default {

  name: "Mantenimiento",

  data() {

    return {
      API: config.API,

      // Retención real posteriormente será 45 días
      diasRetencion: 45,

      // Para prueba podemos dejar 5 minutos
      intervaloMinutos: 5,

      automaticoActivo: false,

      intervaloAutomatico: null,

      ejecutando: false,

      ultimaEjecucion: null,

      proximaEjecucion: null,

      resultado: null,

      estadisticas: {
        eliminados: 0,
        espacio: "0 MB",
        empresas: 0
      }

    };

  },


  methods: {

    /**
     * CONFIRMAR LIMPIEZA MANUAL
     */
    confirmarLimpieza() {

      if (this.ejecutando) {
        return;
      }

      const confirmar = window.confirm(
        "¿Deseas ejecutar la limpieza de PDFs?\n\n" +
        "Se eliminarán únicamente los PDFs " +
        "que superen los " +
        this.diasRetencion +
        " días de antigüedad."
      );

      if (!confirmar) {
        return;
      }

      this.ejecutarLimpieza("manual");

    },


    /**
     * EJECUTAR LIMPIEZA
     */
    async ejecutarLimpieza(origen = "manual") {

      if (this.ejecutando) {
        return;
      }

      this.ejecutando = true;

      this.resultado = null;

      try {

        const response = await this.axios.get(
          this.API + "limpiar-pdfs",
          {
            params: {
              dias: this.diasRetencion
            },
            timeout: 120000
          }
        );


        /*
         * El backend debería devolver algo parecido a:
         *
         * {
         *   success: true,
         *   eliminados: 25,
         *   espacio_liberado: "125 MB",
         *   empresas: 10
         * }
         */

        const data = response.data;


        this.estadisticas.eliminados =
          data.eliminados || 0;

        this.estadisticas.espacio =
          data.espacio_liberado || "0 MB";

        this.estadisticas.empresas =
          data.empresas || 0;


        this.ultimaEjecucion =
          this.obtenerHora();


        this.resultado = {

          tipo: "success",

          mensaje:
            origen === "automatico"

              ? "Limpieza automática ejecutada correctamente."

              : "Limpieza ejecutada correctamente."

        };


        if (this.automaticoActivo) {

          this.calcularProximaEjecucion();

        }


      } catch (error) {

        console.error(
          "Error al ejecutar limpieza:",
          error
        );


        this.resultado = {

          tipo: "error",

          mensaje:
            "No se pudo ejecutar la limpieza. " +
            "Verifica la conexión con el servidor."

        };

      } finally {

        this.ejecutando = false;

      }

    },


    /**
     * ACTIVAR AUTOMÁTICO
     */
    activarAutomatico() {

      if (this.automaticoActivo) {
        return;
      }


      this.automaticoActivo = true;


      /*
       * Primera ejecución inmediata
       */
      this.ejecutarLimpieza("automatico");


      /*
       * Después cada X minutos
       */
      this.intervaloAutomatico =
        setInterval(
          () => {

            this.ejecutarLimpieza("automatico");

          },
          this.intervaloMinutos * 60 * 1000
        );


      this.calcularProximaEjecucion();

    },


    /**
     * DETENER AUTOMÁTICO
     */
    detenerAutomatico() {

      this.automaticoActivo = false;


      if (this.intervaloAutomatico) {

        clearInterval(
          this.intervaloAutomatico
        );

        this.intervaloAutomatico = null;

      }


      this.proximaEjecucion = null;

    },


    /**
     * CALCULAR PRÓXIMA EJECUCIÓN
     */
    calcularProximaEjecucion() {

      const fecha = new Date();

      fecha.setMinutes(
        fecha.getMinutes() +
        this.intervaloMinutos
      );


      this.proximaEjecucion =
        fecha.toLocaleTimeString();

    },


    /**
     * OBTENER HORA
     */
    obtenerHora() {

      return new Date().toLocaleString();

    }

  },


  /**
   * IMPORTANTE:
   * Detener el intervalo al salir de la pantalla.
   */
  beforeDestroy() {

    if (this.intervaloAutomatico) {

      clearInterval(
        this.intervaloAutomatico
      );

    }

  }

};
</script>


<style scoped>

.mantenimiento-container {
  padding: 25px;
  background: #f5f7fa;
  min-height: calc(100vh - 70px);
}


/* HEADER */

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
}

.page-header h2 {
  margin: 0;
  font-weight: 600;
  color: #263238;
}

.page-header h2 i {
  margin-right: 10px;
}

.page-header p {
  margin: 6px 0 0;
  color: #78909c;
}


.estado-general {
  background: white;
  padding: 10px 16px;
  border-radius: 20px;
  box-shadow: 0 2px 8px rgba(0,0,0,.06);
  color: #607d8b;
}

.estado-dot {
  width: 9px;
  height: 9px;
  display: inline-block;
  border-radius: 50%;
  background: #90a4ae;
  margin-right: 7px;
}

.estado-dot.activo {
  background: #28a745;
}


/* CARDS */

.info-card {
  background: white;
  border-radius: 8px;
  padding: 20px;
  display: flex;
  align-items: center;
  margin-bottom: 20px;
  box-shadow: 0 2px 8px rgba(0,0,0,.06);
}

.icon-box {
  width: 55px;
  height: 55px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 15px;
  font-size: 24px;
}

.icon-box.archivos {
  background: #fdecea;
  color: #dc3545;
}

.icon-box.tiempo {
  background: #fff3cd;
  color: #856404;
}

.icon-box.estado {
  background: #e8f5e9;
  color: #28a745;
}

.card-label {
  font-size: 11px;
  color: #90a4ae;
  font-weight: 600;
}

.info-card h3 {
  margin: 3px 0;
  color: #263238;
}

.info-card p {
  margin: 0;
  font-size: 12px;
  color: #90a4ae;
}


/* PANEL */

.panel {
  background: white;
  border-radius: 8px;
  margin-bottom: 20px;
  box-shadow: 0 2px 8px rgba(0,0,0,.06);
  overflow: hidden;
}

.panel-header {
  padding: 18px 20px;
  display: flex;
  align-items: center;
  border-bottom: 1px solid #eceff1;
}

.panel-header h4 {
  margin: 0;
  font-weight: 600;
  color: #263238;
}

.panel-header p {
  margin: 4px 0 0;
  font-size: 13px;
  color: #90a4ae;
}

.panel-icon {
  width: 42px;
  height: 42px;
  border-radius: 7px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 12px;
  background: #eceff1;
  color: #546e7a;
  font-size: 18px;
}

.panel-header.manual .panel-icon {
  background: #e3f2fd;
  color: #1976d2;
}

.panel-header.automatico .panel-icon {
  background: #e8f5e9;
  color: #2e7d32;
}

.panel-body {
  padding: 20px;
}


/* WARNING */

.warning-box {
  display: flex;
  padding: 14px;
  border-radius: 6px;
  background: #fff8e1;
  border: 1px solid #ffe082;
  margin-bottom: 20px;
}

.warning-box > i {
  color: #f9a825;
  font-size: 20px;
  margin-right: 12px;
}

.warning-box strong {
  color: #6d4c41;
}

.warning-box p {
  margin: 4px 0 0;
  color: #795548;
  font-size: 13px;
}


/* BOTON */

.btn-limpieza {
  padding: 13px;
  font-weight: 600;
}


/* RESULTADO */

.resultado {
  margin-top: 15px;
  padding: 12px;
  border-radius: 5px;
  display: flex;
  align-items: center;
}

.resultado i {
  margin-right: 8px;
}

.resultado.success {
  background: #e8f5e9;
  color: #2e7d32;
}

.resultado.error {
  background: #ffebee;
  color: #c62828;
}


/* AUTOMATICO */

.automatico-status {
  display: flex;
  align-items: center;
  margin-bottom: 20px;
}

.status-circle {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: #e8f5e9;
  color: #2e7d32;
  display: flex;
  justify-content: center;
  align-items: center;
  margin-right: 15px;
}

.automatico-status strong {
  color: #263238;
}

.automatico-status p {
  margin: 4px 0 0;
  font-size: 13px;
  color: #90a4ae;
}

.automatico-controls {
  margin-bottom: 20px;
}

.next-run {
  background: #f5f7fa;
  padding: 12px;
  border-radius: 5px;
  font-size: 13px;
}

.next-run i {
  margin-right: 6px;
  color: #607d8b;
}


/* ESTADISTICAS */

.estadisticas {
  display: flex;
  padding: 25px;
}

.estadistica {
  flex: 1;
  text-align: center;
  border-right: 1px solid #eceff1;
}

.estadistica:last-child {
  border-right: none;
}

.numero {
  display: block;
  font-size: 28px;
  font-weight: 600;
  color: #263238;
}

.texto {
  font-size: 12px;
  color: #90a4ae;
}


/* SEGURIDAD */

.security-info {
  background: #e8f4fd;
  border: 1px solid #b3e0f2;
  border-radius: 7px;
  padding: 15px;
  display: flex;
  color: #315b6d;
}

.security-info > i {
  font-size: 22px;
  margin-right: 12px;
}

.security-info p {
  margin: 4px 0 0;
  font-size: 13px;
}


/* RESPONSIVE */

@media (max-width: 768px) {

  .page-header {
    display: block;
  }

  .estado-general {
    margin-top: 15px;
    display: inline-block;
  }

  .estadisticas {
    display: block;
  }

  .estadistica {
    border-right: none;
    border-bottom: 1px solid #eceff1;
    padding: 15px;
  }

}

</style>