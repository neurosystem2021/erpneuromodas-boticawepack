<template>
  <div class="agente-page" :class="{ 'modo-nocturno': modoNocturno }">
    <!-- ===== Panel lateral: Historial de conversaciones ===== -->
    <div class="historial-panel" :class="{ colapsado: !panelAbierto }">
      <div class="historial-header">
        <button
          class="btn-toggle-panel"
          type="button"
          title="Mostrar/ocultar historial"
          @click="panelAbierto = !panelAbierto"
        >
          <span>{{ panelAbierto ? '⟨' : '⟩' }}</span>
        </button>
        <span v-if="panelAbierto" class="historial-titulo">Historial</span>
      </div>

      <button
        v-if="panelAbierto"
        type="button"
        class="btn-nueva-conversacion"
        @click="nuevaConversacion"
      >
        + Nueva conversación
      </button>

      <div v-if="panelAbierto" class="historial-lista">
        <div v-if="historial.length === 0" class="historial-vacio">
          <small>Aún no hay conversaciones guardadas</small>
        </div>

        <div
          v-for="item in historialOrdenado"
          :key="item.IdConversacion"
          class="historial-item"
          :class="{ activo: item.IdConversacion === conversacionActualId }"
          @click="cargarConversacion(item.IdConversacion)"
        >
          <div class="historial-item-texto">
            <span class="historial-item-titulo">{{ item.Titulo }}</span>
            <small class="historial-item-fecha">{{ formatearFecha(item.FechaModificacion) }}</small>
          </div>
          <button
            type="button"
            class="btn-borrar-historial"
            title="Eliminar del historial"
            @click.stop="eliminarConversacion(item.IdConversacion)"
          >
            ✕
          </button>
        </div>
      </div>
    </div>

    <!-- ===== Chat principal ===== -->
    <div class="agente-card">
      <div class="agente-header">
        <div class="agente-header-title">
          <span class="agente-avatar">
            <span class="agente-avatar-glow"></span>
            <span class="agente-avatar-icon">✦</span>
          </span>
          <div>
            <h5 class="mb-0">Agente Inteligente POS</h5>
            <small class="agente-subtitle">Pregunta sobre ventas, productos, vendedores o clientes</small>
          </div>
        </div>
        <div class="header-acciones">
          <button
            type="button"
            class="btn-modo-nocturno"
            :class="{ activo: modoNocturno }"
            :aria-pressed="modoNocturno"
            title="Alternar modo nocturno"
            @click="alternarModoNocturno"
          >
            <span class="modo-nocturno-icon">{{ modoNocturno ? '☀️' : '🌙' }}</span>
          </button>
          <b-button
            v-if="mensajes.length > 1"
            variant="outline-secondary"
            size="sm"
            class="btn-limpiar"
            :disabled="cargando"
            @click="limpiarChat"
          >
            <span class="icon-limpiar">↺</span> Limpiar
          </b-button>
        </div>
      </div>

      <div class="chat-container" ref="chatBox">
        <div
          v-for="msg in mensajes"
          :key="msg.id"
          :class="['chat-row', msg.rol === 'usuario' ? 'usuario' : 'agente']"
        >
          <span v-if="msg.rol !== 'usuario'" class="bubble-avatar" :class="{ 'is-error': msg.esError }">
            {{ msg.esError ? '!' : '✦' }}
          </span>

          <div :class="['chat-message', msg.esError ? 'es-error' : '']">
            <div class="message-content">
              <strong class="message-autor">{{ msg.rol === 'usuario' ? 'Tú' : 'Agente IA' }}</strong>
              <p class="mb-1 texto-mensaje">{{ msg.texto }}</p>

              <div v-if="msg.grafico" class="vista-toggle">
                <button
                  type="button"
                  class="btn-vista"
                  :class="{ activo: msg.vista === 'tabla' }"
                  @click="msg.vista = 'tabla'"
                >
                  ☰ Tabla
                </button>
                <button
                  type="button"
                  class="btn-vista"
                  :class="{ activo: msg.vista === 'grafico' }"
                  @click="msg.vista = 'grafico'"
                >
                  📊 Gráfico
                </button>
              </div>

              <div
                v-if="msg.datos && msg.datos.length > 0 && (!msg.grafico || msg.vista === 'tabla')"
                class="tabla-wrapper"
              >
                <b-table
                  :items="msg.datos"
                  small
                  bordered
                  striped
                  responsive
                  class="mt-2 bg-white tabla-resultados"
                ></b-table>
              </div>

              <div v-if="msg.grafico && msg.vista === 'grafico'" class="grafico-contenedor">

                <!-- Barras horizontales (ranking) -->
                <div v-if="msg.grafico.tipo === 'barras'" class="grafico-barras">
                  <div v-for="(fila, idx) in msg.grafico.filas" :key="idx" class="grafico-fila">
                    <span class="grafico-etiqueta" :title="fila.etiqueta">{{ fila.etiqueta }}</span>
                    <div class="grafico-barra-track">
                      <div class="grafico-barra" :style="{ width: fila.porcentaje + '%' }"></div>
                    </div>
                    <span class="grafico-valor">{{ formatearNumero(fila.valor) }}</span>
                  </div>
                </div>

                <!-- Líneas (series por fecha) -->
                <div v-else-if="msg.grafico.tipo === 'lineas'" class="grafico-lineas">
                  <svg
                    :viewBox="`0 0 ${msg.grafico.anchoSvg} ${msg.grafico.altoSvg}`"
                    class="grafico-svg-lineas"
                    preserveAspectRatio="none"
                  >
                    <path :d="msg.grafico.pathD" class="linea-trazo" fill="none"></path>
                    <circle
                      v-for="(p, idx) in msg.grafico.puntos"
                      :key="idx"
                      :cx="p.x"
                      :cy="p.y"
                      r="3"
                      class="linea-punto"
                    >
                      <title>{{ p.etiqueta }}: {{ formatearNumero(p.valor) }}</title>
                    </circle>
                  </svg>
                  <div class="grafico-lineas-etiquetas">
                    <span v-for="(p, idx) in msg.grafico.puntosVisibles" :key="idx" class="linea-etiqueta">
                      {{ p.etiqueta }}
                    </span>
                  </div>
                </div>

                <!-- Pastel / Dona (proporciones) -->
                <div v-else-if="msg.grafico.tipo === 'pastel'" class="grafico-pastel">
                  <svg viewBox="0 0 150 150" class="grafico-svg-pastel">
                    <circle
                      v-for="(seg, idx) in msg.grafico.segmentos"
                      :key="idx"
                      cx="75"
                      cy="75"
                      :r="msg.grafico.radio"
                      fill="none"
                      :stroke="seg.color"
                      stroke-width="26"
                      :stroke-dasharray="seg.dasharray"
                      :stroke-dashoffset="seg.dashoffset"
                      transform="rotate(-90 75 75)"
                    >
                      <title>{{ seg.etiqueta }}: {{ seg.porcentaje }}%</title>
                    </circle>
                  </svg>
                  <div class="grafico-pastel-leyenda">
                    <div v-for="(seg, idx) in msg.grafico.segmentos" :key="idx" class="leyenda-item">
                      <span class="leyenda-color" :style="{ backgroundColor: seg.color }"></span>
                      <span class="leyenda-etiqueta" :title="seg.etiqueta">{{ seg.etiqueta }}</span>
                      <span class="leyenda-valor">{{ seg.porcentaje }}%</span>
                    </div>
                  </div>
                </div>

                <!-- Barras comparativas / agrupadas (2+ columnas numéricas) -->
                <div v-else-if="msg.grafico.tipo === 'comparativas'" class="grafico-comparativo">
                  <div class="grafico-comparativo-leyenda">
                    <span v-for="(c, idx) in msg.grafico.colores" :key="idx" class="leyenda-item">
                      <span class="leyenda-color" :style="{ backgroundColor: c.color }"></span>
                      <span class="leyenda-etiqueta">{{ c.campo }}</span>
                    </span>
                  </div>
                  <div v-for="(fila, idx) in msg.grafico.filas" :key="idx" class="grafico-comparativo-fila">
                    <span class="grafico-etiqueta" :title="fila.etiqueta">{{ fila.etiqueta }}</span>
                    <div class="grafico-comparativo-barras">
                      <div
                        v-for="(v, i2) in fila.valores"
                        :key="i2"
                        class="grafico-barra-track comparativo-track"
                      >
                        <div class="grafico-barra" :style="{ width: v.porcentaje + '%', background: v.color }"></div>
                      </div>
                    </div>
                    <div class="grafico-comparativo-valores">
                      <span v-for="(v, i2) in fila.valores" :key="i2" class="grafico-valor-mini">
                        {{ formatearNumero(v.valor) }}
                      </span>
                    </div>
                  </div>
                </div>

              </div>

              <small v-if="msg.hora" class="hora-mensaje">{{ msg.hora }}</small>
            </div>
          </div>

          <span v-if="msg.rol === 'usuario'" class="bubble-avatar usuario-avatar">Tú</span>
        </div>

        <div v-if="cargando" class="chat-row agente">
          <span class="bubble-avatar">✦</span>
          <div class="chat-message">
            <div class="message-content typing-content">
              <span class="typing-dots">
                <span></span><span></span><span></span>
              </span>
              <span class="typing-label">Consultando datos...</span>
            </div>
          </div>
        </div>

        <div v-if="mensajes.length === 0" class="chat-vacio text-center py-4">
          <div class="chat-vacio-icon">✦</div>
          <p class="mb-0">No hay mensajes todavía.</p>
          <small>Escribe una pregunta para empezar</small>
        </div>
      </div>

      <b-form @submit.prevent="enviarConsulta" class="form-consulta">
        <b-input-group class="input-group-consulta">
          <b-form-input
            v-model="pregunta"
            :placeholder="escuchando ? 'Escuchando...' : 'Ej. ¿Qué producto se vendió más este mes?'"
            :disabled="cargando"
            ref="inputPregunta"
            autocomplete="off"
            class="input-pregunta"
            @keydown.enter.exact.prevent="enviarConsulta"
          ></b-form-input>
          <b-input-group-append>
            <button
              v-if="reconocimientoVozDisponible"
              type="button"
              class="btn-microfono"
              :class="{ activo: escuchando }"
              :disabled="cargando"
              :title="escuchando ? 'Detener dictado' : 'Buscar por voz'"
              @click="alternarReconocimientoVoz"
            >
              <span v-if="escuchando" class="mic-ondas">
                <span></span><span></span><span></span>
              </span>
              <span v-else class="mic-icon">🎤</span>
            </button>
            <b-button type="submit" class="btn-enviar" :disabled="cargando || !pregunta.trim()">
              <span v-if="cargando" class="spinner-enviar"></span>
              <span v-else>Enviar ➤</span>
            </b-button>
          </b-input-group-append>
        </b-input-group>
        <small v-if="errorVoz" class="error-voz">{{ errorVoz }}</small>
      </b-form>
    </div>
  </div>
