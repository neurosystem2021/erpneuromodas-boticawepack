<template>
  <component :is="embedded ? 'section' : 'b-modal'" :class="embedded ? 'project-inline-scanner' : ''" :visible="true" :no-close-on-backdrop="continuous" title="ESCANEAR CÓDIGO" hide-footer centered modal-class="inventory-camera-modal" @shown="start" @hidden="$emit('close')">
    <p class="inventory-camera-help">CENTRA EL CÓDIGO, EVITA REFLEJOS Y MANTÉN EL CELULAR QUIETO.</p>
    <div ref="viewport" class="inventory-camera-viewport"><video v-show="native" ref="video" autoplay muted playsinline></video><div class="inventory-camera-guide"></div></div>
    <p role="status" class="inventory-camera-status">{{ status }}</p>
    <b-alert :show="!!error" variant="danger">{{ error }}</b-alert>
    <div class="inventory-camera-tools">
      <button v-if="canTorch" type="button" @click="toggleTorch">{{ torch ? 'APAGAR LINTERNA' : 'LINTERNA' }}</button>
      <label v-if="zoomMax > 1">ZOOM <input type="range" :min="zoomMin" :max="zoomMax" :step="zoomStep" v-model.number="zoom" @change="applyZoom"></label>
      <button type="button" @click="$emit('close')">CERRAR</button>
    </div>
  </component>
