<template>
  <div class="barcode-camera-scanner">
    <div ref="viewport" class="barcode-camera-viewport"></div>
    <div v-if="cameraLoading && !cameraError" class="barcode-camera-loading">
      <i class="fas fa-circle-notch fa-spin"></i>
      <span>Iniciando camara...</span>
    </div>
    <button v-if="torchAvailable" type="button" class="barcode-camera-torch"
      :class="{ 'is-active': torchActive }" @click="toggleTorch">
      <i class="fas fa-lightbulb"></i>
      <span>{{ torchActive ? 'Apagar luz' : 'Encender luz' }}</span>
    </button>
    <div v-if="cameraError" class="barcode-camera-error">
      <i class="fas fa-exclamation-triangle"></i>
      <span>{{ cameraError }}</span>
      <button type="button" @click="retryScanner">Reintentar</button>
    </div>
  </div>
</template>

<script>
import Quagga from "quagga";

export default {
  name: "BarcodeCameraScanner",
  props: {
    onDetected: {
      type: Function,
      required: true
    },
    readerTypes: {
      type: Array,
      default: () => [
        "code_128_reader",
        "ean_reader",
        "ean_8_reader",
        "upc_reader",
        "upc_e_reader"
      ]
    },
    readerSize: {
      type: Object,
      default: () => ({ width: 1280, height: 720 })
    }
  },
  data() {
    return {
      cameraError: "",
      cameraLoading: true,
      torchAvailable: false,
      torchActive: false,
      activeTrack: null,
      scannerStarted: false,
      detectionLocked: false,
      lastCandidate: "",
      lastCandidateAt: 0,
      candidateHits: 0,
      startFrame: null,
      playbackTimer: null
    };
  },
  mounted() {
    this.scheduleStart();
  },
  beforeDestroy() {
    this.stopScanner();
  },
  methods: {
    scheduleStart() {
      this.startFrame = window.requestAnimationFrame(() => {
        this.startFrame = window.requestAnimationFrame(() => {
          if (!this._isBeingDestroyed && !this._isDestroyed) this.startScanner();
        });
      });
    },
    startScanner() {
      if (!window.isSecureContext || !window.navigator.mediaDevices ||
          typeof window.navigator.mediaDevices.getUserMedia !== "function") {
        this.cameraLoading = false;
        this.cameraError = "La camara requiere una conexion HTTPS y un navegador compatible.";
        return;
      }

      const viewport = this.$refs.viewport;
      if (!viewport || viewport.clientWidth < 2 || viewport.clientHeight < 2) {
        this.cameraLoading = false;
        this.cameraError = "No se pudo preparar la vista de la camara.";
        return;
      }

      this.cameraError = "";
      this.cameraLoading = true;
      const processorCount = Number(window.navigator.hardwareConcurrency) || 4;
      const workerCount = Math.max(2, Math.min(4, processorCount - 1));
      const width = Number(this.readerSize.width) || 1280;
      const height = Number(this.readerSize.height) || 720;
      const config = {
        inputStream: {
          type: "LiveStream",
          target: this.$refs.viewport,
          area: {
            top: "24%",
            right: "6%",
            left: "6%",
            bottom: "24%"
          },
          constraints: {
            width: { ideal: width },
            height: { ideal: height },
            facingMode: { ideal: "environment" }
          }
        },
        locator: {
          patchSize: "medium",
          halfSample: false
        },
        numOfWorkers: workerCount,
        frequency: 20,
        decoder: {
          readers: this.readerTypes,
          multiple: false
        },
        locate: true
      };

      Quagga.init(config, error => {
        if (error) {
          this.cameraLoading = false;
          this.cameraError = this.getCameraErrorMessage(error);
          return;
        }
        if (this._isBeingDestroyed || this._isDestroyed) {
          Quagga.stop();
          return;
        }
        Quagga.onDetected(this.handleDetected);
        Quagga.start();
        this.scannerStarted = true;
        this.ensureVideoPlayback();
        window.setTimeout(this.optimizeCameraTrack, 250);
      });
    },
    ensureVideoPlayback() {
      const viewport = this.$refs.viewport;
      const video = viewport ? viewport.querySelector("video") : null;
      if (!video) {
        this.cameraLoading = false;
        this.cameraError = "No se pudo crear la vista de la camara.";
        return;
      }

      video.muted = true;
      video.setAttribute("muted", "");
      video.setAttribute("autoplay", "");
      video.setAttribute("playsinline", "");
      video.setAttribute("webkit-playsinline", "");

      const markReady = () => {
        if (video.videoWidth > 0 && video.videoHeight > 0) {
          this.cameraLoading = false;
          this.cameraError = "";
        }
      };
      video.addEventListener("playing", markReady, { once: true });
      video.addEventListener("canplay", markReady, { once: true });

      const playback = video.play();
      if (playback && typeof playback.catch === "function") {
        playback.catch(() => {
          this.cameraLoading = false;
          this.cameraError = "El navegador bloqueo la camara. Pulse Reintentar.";
        });
      }

      this.playbackTimer = window.setTimeout(() => {
        if (this.cameraLoading) {
          markReady();
          if (this.cameraLoading) {
            this.cameraLoading = false;
            this.cameraError = "La camara no pudo mostrar imagen. Pulse Reintentar.";
          }
        }
      }, 4000);
      markReady();
    },
    retryScanner() {
      this.stopScanner();
      this.cameraError = "";
      this.cameraLoading = true;
      this.$nextTick(this.scheduleStart);
    },
    stopScanner() {
      if (this.startFrame !== null) {
        window.cancelAnimationFrame(this.startFrame);
        this.startFrame = null;
      }
      if (this.playbackTimer !== null) {
        window.clearTimeout(this.playbackTimer);
        this.playbackTimer = null;
      }
      try {
        Quagga.offDetected(this.handleDetected);
        if (this.activeTrack && this.torchActive) {
          this.activeTrack.applyConstraints({ advanced: [{ torch: false }] }).catch(() => {});
        }
        if (this.scannerStarted) Quagga.stop();
      } catch (error) {
        // La camara puede haberse detenido al cerrar rapidamente el modal.
      }
      this.scannerStarted = false;
      this.activeTrack = null;
      this.torchAvailable = false;
      this.torchActive = false;
      const viewport = this.$refs.viewport;
      if (viewport) viewport.innerHTML = "";
    },
    optimizeCameraTrack() {
      try {
        const cameraAccess = Quagga.CameraAccess;
        const track = cameraAccess && cameraAccess.getActiveTrack
          ? cameraAccess.getActiveTrack()
          : null;
        if (!track) return;

        this.activeTrack = track;
        const capabilities = track.getCapabilities ? track.getCapabilities() : {};
        const advanced = [];
        if (capabilities.focusMode && capabilities.focusMode.indexOf("continuous") !== -1) {
          advanced.push({ focusMode: "continuous" });
        }
        if (advanced.length) track.applyConstraints({ advanced }).catch(() => {});
        this.torchAvailable = Boolean(capabilities.torch);
      } catch (error) {
        this.torchAvailable = false;
      }
    },
    toggleTorch() {
      if (!this.activeTrack || !this.torchAvailable) return;
      const nextState = !this.torchActive;
      this.activeTrack.applyConstraints({ advanced: [{ torch: nextState }] })
        .then(() => { this.torchActive = nextState; })
        .catch(() => { this.torchAvailable = false; });
    },
    handleDetected(result) {
      if (this.detectionLocked || !result || !result.codeResult) return;
      const code = String(result.codeResult.code || "").trim();
      if (code.length < 3) return;

      const now = Date.now();
      if (code === this.lastCandidate && now - this.lastCandidateAt < 900) {
        this.candidateHits += 1;
      } else {
        this.lastCandidate = code;
        this.candidateHits = 1;
      }
      this.lastCandidateAt = now;

      const decoded = result.codeResult.decodedCodes || [];
      const errors = decoded
        .map(item => Number(item && item.error))
        .filter(value => Number.isFinite(value));
      const averageError = errors.length
        ? errors.reduce((total, value) => total + value, 0) / errors.length
        : 1;

      // Una lectura muy limpia se acepta al instante; las demas se confirman
      // en dos cuadros consecutivos para evitar codigos falsos sin demoras.
      if (averageError > 0.12 && this.candidateHits < 2) return;

      this.detectionLocked = true;
      this.onDetected(result);
    },
    getCameraErrorMessage(error) {
      const name = error && error.name ? error.name : "";
      if (name === "NotAllowedError" || name === "PermissionDeniedError") {
        return "Permita el acceso a la camara para escanear.";
      }
      if (name === "NotFoundError" || name === "DevicesNotFoundError") {
        return "No se encontro una camara disponible.";
      }
      return "No se pudo iniciar la camara. Cierre y vuelva a intentar.";
    }
  }
};
</script>