</template>

<script>

import config from '@/store/config.js'

let contadorMensajes = 0;

const MENSAJE_BIENVENIDA =
  '¡Hola! Pregúntame sobre ventas, productos más vendidos, rendimiento de vendedores o clientes.';

// Paleta de colores reutilizada por los gráficos de pastel y comparativos
const PALETA_GRAFICOS = ['#6c5ce7', '#2ec4b6', '#ffb703', '#e0575f', '#3a86ff', '#ff6f91', '#8ecae6', '#ff9f1c'];

export default {
  name: 'AgenteChat',
  data() {
    return {
      pregunta: '',
      cargando: false,
      modoNocturno: false,
      mensajes: [this.crearMensaje('agente', MENSAJE_BIENVENIDA)],

      // ---- Historial (persistido en base de datos) ----
      historial: [],
      conversacionActualId: null, // null = conversación aún no creada en BD
      panelAbierto: true,
      cargandoHistorial: false,

      // ---- Búsqueda por voz ----
      reconocimientoVozDisponible: false,
      escuchando: false,
      errorVoz: '',
      _reconocimiento: null, // instancia de SpeechRecognition (no reactiva)
      _preguntaAntesDeEscuchar: '' // texto que ya había en el input antes de dictar
    };
  },
  computed: {
    // El backend ya devuelve el historial ordenado por updated_at DESC
    historialOrdenado() {
      return this.historial;
    }
  },
  mounted() {
    this.cargarPreferenciaModoNocturno();
    this.cargarHistorial();
    this.inicializarReconocimientoVoz();
    this.scrollToBottom();
    this.$nextTick(() => {
      if (this.$refs.inputPregunta) this.$refs.inputPregunta.focus();
    });
  },
  beforeDestroy() {
    // Evita que el reconocimiento siga escuchando (o dispare eventos)
    // si el usuario navega a otra vista mientras dictaba.
    if (this._reconocimiento) {
      this._reconocimiento.onresult = null;
      this._reconocimiento.onerror = null;
      this._reconocimiento.onend = null;
      try {
        this._reconocimiento.abort();
      } catch (e) {
        // no-op
      }
    }
  },
  methods: {
    alternarModoNocturno() {
      this.modoNocturno = !this.modoNocturno;
      try {
        localStorage.setItem('agenteChat.modoNocturno', this.modoNocturno ? '1' : '0');
      } catch (e) {
        // localStorage puede no estar disponible (modo privado, etc.); no es crítico
        console.warn('No se pudo guardar la preferencia de modo nocturno:', e);
      }
    },

    cargarPreferenciaModoNocturno() {
      try {
        this.modoNocturno = localStorage.getItem('agenteChat.modoNocturno') === '1';
      } catch (e) {
        this.modoNocturno = false;
      }
    },

    crearMensaje(rol, texto, datos = null, esError = false, preguntaOrigen = '') {
      contadorMensajes += 1;
      const grafico = this.prepararGrafico(datos, preguntaOrigen);
      const quiereGrafico = this.detectarIntencionGrafico(preguntaOrigen);
      return {
        id: contadorMensajes,
        rol,
        texto,
        datos,
        esError,
        grafico,
        vista: grafico && quiereGrafico ? 'grafico' : 'tabla',
        hora: new Date().toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' })
      };
    },

    // Detecta si la pregunta del usuario pide explícitamente un gráfico/visualización
    detectarIntencionGrafico(texto) {
      return /gr[aá]fic|chart|diagrama|visualiz/i.test(texto || '');
    },

    // ---------------------------------------------------------------
    // Búsqueda por voz: dicta texto dentro del input. NO envía la
    // consulta automáticamente; el usuario debe presionar "Enviar"
    // (o Enter) igual que si hubiera escrito el texto a mano.
    // ---------------------------------------------------------------
    inicializarReconocimientoVoz() {
      const SpeechRecognitionApi = window.SpeechRecognition || window.webkitSpeechRecognition;
      if (!SpeechRecognitionApi) {
        this.reconocimientoVozDisponible = false;
        return;
      }

      const reconocimiento = new SpeechRecognitionApi();
      reconocimiento.lang = 'es-PE';
      reconocimiento.continuous = false; // se detiene solo al terminar de hablar
      reconocimiento.interimResults = true; // muestra el texto mientras se habla
      reconocimiento.maxAlternatives = 1;

      reconocimiento.onresult = (evento) => {
        let transcripcion = '';
        for (let i = 0; i < evento.results.length; i++) {
          transcripcion += evento.results[i][0].transcript;
        }
        // Solo actualiza el texto del input; el envío lo decide el usuario
        this.pregunta = (this._preguntaAntesDeEscuchar + ' ' + transcripcion).trim();
      };

      reconocimiento.onerror = (evento) => {
        this.escuchando = false;
        const mensajes = {
          'not-allowed': 'Permiso de micrófono denegado. Habilítalo en tu navegador para dictar.',
          'no-speech': 'No se detectó voz. Intenta de nuevo.',
          'audio-capture': 'No se encontró un micrófono disponible.'
        };
        this.errorVoz = mensajes[evento.error] || 'No se pudo completar el dictado por voz.';
        setTimeout(() => {
          this.errorVoz = '';
        }, 4000);
      };

      reconocimiento.onend = () => {
        this.escuchando = false;
      };

      this._reconocimiento = reconocimiento;
      this.reconocimientoVozDisponible = true;
    },

    alternarReconocimientoVoz() {
      if (!this._reconocimiento) return;

      if (this.escuchando) {
        this._reconocimiento.stop();
        this.escuchando = false;
        return;
      }

      this.errorVoz = '';
      this._preguntaAntesDeEscuchar = this.pregunta.trim();
      try {
        this._reconocimiento.start();
        this.escuchando = true;
      } catch (e) {
        // start() lanza error si ya estaba activo; se ignora
        console.warn('No se pudo iniciar el reconocimiento de voz:', e);
      }
    },

    // -----------------------------------------------------------------
    // Preparación de gráficos: a partir del array "datos" (resultado de
    // la consulta SQL) decide qué tipo de gráfico conviene y arma la
    // estructura necesaria para dibujarlo.
    // -----------------------------------------------------------------
    prepararGrafico(datos, preguntaOrigen = '') {
      if (!Array.isArray(datos) || datos.length < 2) return null;

      const columnas = Object.keys(datos[0] || {});
      if (columnas.length < 2) return null;

      const esNumerico = (v) =>
        v !== null && v !== '' && v !== undefined && !isNaN(parseFloat(v)) && isFinite(v);

      const pareceFecha = (v) => {
        if (v === null || v === undefined) return false;
        const s = String(v).trim();
        return (
          /^\d{4}-\d{2}-\d{2}/.test(s) || // 2026-08-17
          /^\d{2}\/\d{2}\/\d{4}/.test(s) || // 17/08/2026
          /^\d{2}-\d{2}-\d{4}/.test(s) // 17-08-2026
        );
      };

      // Columnas numéricas: todas donde TODOS los valores son numéricos
      const columnasNumericas = columnas.filter((col) => datos.every((fila) => esNumerico(fila[col])));
      if (columnasNumericas.length === 0) return null;

      // Columnas tipo identificador (IdMetodoPago, IdVendedor, etc.): son
      // numéricas pero no representan una magnitud que tenga sentido graficar,
      // así que se excluyen de las columnas "de valor".
      const esColumnaId = (col) => /^id/i.test(col.trim());
      const columnasValor = columnasNumericas.filter((col) => !esColumnaId(col));
      // Si TODAS las numéricas son ids (caso raro), se usan igual como fallback
      const numericasParaTipo = columnasValor.length > 0 ? columnasValor : columnasNumericas;

      // Columna de fecha: no numérica y con formato de fecha en todas las filas
      const campoFecha = columnas.find(
        (col) => !columnasNumericas.includes(col) && datos.every((fila) => pareceFecha(fila[col]))
      );

      // Columna de etiqueta: preferimos una columna de texto; si no hay,
      // usamos una columna Id (aunque no sea ideal) antes que perder el gráfico
      const campoEtiqueta =
        columnas.find((col) => !columnasNumericas.includes(col)) ||
        columnas.find((col) => esColumnaId(col)) ||
        columnas[0];

      // Columna que ya viene expresada como porcentaje (nombre lo delata)
      const columnaPorcentaje = numericasParaTipo.find((col) => /porcentaje|%|proporci[oó]n/i.test(col));

      const tipo = this.detectarTipoGrafico(preguntaOrigen, {
        columnasNumericas: numericasParaTipo,
        columnaPorcentaje,
        campoFecha,
        datos
      });

      switch (tipo) {
        case 'lineas':
          return this.construirGraficoLineas(datos, campoFecha || campoEtiqueta, numericasParaTipo[0]);
        case 'pastel': {
          // Si hay una columna de monto además de la de porcentaje, se usa el
          // monto para calcular las proporciones (más preciso que redondeos
          // ya guardados); si no, se usa directamente la columna disponible.
          const campoValorPastel =
            (columnaPorcentaje && numericasParaTipo.find((c) => c !== columnaPorcentaje)) ||
            columnaPorcentaje ||
            numericasParaTipo[0];
          return this.construirGraficoPastel(datos, campoEtiqueta, campoValorPastel);
        }
        case 'comparativas':
          return this.construirGraficoComparativo(datos, campoEtiqueta, numericasParaTipo);
        default:
          return this.construirGraficoBarras(datos, campoEtiqueta, numericasParaTipo[0]);
      }
    },

    // Decide qué tipo de gráfico conviene según la pregunta del usuario
    // (si menciona algo explícito) y, si no, según la forma de los datos:
    //  - Barras horizontales → ranking (ej. "top 3 vendedores")
    //  - Líneas               → cuando hay una columna de fecha
    //  - Pastel/dona          → cuando los datos representan proporciones
    //  - Barras comparativas  → cuando hay 2+ columnas numéricas por categoría
    detectarTipoGrafico(preguntaOrigen, { columnasNumericas, columnaPorcentaje, campoFecha, datos }) {
      const texto = (preguntaOrigen || '').toLowerCase();

      // 1) Pedido explícito en la pregunta
      if (/l[ií]neas?/i.test(texto) && campoFecha) return 'lineas';
      if (/pastel|dona|donut|torta|circular/i.test(texto)) return 'pastel';
      if (/comparativ|agrupad|\bvs\.?\b|versus/i.test(texto) && columnasNumericas.length >= 2) return 'comparativas';
      if (/barras?/i.test(texto) && !/comparativ|agrupad/i.test(texto)) return 'barras';

      // 2) Si el propio resultado ya trae una columna de "porcentaje"/"%",
      // es la señal más fuerte de que se quieren ver proporciones, por
      // encima de tratarla como una serie más para comparar.
      if (columnaPorcentaje && datos.length <= 8) return 'pastel';

      // 3) Detección automática según la forma de los datos
      if (campoFecha && columnasNumericas.length === 1) return 'lineas';
      if (columnasNumericas.length >= 2) return 'comparativas';

      if (columnasNumericas.length === 1 && datos.length <= 8) {
        const valores = datos.map((f) => parseFloat(f[columnasNumericas[0]]));
        const suma = valores.reduce((a, b) => a + b, 0);
        const pareceProporcion =
          /%|porcentaje|proporci[oó]n|m[eé]todo de pago|distribuci[oó]n/i.test(texto) || (suma > 90 && suma < 110);
        if (pareceProporcion) return 'pastel';
      }

      return 'barras';
    },

    // ---- Barras horizontales: ranking simple (una etiqueta, un valor) ----
    construirGraficoBarras(datos, campoEtiqueta, campoValor) {
      const valores = datos.map((fila) => parseFloat(fila[campoValor]));
      const maxValor = Math.max(...valores, 0.0001);

      // Limita a 15 barras para que el gráfico se mantenga legible
      const filas = datos.slice(0, 15).map((fila) => {
        const valor = parseFloat(fila[campoValor]);
        return {
          etiqueta: String(fila[campoEtiqueta]),
          valor,
          porcentaje: Math.max(4, (valor / maxValor) * 100)
        };
      });

      return { tipo: 'barras', campoValor, campoEtiqueta, filas };
    },

    // ---- Líneas: serie ordenada por fecha ----
    construirGraficoLineas(datos, campoX, campoValor) {
      const puntosOrdenados = [...datos]
        .sort((a, b) => {
          const fa = new Date(a[campoX]).getTime();
          const fb = new Date(b[campoX]).getTime();
          if (isNaN(fa) || isNaN(fb)) return 0;
          return fa - fb;
        })
        .slice(0, 30); // límite para mantener el trazo legible

      const valores = puntosOrdenados.map((fila) => parseFloat(fila[campoValor]) || 0);
      const maxValor = Math.max(...valores, 0.0001);
      const minValor = Math.min(...valores, 0);
      const rango = maxValor - minValor || 1;

      const anchoSvg = 600;
      const altoSvg = 180;
      const paddingX = 24;
      const paddingY = 20;
      const n = puntosOrdenados.length;

      const puntos = puntosOrdenados.map((fila, idx) => {
        const valor = parseFloat(fila[campoValor]) || 0;
        const x = n === 1 ? anchoSvg / 2 : paddingX + (idx / (n - 1)) * (anchoSvg - paddingX * 2);
        const y = altoSvg - paddingY - ((valor - minValor) / rango) * (altoSvg - paddingY * 2);
        return {
          etiqueta: String(fila[campoX]),
          valor,
          x: Number(x.toFixed(1)),
          y: Number(y.toFixed(1))
        };
      });

      const pathD = puntos.map((p, idx) => `${idx === 0 ? 'M' : 'L'} ${p.x} ${p.y}`).join(' ');

      // Muestra pocas etiquetas si hay muchos puntos, para no saturar el eje
      const paso = Math.max(1, Math.ceil(n / 6));
      const puntosVisibles = puntos.filter((_, idx) => n <= 8 || idx === 0 || idx === n - 1 || idx % paso === 0);

      return {
        tipo: 'lineas',
        campoValor,
        campoEtiqueta: campoX,
        puntos,
        puntosVisibles,
        pathD,
        anchoSvg,
        altoSvg
      };
    },

    // ---- Pastel / dona: proporciones de un total ----
    construirGraficoPastel(datos, campoEtiqueta, campoValor) {
      const filas = datos.slice(0, 8); // más de 8 porciones deja de ser legible
      const valores = filas.map((fila) => Math.max(0, parseFloat(fila[campoValor]) || 0));
      const total = valores.reduce((a, b) => a + b, 0) || 0.0001;

      const radio = 60;
      const circunferencia = 2 * Math.PI * radio;
      let acumulado = 0;

      const segmentos = filas.map((fila, idx) => {
        const valor = valores[idx];
        const porcentaje = (valor / total) * 100;
        const largoArco = (valor / total) * circunferencia;
        const segmento = {
          etiqueta: String(fila[campoEtiqueta]),
          valor,
          porcentaje: Number(porcentaje.toFixed(1)),
          color: PALETA_GRAFICOS[idx % PALETA_GRAFICOS.length],
          dasharray: `${largoArco} ${circunferencia - largoArco}`,
          dashoffset: -((acumulado / total) * circunferencia)
        };
        acumulado += valor;
        return segmento;
      });

      return { tipo: 'pastel', campoValor, campoEtiqueta, segmentos, radio, circunferencia };
    },

    // ---- Barras comparativas / agrupadas: 2+ columnas numéricas por categoría ----
    construirGraficoComparativo(datos, campoEtiqueta, columnasNumericas) {
      const camposValor = columnasNumericas.slice(0, 4); // hasta 4 series para no saturar
      let maxValor = 0.0001;

      const filas = datos.slice(0, 10).map((fila) => {
        const valores = camposValor.map((campo, idx) => {
          const valor = parseFloat(fila[campo]) || 0;
          maxValor = Math.max(maxValor, valor);
          return { campo, valor, color: PALETA_GRAFICOS[idx % PALETA_GRAFICOS.length] };
        });
        return { etiqueta: String(fila[campoEtiqueta]), valores };
      });

      filas.forEach((fila) => {
        fila.valores.forEach((v) => {
          v.porcentaje = Math.max(4, (v.valor / maxValor) * 100);
        });
      });

      const colores = camposValor.map((campo, idx) => ({
        campo,
        color: PALETA_GRAFICOS[idx % PALETA_GRAFICOS.length]
      }));

      return { tipo: 'comparativas', campoEtiqueta, camposValor, filas, colores };
    },

    formatearNumero(valor) {
      if (valor === null || valor === undefined || isNaN(valor)) return '';
      return valor.toLocaleString('es-PE', { maximumFractionDigits: 2 });
    },

    formatearFecha(fechaISO) {
      if (!fechaISO) return '';
      return new Date(fechaISO).toLocaleString('es-PE', {
        day: '2-digit',
        month: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
      });
    },

    // Convierte un mensaje devuelto por la API al formato usado en pantalla
    mapearMensajeServidor(m) {
      contadorMensajes += 1;
      const grafico = this.prepararGrafico(m.Datos, m.Texto);
      return {
        id: contadorMensajes,
        rol: m.Rol,
        texto: m.Texto,
        datos: m.Datos,
        esError: !!m.EsError,
        grafico,
        vista: 'tabla',
        hora: new Date(m.Fecha).toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' })
      };
    },

    // ---------------------------------------------------------------
    // Historial: persistencia en base de datos (tablas Ag_Conversacion
    // y Ag_ConversacionDetalle, vía /agente/conversaciones)
    // ---------------------------------------------------------------
    cargarHistorial() {
      const apiBase = this.getApiBase();
      if (!apiBase) return;

      this.cargandoHistorial = true;
      this.axios
        .get(`${apiBase}/agente/conversaciones`)
        .then((response) => {
          const data = response.data;
          this.historial = (data && data.conversaciones) || [];
        })
        .catch((error) => {
          console.error('No se pudo cargar el historial:', error);
        })
        .finally(() => {
          this.cargandoHistorial = false;
        });
    },

    // Crea la conversación en BD la primera vez que el usuario escribe.
    // Devuelve una Promise que resuelve con el IdConversacion.
    asegurarConversacion() {
      if (this.conversacionActualId) {
        return Promise.resolve(this.conversacionActualId);
      }

      const apiBase = this.getApiBase();
      // Ajusta esto a como obtienes el usuario logueado en tu app
      // (store, localStorage, etc). Si no lo tienes a mano, se guarda como NULL.
      const usuarioReg = this.obtenerUsuarioActual();

      return this.axios
        .post(`${apiBase}/agente/conversaciones`, { UsuarioReg: usuarioReg })
        .then((response) => {
          const id = response.data && response.data.IdConversacion;
          this.conversacionActualId = id;
          // La agregamos al historial de inmediato para que se vea sin esperar el refresco
          this.historial.unshift({
            IdConversacion: id,
            UsuarioReg: usuarioReg,
            Titulo: 'Nueva conversación',
            Fecha: new Date().toISOString(),
            FechaModificacion: new Date().toISOString()
          });
          return id;
        });
    },

    // Devuelve el nombre de usuario logueado (equivalente al UsuarioReg de Cb_CajaBanco).
    // Misma fuente que usa el resto del sistema: sessionStorage.getItem("User")
    obtenerUsuarioActual() {
      try {
        return sessionStorage.getItem('User') || null;
      } catch (e) {
        return null;
      }
    },

    // Envía un mensaje ya mostrado en pantalla para que quede como constancia en BD
    registrarMensaje(conversacionId, msg) {
      const apiBase = this.getApiBase();
      return this.axios
        .post(`${apiBase}/agente/conversaciones/${conversacionId}/mensajes`, {
          Rol: msg.rol,
          Texto: msg.texto,
          Datos: msg.datos,
          EsError: msg.esError
        })
        .catch((error) => {
          console.error('No se pudo guardar el mensaje en el historial:', error);
        });
    },

    cargarConversacion(id) {
      if (id === this.conversacionActualId) return;

      const apiBase = this.getApiBase();
      this.cargando = true;
      this.axios
        .get(`${apiBase}/agente/conversaciones/${id}`)
        .then((response) => {
          const conv = response.data && response.data.conversacion;
          if (!conv) return;
          this.conversacionActualId = conv.IdConversacion;
          this.mensajes = conv.Mensajes.map(this.mapearMensajeServidor);
          this.scrollToBottom();
        })
        .catch((error) => {
          console.error('No se pudo abrir la conversación:', error);
          this.mostrarError('No se pudo abrir esa conversación');
        })
        .finally(() => {
          this.cargando = false;
        });
    },

    eliminarConversacion(id) {
      const apiBase = this.getApiBase();
      this.axios
        .delete(`${apiBase}/agente/conversaciones/${id}`)
        .then(() => {
          this.historial = this.historial.filter((c) => c.IdConversacion !== id);
          if (id === this.conversacionActualId) {
            this.nuevaConversacion();
          }
        })
        .catch((error) => {
          console.error('No se pudo eliminar la conversación:', error);
          this.mostrarError('No se pudo eliminar esa conversación');
        });
    },

    nuevaConversacion() {
      this.mensajes = [this.crearMensaje('agente', MENSAJE_BIENVENIDA)];
      this.conversacionActualId = null;
      this.scrollToBottom();
      this.$nextTick(() => {
        if (this.$refs.inputPregunta) this.$refs.inputPregunta.focus();
      });
    },

    limpiarChat() {
      this.nuevaConversacion();
    },

    // ---------------------------------------------------------------

    getApiBase() {
      // Evita pantalla en blanco si "config" global no está definido en este contexto
      if (typeof config !== 'undefined' && config.API) {
        return config.API;
      }
      console.error('config.API no está definido. Revisa config/dev.env.js y config/prod.env.js');
      return '';
    },

    enviarConsulta() {
      const preguntaTexto = this.pregunta.trim();
      if (!preguntaTexto || this.cargando) return;

      const apiBase = this.getApiBase();
      if (!apiBase) {
        this.mensajes.push(
          this.crearMensaje('agente', 'Error de configuración: no se encontró la URL de la API.', null, true)
        );
        return;
      }

      const mensajeUsuario = this.crearMensaje('usuario', preguntaTexto);
      this.mensajes.push(mensajeUsuario);
      this.pregunta = '';
      this.cargando = true;
      this.scrollToBottom();

      // Crea la conversación en BD si es la primera pregunta, y deja
      // constancia de la pregunta del usuario.
      this.asegurarConversacion()
        .then((conversacionId) => {
          this.registrarMensaje(conversacionId, mensajeUsuario);
          return conversacionId;
        })
        .catch((error) => console.error('No se pudo crear la conversación:', error));

      this.axios
        .post(`${apiBase}/agente/consulta`, { pregunta: preguntaTexto })
        .then((response) => {
          const data = response.data;

          if (data && data.status) {
            this.mensajes.push(
              this.crearMensaje('agente', data.respuesta || 'No se encontró información.', data.datos, false, preguntaTexto)
            );
          } else {
            const msg = (data && data.message) || 'No se pudo procesar la consulta';
            this.mensajes.push(this.crearMensaje('agente', msg, null, true));
            this.mostrarError(msg);
          }
        })
        .catch((error) => {
          console.error(error);
          const errorMsg =
            (error.response && error.response.data && error.response.data.message) ||
            'Error de conexión con el agente';

          this.mensajes.push(this.crearMensaje('agente', errorMsg, null, true));
          this.mostrarError(errorMsg);
        })
        .finally(() => {
          this.cargando = false;
          this.scrollToBottom();

          // Deja constancia en BD de la última respuesta del agente (o error)
          const ultimoMensaje = this.mensajes[this.mensajes.length - 1];
          if (this.conversacionActualId && ultimoMensaje.rol === 'agente') {
            this.registrarMensaje(this.conversacionActualId, ultimoMensaje);
          }

          this.$nextTick(() => {
            if (this.$refs.inputPregunta) this.$refs.inputPregunta.focus();
          });
        });
    },

    // Centraliza el manejo de notificaciones de error para no romper
    // la página si $toasted o $swal no están instalados/registrados
    mostrarError(msg) {
      if (this.$toasted && typeof this.$toasted.error === 'function') {
        this.$toasted.error(msg);
      } else if (this.$swal) {
        this.$swal({ icon: 'error', title: 'Error del Agente', text: msg });
      } else {
        console.warn('Error del Agente:', msg);
      }
    },

    scrollToBottom() {
      this.$nextTick(() => {
        if (this.$refs.chatBox) {
          this.$refs.chatBox.scrollTop = this.$refs.chatBox.scrollHeight;
        }
      });
    }
  }
};
</script>

