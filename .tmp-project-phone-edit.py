from pathlib import Path
p=Path(r'C:\xampp\htdocs\boticav1\api\index.php');s=p.read_text(encoding='utf-8');s=s.replace("$app->get('/habitaciones'", "$app->post('/proyecto/telefono', require __DIR__.'/project-phone-save.php');\n\n$app->get('/habitaciones'",1);assert "'/proyecto/telefono'" in s;p=Path('integration/project-phone-edit/api/index.php');p.write_text(s,encoding='utf-8')
p=Path('src/views/Habitaciones.vue');s=p.read_text(encoding='utf-8');a=s.index('<p class="project-detail-phone">');b=s.index('</p>',a)+4;s=s[:a]+'''<div class="project-detail-phone"><div v-if="!editingProjectPhone" class="project-phone-view"><span><i class="fas fa-phone" aria-hidden="true"></i> Teléfono: <b>{{ selectProducto.Telefono || 'Sin teléfono registrado' }}</b></span><b-button type="button" size="sm" variant="outline-info" @click="editProjectPhone"><i class="fas fa-pencil-alt" aria-hidden="true"></i> Editar teléfono</b-button></div><div v-else class="project-phone-editor"><label for="projectPhone">Teléfono del cliente</label><b-form-input id="projectPhone" v-model="projectPhoneDraft" type="tel" autocomplete="tel" :disabled="savingProjectPhone"/><b-button type="button" variant="success" :disabled="savingProjectPhone || !projectPhoneDraft.trim()" @click="saveProjectPhone">{{ savingProjectPhone ? 'Guardando…' : 'Guardar teléfono' }}</b-button><b-button type="button" variant="outline-secondary" :disabled="savingProjectPhone" @click="editingProjectPhone=false">Cancelar</b-button></div></div>'''+s[b:]
s=s.replace('      dictatingDescription: false,','      editingProjectPhone:false,\n      savingProjectPhone:false,\n      projectPhoneDraft:\'\',\n      dictatingDescription: false,',1)
s=s.replace('  methods: {','''  methods: {
    editProjectPhone() { this.projectPhoneDraft=this.selectProducto.Telefono || ''; this.editingProjectPhone=true; },
    async saveProjectPhone() {
      if(this.savingProjectPhone) return;
      const product=this.selectProducto;
      this.savingProjectPhone=true;
      try {
        const response=await this.axios.post(this.API+'proyecto/telefono',{IdProducto:product.IdProducto,Telefono:this.projectPhoneDraft});
        this.$set(product,'Telefono',response.data.Telefono);
        this.habitaciones.filter(item => Number(item.IdClienteReserva)===Number(response.data.IdCliente)).forEach(item => this.$set(item,'Telefono',response.data.Telefono));
        this.editingProjectPhone=false;
        this.$toasted.success('Teléfono guardado correctamente');
      } catch(error) { this.$toasted.error(error.response && error.response.data && error.response.data.error || 'No se pudo guardar el teléfono.'); }
      finally { this.savingProjectPhone=false; }
    },''',1)
s=s.replace('    openCarrito(hab) {','    openCarrito(hab) {\n      this.editingProjectPhone=false;',1);p.write_text(s,encoding='utf-8')