<style>
.barcode-camera-scanner,
.barcode-camera-viewport {
  position: relative;
  width: 100%;
  height: 100%;
  overflow: hidden;
  background: #020b16;
}

.barcode-camera-viewport video,
.barcode-camera-viewport canvas {
  position: absolute;
  inset: 0;
  display: block;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.barcode-camera-torch {
  position: absolute;
  z-index: 9;
  top: 12px;
  right: 12px;
  display: inline-flex;
  min-height: 38px;
  padding: 0 12px;
  align-items: center;
  gap: 7px;
  border: 1px solid rgba(255, 255, 255, .32);
  border-radius: 999px;
  color: #fff;
  background: rgba(3, 56, 47, .88);
  box-shadow: 0 6px 16px rgba(0, 0, 0, .25);
  font-size: 11px;
  font-weight: 900;
  text-transform: uppercase;
}

.barcode-camera-torch.is-active {
  color: #173247;
  background: #ffb20b;
}

.barcode-camera-error {
  position: absolute;
  z-index: 10;
  inset: 0;
  display: flex;
  padding: 28px;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  gap: 12px;
  color: #fff;
  background: #071522;
  font-weight: 800;
  text-align: center;
}

.barcode-camera-loading {
  position: absolute;
  z-index: 8;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  gap: 12px;
  color: #fff;
  background: #020b16;
  font-weight: 800;
}

.barcode-camera-loading i {
  color: #ffb20b;
  font-size: 28px;
}

.barcode-camera-error i {
  color: #ffb20b;
  font-size: 28px;
}

.barcode-camera-error button {
  min-height: 40px;
  padding: 0 18px;
  border: 0;
  border-radius: 999px;
  color: #173247;
  background: #ffb20b;
  font-weight: 900;
}
</style>
