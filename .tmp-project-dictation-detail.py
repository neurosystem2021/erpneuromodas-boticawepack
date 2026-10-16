from pathlib import Path
p=Path('src/views/Habitaciones.vue');s=p.read_text(encoding='utf-8')
s=s.replace(':variant="dictatingDescription ? \'danger\' : \'outline-info\'"',':variant="dictatingDescription ? \'danger\' : \'outline-info\'" :class="{\'project-voice-active\':dictatingDescription}"',1).replace("dictatingDescription ? 'Terminar dictado' : 'Dictar descripción'","dictatingDescription ? 'Escuchando · Detener' : 'Dictar descripción'",1)
s=s.replace('      dictatingDescription: false,','      dictatingProjectDescription:false,\n      dictatingDescription: false,',1)
s=s.replace('beforeDestroy() { if (this._descriptionDictation) this._descriptionDictation.dispose(); }','beforeDestroy() { if (this._descriptionDictation) this._descriptionDictation.dispose(); if(this._projectDescriptionDictation)this._projectDescriptionDictation.dispose(); }',1)
s=s.replace('ref="modalCarrito" modal-class=','@hide="stopProjectDictation" ref="modalCarrito" modal-class=',1)
a=s.index('                      variant="info"',s.index('<div class="project-description-section">'));s=s[:a]+s[a:].replace('                      variant="info"','                      :variant="dictatingProjectDescription ? \'danger\' : \'info\'"\n                      :class="{\'project-voice-active\':dictatingProjectDescription}"\n                      :aria-pressed="dictatingProjectDescription"\n                      type="button"',1)
s=s.replace('                      <i class="fa fa-microphone" aria-hidden="true"></i>\n                      Dictado Por Voz','                      <i :class="dictatingProjectDescription ? \'fas fa-stop\' : \'fas fa-microphone\'" aria-hidden="true"></i>\n                      {{ dictatingProjectDescription ? \'Escuchando · Detener\' : \'Dictado por voz\' }}',1)
s=s.replace('                    >\n                  </b-col>\n                  <b-col md="9">','                    >\n                    <p v-if="dictatingProjectDescription" class="project-dictation-status" role="status">Puedes hablar. Se detiene después de 10 segundos de silencio; lo escrito se conserva.</p>\n                  </b-col>\n                  <b-col md="9">',1)
s=s.replace('                      :disabled="!editarDescripcion"','                      :disabled="!editarDescripcion"\n                      :readonly="dictatingProjectDescription"',1)
a=s.index('    startRecognition(event) {');b=s.index('    handleError(error) {',a)
s=s[:a]+'''    stopProjectDictation() { if(this._projectDescriptionDictation)this._projectDescriptionDictation.stop(); },
    startRecognition(event) {
      if(event)event.stopPropagation();
      if(this.dictatingProjectDescription){this.stopProjectDictation();return;}
      const Recognition=window.SpeechRecognition || window.webkitSpeechRecognition;
      if(!Recognition){this.$toasted.error('El dictado no está disponible en este navegador. Usa Chrome o Edge.');return;}
      if(this._descriptionDictation)this._descriptionDictation.stop();
      if(this._projectDescriptionDictation)this._projectDescriptionDictation.dispose();
      const project=this.selectProducto;
      this._projectDescriptionDictation=createDescriptionDictation({Recognition,initialText:project.Descripcion || '',
        onText:text=>this.$set(project,'Descripcion',text),
        onState:active=>{this.dictatingProjectDescription=active;},
        onError:error=>this.$toasted.error(error==='not-allowed'?'Permite el acceso al micrófono para dictar.':'No se pudo continuar el dictado. El texto escrito se conserva.')
      });
      this._projectDescriptionDictation.start();
    },
'''+s[b:]
s=s.replace('    guardarDescripcion() {','    guardarDescripcion() {\n      this.stopProjectDictation();',1).replace('    openCarrito(hab) {','    openCarrito(hab) {\n      this.stopProjectDictation();',1)
p.write_text(s,encoding='utf-8')