</template>
<script>
import Quagga from 'quagga';
import { createScanConsensus } from '@/utils/inventoryScan';
export default {
  props:{continuous:{type:Boolean,default:false},embedded:{type:Boolean,default:false}},
  data() { return { native: false, error: '', status: 'INICIANDO CÁMARA…', canTorch: false, torch: false, zoom: 1, zoomMin: 1, zoomMax: 1, zoomStep: .1 }; },
  created() { this._generation = 0; this._accept = createScanConsensus(); this._handler = null; this._stream = null; },
  mounted() { document.addEventListener('visibilitychange', this.visibilityChanged); if(this.embedded)this.$nextTick(()=>this.start()); },
  beforeDestroy() { this.stop(); document.removeEventListener('visibilitychange', this.visibilityChanged); },
  methods: {
    visibilityChanged() { if(this.continuous){if(document.hidden){this.stop();this._paused=true;}else if(this._paused){this._paused=false;this.start();}}else if(document.hidden)this.$emit('close'); },
    async start() {
      const generation = ++this._generation;
      if (!window.isSecureContext || !navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        this.error = 'ABRE EL SISTEMA CON HTTPS PARA USAR LA CÁMARA DEL CELULAR. TAMBIÉN PUEDES ESCRIBIR EL CÓDIGO O USAR UN LECTOR USB.'; this.status = 'CÁMARA NO DISPONIBLE'; return;
      }
      try {
        let formats = [];
        if (window.BarcodeDetector) {
          try { formats = (await window.BarcodeDetector.getSupportedFormats()).filter(format => ['ean_13', 'ean_8', 'upc_a', 'upc_e', 'code_128', 'code_39', 'code_93', 'itf', 'codabar', 'qr_code'].includes(format)); } catch (_) {}
        }
        if (generation !== this._generation) return;
        if (formats.length) {
          const detector = new window.BarcodeDetector({ formats });
          const stream = await navigator.mediaDevices.getUserMedia({ audio: false, video: { facingMode: { ideal: 'environment' }, width: { ideal: 1920 }, height: { ideal: 1080 } } });
          if (generation !== this._generation) { stream.getTracks().forEach(track => track.stop()); return; }
          this.native = true; this._stream = stream; this.$refs.video.srcObject = stream;
          await this.$refs.video.play();
          if (generation !== this._generation) return;
          await this.configureTrack(stream.getVideoTracks()[0]);
          this.status = 'BUSCANDO · VALIDACIÓN DE 3 LECTURAS';
          const read = async () => {
            if (generation !== this._generation) return;
            try { const results = await detector.detect(this.$refs.video); if (generation === this._generation && results.length === 1) this.detected(results[0].rawValue); } catch (_) {}
            if (generation === this._generation) this._timer = setTimeout(read, 120);
          };
          read();
        } else {
          this._quagga = true;
          Quagga.init({ inputStream: { type: 'LiveStream', target: this.$refs.viewport, constraints: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } } }, numOfWorkers: 0, frequency: 10, locate: true, locator: { patchSize: 'medium', halfSample: false }, decoder: { readers: ['code_128_reader', 'ean_reader', 'ean_8_reader', 'upc_reader', 'upc_e_reader', 'code_39_reader', 'code_93_reader', 'i2of5_reader', 'codabar_reader'] } }, async error => {
            if (generation !== this._generation) { try { Quagga.stop(); } catch (_) {} return; }
            if (error) { this.cameraError(error); return; }
            this._handler = result => {
              if (generation !== this._generation || !result || !result.codeResult) return;
              const errors = (result.codeResult.decodedCodes || []).map(item => item.error).filter(value => Number.isFinite(value));
              if (errors.length && errors.reduce((sum, value) => sum + value, 0) / errors.length > .18) return;
              this.detected(result.codeResult.code);
            };
            Quagga.onDetected(this._handler); Quagga.start();
            const track = Quagga.CameraAccess.getActiveTrack();
            if (track) await this.configureTrack(track);
            this.status = 'BUSCANDO · VALIDACIÓN DE 3 LECTURAS';
          });
        }
      } catch (error) { if (generation === this._generation) this.cameraError(error); }
    },
    cameraError(error) { this.stop(); this.status = 'NO SE PUDO ABRIR LA CÁMARA'; this.error = error && error.name === 'NotAllowedError' ? 'PERMITE EL ACCESO A LA CÁMARA EN EL NAVEGADOR Y VUELVE A INTENTAR.' : 'REVISA QUE LA CÁMARA ESTÉ DISPONIBLE Y ABRE EL SISTEMA CON HTTPS.'; },
    async configureTrack(track) {
      this._track = track;
      const capabilities = track.getCapabilities ? track.getCapabilities() : {};
      this.canTorch = !!capabilities.torch;
      if (capabilities.zoom) { this.zoomMin = capabilities.zoom.min; this.zoomMax = Math.min(4, capabilities.zoom.max); this.zoomStep = capabilities.zoom.step || .1; this.zoom = Math.max(this.zoomMin, Math.min(this.zoomMax, (track.getSettings().zoom || 1))); }
      if ((capabilities.focusMode || []).includes('continuous')) { try { await track.applyConstraints({ advanced: [{ focusMode: 'continuous' }] }); } catch (_) {} }
    },
    async toggleTorch() { try { await this._track.applyConstraints({ advanced: [{ torch: !this.torch }] }); this.torch = !this.torch; } catch (_) { this.error = 'EL DISPOSITIVO NO PERMITE ACTIVAR LA LINTERNA.'; } },
    async applyZoom() { try { await this._track.applyConstraints({ advanced: [{ zoom: Number(this.zoom) }] }); } catch (_) { this.error = 'NO SE PUDO CAMBIAR EL ZOOM.'; } },
    detected(value) {
      const code=this._accept(value);if(!code) return;
      if(this.continuous){const now=Date.now();if(this._lastReadAt && now-this._lastReadAt<2500)return;this._lastReadAt=now;this.status='LEÍDO: '+code+' · PUEDES ESCANEAR OTRO PRODUCTO';}
      else this.stop();
      if(navigator.vibrate)navigator.vibrate(80);
      this.$emit('code',code);
    },
    stop() {
      ++this._generation; clearTimeout(this._timer);
      if (this._handler) { Quagga.offDetected(this._handler); this._handler = null; }
      if (this._quagga) { try { Quagga.stop(); } catch (_) {} this._quagga = false; }
      if (this._stream) { this._stream.getTracks().forEach(track => track.stop()); this._stream = null; }
      this._track = null;
    }
  }
};
</script>
