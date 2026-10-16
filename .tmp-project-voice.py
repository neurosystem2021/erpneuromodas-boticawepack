from pathlib import Path
p=Path('src/views/Habitaciones.vue');s=p.read_text(encoding='utf-8').replace('import { mapActions, mapState }','import { createDescriptionDictation } from "@/utils/descriptionDictation";\nimport { mapActions, mapState }',1)
s=s.replace('            <label>Descripcion:</label>','''            <div class="project-description-label"><label>Descripción:</label><b-button type="button" :variant="dictatingDescription ? 'danger' : 'outline-info'" size="sm" @click="toggleDescriptionDictation" :aria-pressed="dictatingDescription"><i :class="dictatingDescription ? 'fas fa-stop' : 'fas fa-microphone'" aria-hidden="true"></i> {{ dictatingDescription ? 'Terminar dictado' : 'Dictar descripción' }}</b-button></div><p v-if="dictatingDescription" class="project-dictation-status" role="status">Escuchando… Puedes hacer pausas de hasta 10 segundos.</p>''',1)
s=s.replace('v-model="selectCliente.Descripcion"','v-model="selectCliente.Descripcion"\n              :readonly="dictatingDescription"',1)
s=s.replace('      mdlNuevoProyecto: false,','      dictatingDescription: false,\n      mdlNuevoProyecto: false,',1)
s=s.replace('  methods: {','''  beforeDestroy() { if (this._descriptionDictation) this._descriptionDictation.dispose(); },
  methods: {
    toggleDescriptionDictation() {
      if (this.dictatingDescription) { this._descriptionDictation.stop(); return; }
      const Recognition=window.SpeechRecognition || window.webkitSpeechRecognition;
      if (!Recognition) { this.$toasted.error('El dictado no está disponible en este navegador. Usa Chrome o Edge.'); return; }
      if (this._descriptionDictation) this._descriptionDictation.dispose();
      this._descriptionDictation=createDescriptionDictation({Recognition,initialText:this.selectCliente.Descripcion || '',
        onText:text => { this.$set(this.selectCliente,'Descripcion',text); },
        onState:active => { this.dictatingDescription=active; },
        onError:error => { this.$toasted.error(error === 'not-allowed' ? 'Permite el acceso al micrófono para dictar.' : 'No se pudo continuar el dictado. Tu descripción se conserva.'); }
      });
      this._descriptionDictation.start();
    },''',1)
s=s.replace('    async nuevoProyecto() {','    async nuevoProyecto() {\n      if (this._descriptionDictation) this._descriptionDictation.stop();',1)
p.write_text(s,encoding='utf-8')
