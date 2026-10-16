from pathlib import Path
p=Path('src/components/ProductosCarritoNuevo.vue');s=p.read_text(encoding='utf-8');a=s.index('    startRecognition() {');b=s.index('    startRecognitionCodeBar()',a)
s=s[:a]+'''    startRecognition() {
      if (this._productRecognition) { this._productRecognition.stop(); return; }
      const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
      if (!SpeechRecognition) { this.$toasted.error("Tu navegador no soporta la búsqueda por voz."); return; }
      const recognition = new SpeechRecognition();
      this._productRecognition = recognition;
      recognition.lang = "es-PE";
      recognition.continuous = false;
      recognition.interimResults = true;
      let latest = "", searched = "";
      const reset = () => {
        if (this._productRecognition === recognition) {
          this._productRecognition = null;
          this.microActivo = false;
        }
      };
      const searchLatest = () => {
        if (latest && latest !== searched) { searched = latest; this.query = latest; this.search(); }
      };
      recognition.onstart = () => {
        if (this._productRecognition === recognition) this.microActivo = true;
      };
      recognition.onresult = event => {
        if (this._productRecognition !== recognition) return;
        const parts = [];
        for (let i = 0; i < event.results.length; i++) {
          if (event.results[i][0]) parts.push(event.results[i][0].transcript);
        }
        const text = parts.join(" ").replace(/\\s+/g, " ").trim();
        if (!text) return;
        latest = text; this.query = text;
        const autocomplete = this.$refs.autocompleteProducto;
        if (autocomplete) autocomplete.setValue(text);
        const last = event.results[event.results.length - 1];
        if (last.isFinal !== false) searchLatest();
      };
      recognition.onerror = event => {
        reset();
        if (event.error !== "aborted" && event.error !== "no-speech") this.$toasted.error("No se pudo usar el micrófono. Revisa sus permisos.");
      };
      recognition.onend = () => {
        if (this._productRecognition !== recognition) return;
        searchLatest(); reset();
      };
      try { recognition.start(); } catch (error) { reset(); this.$toasted.error("No se pudo iniciar el micrófono. Intenta nuevamente."); }
    },
'''+s[b:]
old='''        input.focus();
        input.value = text;
        autocomplete.handleInput({ target: input });''';new='''        this._voiceSearchFocus = true;
        try { input.focus(); } finally { this._voiceSearchFocus = false; }
        // Focus callbacks must not reset the autocomplete's reactive model.
        autocomplete.setValue(text);
        input.value = text;
        autocomplete.handleInput({ target: input });''';assert old in s;s=s.replace(old,new,1)
old='''    focusAutocomplete(ref) {
      this.$refs[ref].type = "";
      this.$refs[ref].handleDoubleClick();
    },''';new='''    focusAutocomplete(ref) {
      const autocomplete = this.$refs[ref];
      if (!autocomplete || this._voiceSearchFocus) return;
      const text = String(autocomplete.type || "").trim();
      if (text) autocomplete.handleInput({ target: { value: text } });
      else autocomplete.handleDoubleClick();
    },''';assert old in s;s=s.replace(old,new,1)
s=s.replace('Detener micr?fono','Detener micrófono')
needle='''  destroyed() {
''';s=s.replace(needle,needle+'''    if (this._productRecognition) {
      const recognition = this._productRecognition;
      this._productRecognition = null;
      recognition.onresult = recognition.onend = recognition.onerror = null;
      try { recognition.abort(); } catch (error) {}
    }
''',1)
p.write_text(s,encoding='utf-8')