<style scoped>
:root {
  --agente-ink: #1b1f3b;
  --agente-accent: #6c5ce7;
  --agente-accent-2: #2ec4b6;
  --agente-bg: #f5f5f8;
}

.agente-page {
  padding: 0;
  min-height: 100vh;
  width: 100%;
  box-sizing: border-box;
  display: flex;
  justify-content: center;
  align-items: stretch;
  background: linear-gradient(180deg, #f5f5f8 0%, #eeeef4 100%);
}

/* ---------- Panel de historial ---------- */
.historial-panel {
  width: 260px;
  min-width: 260px;
  height: 100vh;
  background: #fafafc;
  border-right: 1px solid #eaeaf0;
  display: flex;
  flex-direction: column;
  box-sizing: border-box;
  transition: width 0.2s ease, min-width 0.2s ease;
}

.historial-panel.colapsado {
  width: 52px;
  min-width: 52px;
}

.historial-header {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 16px 14px;
  border-bottom: 1px solid #eaeaf0;
}

.btn-toggle-panel {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  border: 1px solid #e2e2ea;
  background: #fff;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6a6f85;
  flex-shrink: 0;
}

.btn-toggle-panel:hover {
  border-color: var(--agente-accent);
  color: var(--agente-accent);
}

.historial-titulo {
  font-weight: 700;
  font-size: 0.85rem;
  color: var(--agente-ink);
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.btn-nueva-conversacion {
  margin: 12px 14px;
  padding: 8px 12px;
  border-radius: 10px;
  border: 1px dashed #cfcfe0;
  background: #fff;
  color: var(--agente-accent);
  font-weight: 600;
  font-size: 0.82rem;
  cursor: pointer;
  text-align: left;
  transition: background-color 0.15s ease, border-color 0.15s ease;
}

.btn-nueva-conversacion:hover {
  background: #f2f0fe;
  border-color: var(--agente-accent);
}

.historial-lista {
  flex: 1 1 auto;
  overflow-y: auto;
  padding: 0 8px 12px;
}

.historial-lista::-webkit-scrollbar {
  width: 6px;
}
.historial-lista::-webkit-scrollbar-thumb {
  background: #d5d5e0;
  border-radius: 8px;
}

.historial-vacio {
  padding: 16px 10px;
  color: #a4a8ba;
  text-align: center;
}

.historial-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 6px;
  padding: 10px 10px;
  border-radius: 10px;
  cursor: pointer;
  margin-bottom: 2px;
  transition: background-color 0.15s ease;
}

.historial-item:hover {
  background-color: #f0f0f7;
}

.historial-item.activo {
  background-color: #ece9fd;
}

.historial-item-texto {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.historial-item-titulo {
  font-size: 0.83rem;
  color: var(--agente-ink);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.historial-item-fecha {
  color: #a4a8ba;
  font-size: 0.7rem;
}

.btn-borrar-historial {
  border: none;
  background: transparent;
  color: #c3c6d4;
  cursor: pointer;
  font-size: 0.75rem;
  flex-shrink: 0;
  padding: 2px 4px;
  border-radius: 4px;
}

.btn-borrar-historial:hover {
  color: #e0575f;
  background-color: #fdf0f0;
}

@media (max-width: 768px) {
  .historial-panel {
    width: 210px;
    min-width: 210px;
  }
  .historial-panel.colapsado {
    width: 44px;
    min-width: 44px;
  }
}

.agente-card {
  width: 100%;
  max-width: none;
  height: 100vh;
  background: #ffffff;
  border-radius: 0;
  box-shadow: none;
  padding: 20px 24px;
  display: flex;
  flex-direction: column;
  border: none;
  box-sizing: border-box;
}

/* ---------- Header ---------- */
.agente-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #eee;
  padding-bottom: 16px;
  margin-bottom: 14px;
}

.agente-header-title {
  display: flex;
  align-items: center;
  gap: 14px;
}

.agente-avatar {
  position: relative;
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: linear-gradient(135deg, #6c5ce7 0%, #2ec4b6 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  box-shadow: 0 4px 14px rgba(108, 92, 231, 0.35);
}

.agente-avatar-icon {
  color: #fff;
  font-size: 1.3rem;
  z-index: 1;
}

.agente-avatar-glow {
  position: absolute;
  inset: -3px;
  border-radius: 14px;
  background: linear-gradient(135deg, #6c5ce7, #2ec4b6);
  filter: blur(8px);
  opacity: 0.5;
  z-index: 0;
}

.agente-header-title h5 {
  color: var(--agente-ink);
  font-weight: 700;
  letter-spacing: -0.01em;
}

.agente-subtitle {
  color: #8a8fa3;
  font-size: 0.8rem;
}

.header-acciones {
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-modo-nocturno {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  border: 1px solid #e2e2ea;
  background: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 0.95rem;
  line-height: 1;
  transition: transform 0.2s ease, background-color 0.2s ease, border-color 0.2s ease;
}

.btn-modo-nocturno:hover {
  transform: rotate(-12deg);
  border-color: var(--agente-accent);
}

.btn-modo-nocturno.activo {
  background: #2a2d45;
  border-color: #2a2d45;
}

.btn-modo-nocturno:focus-visible {
  outline: 2px solid var(--agente-accent);
  outline-offset: 2px;
}

.btn-limpiar {
  border-radius: 20px;
  font-size: 0.8rem;
  color: #6a6f85;
  border-color: #e2e2ea;
}

.btn-limpiar:hover {
  background-color: #f5f5f8;
  color: var(--agente-ink);
}

.icon-limpiar {
  display: inline-block;
  margin-right: 2px;
}

/* ---------- Chat area ---------- */
.chat-container {
  flex: 1 1 auto;
  min-height: 0;
  overflow-y: auto;
  padding: 16px 10px;
  background-color: var(--agente-bg);
  border-radius: 12px;
  margin-bottom: 14px;
  scroll-behavior: smooth;
}

.chat-container::-webkit-scrollbar {
  width: 8px;
}
.chat-container::-webkit-scrollbar-thumb {
  background: #d5d5e0;
  border-radius: 8px;
}
.chat-container::-webkit-scrollbar-track {
  background: transparent;
}

.chat-vacio {
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #a4a8ba;
}

.chat-vacio-icon {
  font-size: 1.8rem;
  color: var(--agente-accent);
  margin-bottom: 6px;
}

.chat-vacio small {
  color: #b5b8c6;
}

.chat-row {
  display: flex;
  align-items: flex-end;
  gap: 8px;
  margin-bottom: 14px;
  animation: fadeSlideIn 0.25s ease;
}

@keyframes fadeSlideIn {
  from {
    opacity: 0;
    transform: translateY(6px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.chat-row.usuario {
  justify-content: flex-end;
}

.chat-row.agente {
  justify-content: flex-start;
}

.bubble-avatar {
  width: 30px;
  height: 30px;
  min-width: 30px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  font-weight: 700;
  color: #fff;
  background: linear-gradient(135deg, #6c5ce7 0%, #2ec4b6 100%);
}

.bubble-avatar.is-error {
  background: #e0575f;
}

.bubble-avatar.usuario-avatar {
  background: #1b1f3b;
}

.message-content {
  max-width: 100%;
  padding: 10px 16px;
  border-radius: 14px;
}

.chat-message {
  max-width: 78%;
}

.message-autor {
  display: block;
  font-size: 0.72rem;
  opacity: 0.7;
  margin-bottom: 2px;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.texto-mensaje {
  white-space: pre-wrap;
  word-break: break-word;
  line-height: 1.45;
  font-size: 0.94rem;
}

.hora-mensaje {
  display: block;
  margin-top: 4px;
  font-size: 0.68rem;
  opacity: 0.55;
}

.chat-row.usuario .message-content {
  background: linear-gradient(135deg, #6c5ce7 0%, #7f6bf0 100%);
  color: white;
  border-bottom-right-radius: 4px;
}

.chat-row.agente .message-content {
  background-color: #ffffff;
  color: #2a2d3e;
  border: 1px solid #e9e9f1;
  border-bottom-left-radius: 4px;
}

.chat-row.agente .chat-message.es-error .message-content {
  background-color: #fdf0f0;
  border-color: #f3caca;
  color: #a3363d;
}

.tabla-wrapper {
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid #ececf3;
}

.tabla-resultados {
  margin-bottom: 0 !important;
  font-size: 0.85rem;
}

/* ---------- Toggle Tabla / Gráfico ---------- */
.vista-toggle {
  display: inline-flex;
  gap: 4px;
  background: #f0f0f7;
  border-radius: 20px;
  padding: 3px;
  margin: 6px 0 8px;
}

.btn-vista {
  border: none;
  background: transparent;
  padding: 5px 12px;
  border-radius: 16px;
  font-size: 0.76rem;
  font-weight: 600;
  color: #6a6f85;
  cursor: pointer;
  transition: background-color 0.15s ease, color 0.15s ease;
}

.btn-vista.activo {
  background: #ffffff;
  color: var(--agente-accent);
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
}

/* ---------- Contenedor común de gráficos ---------- */
.grafico-contenedor {
  padding: 4px 2px 2px;
}

/* ---------- Gráfico de barras (ranking) ---------- */
.grafico-barras {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.grafico-fila {
  display: grid;
  grid-template-columns: 90px 1fr auto;
  align-items: center;
  gap: 8px;
}

.grafico-etiqueta {
  font-size: 0.78rem;
  color: #4a4e63;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.grafico-barra-track {
  height: 16px;
  background: #eeeef4;
  border-radius: 8px;
  overflow: hidden;
}

.grafico-barra {
  height: 100%;
  border-radius: 8px;
  background: linear-gradient(90deg, #6c5ce7 0%, #2ec4b6 100%);
  transition: width 0.5s ease;
}

.grafico-valor {
  font-size: 0.78rem;
  font-weight: 700;
  color: var(--agente-ink);
  min-width: 46px;
  text-align: right;
}

/* ---------- Gráfico de líneas (fecha) ---------- */
.grafico-lineas {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.grafico-svg-lineas {
  width: 100%;
  height: 150px;
  overflow: visible;
}

.linea-trazo {
  stroke: var(--agente-accent);
  stroke-width: 2.5;
  stroke-linejoin: round;
  stroke-linecap: round;
}

.linea-punto {
  fill: #ffffff;
  stroke: var(--agente-accent-2);
  stroke-width: 2;
}

.grafico-lineas-etiquetas {
  display: flex;
  justify-content: space-between;
  padding: 0 4px;
}

.linea-etiqueta {
  font-size: 0.68rem;
  color: #8a8fa3;
  white-space: nowrap;
}

/* ---------- Gráfico de pastel / dona ---------- */
.grafico-pastel {
  display: flex;
  align-items: center;
  gap: 18px;
  flex-wrap: wrap;
}

.grafico-svg-pastel {
  width: 130px;
  height: 130px;
  flex-shrink: 0;
}

.grafico-svg-pastel circle {
  transition: stroke-dasharray 0.5s ease;
}

.grafico-pastel-leyenda {
  display: flex;
  flex-direction: column;
  gap: 6px;
  flex: 1 1 140px;
  min-width: 140px;
}

.leyenda-item {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.78rem;
  color: #4a4e63;
}

.leyenda-color {
  width: 10px;
  height: 10px;
  border-radius: 3px;
  flex-shrink: 0;
}

.leyenda-etiqueta {
  flex: 1 1 auto;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.leyenda-valor {
  font-weight: 700;
  color: var(--agente-ink);
}

/* ---------- Gráfico comparativo / agrupado ---------- */
.grafico-comparativo {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.grafico-comparativo-leyenda {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 4px;
}

.grafico-comparativo-fila {
  display: grid;
  grid-template-columns: 90px 1fr auto;
  align-items: center;
  gap: 8px;
}

.grafico-comparativo-barras {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.comparativo-track {
  height: 10px;
}

.grafico-comparativo-valores {
  display: flex;
  flex-direction: column;
  gap: 3px;
  align-items: flex-end;
}

.grafico-valor-mini {
  font-size: 0.7rem;
  font-weight: 700;
  color: var(--agente-ink);
  line-height: 10px;
}

/* ---------- Typing indicator ---------- */
.typing-content {
  display: flex;
  align-items: center;
  gap: 10px;
}

.typing-dots {
  display: inline-flex;
  gap: 4px;
}

.typing-dots span {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--agente-accent);
  animation: bounceDot 1.2s infinite ease-in-out;
}

.typing-dots span:nth-child(2) {
  animation-delay: 0.15s;
}
.typing-dots span:nth-child(3) {
  animation-delay: 0.3s;
}

@keyframes bounceDot {
  0%, 60%, 100% {
    transform: translateY(0);
    opacity: 0.5;
  }
  30% {
    transform: translateY(-4px);
    opacity: 1;
  }
}

.typing-label {
  font-size: 0.82rem;
  color: #8a8fa3;
}

/* ---------- Input area ---------- */
.form-consulta {
  margin-top: 4px;
}

.input-group-consulta {
  background: var(--agente-bg);
  border-radius: 26px;
  padding: 4px 4px 4px 18px;
  border: 1px solid #e5e5ee;
}

.input-pregunta {
  border: none !important;
  background: transparent !important;
  box-shadow: none !important;
  font-size: 0.94rem;
}

.input-pregunta:focus {
  box-shadow: none !important;
}

.btn-microfono {
  width: 40px;
  height: 40px;
  min-width: 40px;
  border-radius: 50%;
  border: none;
  background: transparent;
  color: #6a6f85;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 1.05rem;
  margin-right: 2px;
  transition: background-color 0.15s ease, color 0.15s ease, transform 0.15s ease;
}

.btn-microfono:hover:not(:disabled) {
  background-color: #eceafc;
  color: var(--agente-accent);
}

.btn-microfono:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.btn-microfono.activo {
  background: linear-gradient(135deg, #6c5ce7 0%, #2ec4b6 100%);
  color: #fff;
  animation: pulsoMic 1.4s ease-in-out infinite;
}

@keyframes pulsoMic {
  0%, 100% {
    box-shadow: 0 0 0 0 rgba(108, 92, 231, 0.45);
  }
  50% {
    box-shadow: 0 0 0 8px rgba(108, 92, 231, 0);
  }
}

.mic-icon {
  line-height: 1;
}

.mic-ondas {
  display: inline-flex;
  align-items: flex-end;
  gap: 2px;
  height: 14px;
}

.mic-ondas span {
  width: 3px;
  background: #fff;
  border-radius: 2px;
  animation: ondaMic 0.9s ease-in-out infinite;
}

.mic-ondas span:nth-child(1) {
  height: 6px;
  animation-delay: 0s;
}
.mic-ondas span:nth-child(2) {
  height: 14px;
  animation-delay: 0.15s;
}
.mic-ondas span:nth-child(3) {
  height: 9px;
  animation-delay: 0.3s;
}

@keyframes ondaMic {
  0%, 100% {
    transform: scaleY(0.5);
  }
  50% {
    transform: scaleY(1);
  }
}

.error-voz {
  display: block;
  margin-top: 6px;
  color: #a3363d;
  font-size: 0.76rem;
  padding-left: 4px;
}

.btn-enviar {
  border-radius: 22px !important;
  padding: 8px 20px !important;
  background: linear-gradient(135deg, #6c5ce7 0%, #2ec4b6 100%) !important;
  border: none !important;
  font-weight: 600;
  font-size: 0.88rem;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.btn-enviar:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 14px rgba(108, 92, 231, 0.35);
}

.btn-enviar:disabled {
  opacity: 0.5;
}

.spinner-enviar {
  display: inline-block;
  width: 14px;
  height: 14px;
  border: 2px solid rgba(255, 255, 255, 0.4);
  border-top-color: #fff;
  border-radius: 50%;
  animation: girarSpinner 0.7s linear infinite;
}

@keyframes girarSpinner {
  to {
    transform: rotate(360deg);
  }
}

/* ---------- Accessibility ---------- */
@media (prefers-reduced-motion: reduce) {
  .chat-row,
  .typing-dots span,
  .spinner-enviar,
  .btn-enviar {
    animation: none !important;
    transition: none !important;
  }
}

@media (max-width: 576px) {
  .agente-card {
    padding: 14px;
  }
  .chat-message {
    max-width: 88%;
  }
}

/* ---------- Modo nocturno (solo esta ventana) ---------- */
.agente-page.modo-nocturno {
  min-height: 100vh;
  background: linear-gradient(180deg, #14151f 0%, #0e0f17 100%);
  transition: background 0.25s ease;
}

.modo-nocturno .historial-panel {
  background: #171827;
  border-right-color: rgba(255, 255, 255, 0.06);
}

.modo-nocturno .historial-header {
  border-bottom-color: rgba(255, 255, 255, 0.08);
}

.modo-nocturno .btn-toggle-panel {
  background: #2a2d45;
  border-color: #383c5c;
  color: #b7bad0;
}

.modo-nocturno .historial-titulo {
  color: #f1f1f6;
}

.modo-nocturno .btn-nueva-conversacion {
  background: #1f2033;
  border-color: #3a3d5c;
  color: #a89bff;
}

.modo-nocturno .btn-nueva-conversacion:hover {
  background: #262841;
}

.modo-nocturno .historial-item:hover {
  background-color: #20223a;
}

.modo-nocturno .historial-item.activo {
  background-color: #2a2c4d;
}

.modo-nocturno .historial-item-titulo {
  color: #e4e5f1;
}

.modo-nocturno .historial-item-fecha {
  color: #6b6f8c;
}

.modo-nocturno .btn-borrar-historial {
  color: #565a75;
}

.modo-nocturno .agente-card {
  background: #1b1c29;
  border-color: rgba(255, 255, 255, 0.06);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.45);
}

.modo-nocturno .agente-header {
  border-bottom-color: rgba(255, 255, 255, 0.08);
}

.modo-nocturno .agente-header-title h5 {
  color: #f1f1f6;
}

.modo-nocturno .agente-subtitle {
  color: #8489a3;
}

.modo-nocturno .btn-modo-nocturno {
  background: #2a2d45;
  border-color: #383c5c;
}

.modo-nocturno .btn-limpiar {
  color: #b7bad0;
  border-color: #383c5c;
}

.modo-nocturno .btn-limpiar:hover {
  background-color: #262841;
  color: #f1f1f6;
}

.modo-nocturno .chat-container {
  background-color: #131420;
}

.modo-nocturno .chat-container::-webkit-scrollbar-thumb {
  background: #34364d;
}

.modo-nocturno .chat-vacio {
  color: #6b6f8c;
}

.modo-nocturno .chat-vacio small {
  color: #565a75;
}

.modo-nocturno .chat-row.agente .message-content {
  background-color: #20223a;
  color: #e4e5f1;
  border-color: #2e3050;
}

.modo-nocturno .chat-row.usuario .message-content {
  background: linear-gradient(135deg, #7a68f2 0%, #5546c8 100%);
}

.modo-nocturno .chat-row.agente .chat-message.es-error .message-content {
  background-color: #34202a;
  border-color: #5c2c3a;
  color: #f2a3b0;
}

.modo-nocturno .message-autor {
  opacity: 0.6;
}

.modo-nocturno .hora-mensaje {
  opacity: 0.45;
}

.modo-nocturno .tabla-wrapper {
  border-color: #2e3050;
}

.modo-nocturno .tabla-resultados {
  color: #1b1c29;
}

.modo-nocturno .vista-toggle {
  background: #20223a;
}

.modo-nocturno .btn-vista {
  color: #8489a3;
}

.modo-nocturno .btn-vista.activo {
  background: #2e3050;
  color: #a89bff;
}

.modo-nocturno .grafico-etiqueta {
  color: #c7c9dd;
}

.modo-nocturno .grafico-barra-track {
  background: #262841;
}

.modo-nocturno .grafico-valor {
  color: #f1f1f6;
}

.modo-nocturno .linea-etiqueta {
  color: #7f83a0;
}

.modo-nocturno .linea-punto {
  fill: #1b1c29;
}

.modo-nocturno .leyenda-item {
  color: #c7c9dd;
}

.modo-nocturno .leyenda-valor {
  color: #f1f1f6;
}

.modo-nocturno .grafico-valor-mini {
  color: #f1f1f6;
}

.modo-nocturno .typing-label {
  color: #7f83a0;
}

.modo-nocturno .input-group-consulta {
  background: #20223a;
  border-color: #2e3050;
}

.modo-nocturno .input-pregunta {
  color: #f1f1f6 !important;
}

.modo-nocturno .input-pregunta::placeholder {
  color: #6b6f8c;
}

.modo-nocturno .btn-microfono {
  color: #8489a3;
}

.modo-nocturno .btn-microfono:hover:not(:disabled) {
  background-color: #262841;
  color: #a89bff;
}

.modo-nocturno .error-voz {
  color: #f2a3b0;
}
</style>