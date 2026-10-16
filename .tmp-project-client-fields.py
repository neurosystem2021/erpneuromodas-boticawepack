from pathlib import Path
p=Path('src/components/ClientesForm.vue');s=p.read_text(encoding='utf-8');s=s.replace('"buttonTitle"],','"buttonTitle", "projectMode"],',1)
s=s.replace('modal-class="nst-dialog nst-client-dialog"',':modal-class="projectMode ? \'nst-dialog nst-client-dialog project-client-dialog\' : \'nst-dialog nst-client-dialog\'"',1)
a=s.index('        <b-form-group>\n          <label>Direc')
s=s[:a]+'''        <b-form-group v-if="projectMode">
          <label>Teléfono:</label><b-form-input v-model="cliente.Telefono" type="tel" autocomplete="tel"></b-form-input>
        </b-form-group>
'''+s[a:]
s=s.replace('<label>Dirección 1:</label>','<label>{{ projectMode ? "Dirección:" : "Dirección 1:" }}</label>',1)
for field in ['Direccion2','Direccion3']:
    loc=s.index('v-model="cliente.'+field+'"');a=s.rfind('<b-form-group>',0,loc);s=s[:a]+s[a:].replace('<b-form-group>','<b-form-group v-if="!projectMode">',1)
for field in ['Telefono','Nacionalidad']:
    loc=s.index('v-model="cliente.'+field+'"',s.index('    </b-row>'));a=s.rfind('<b-col>',0,loc);s=s[:a]+s[a:].replace('<b-col>','<b-col v-if="!projectMode">',1)
loc=s.index('v-model="cliente.Sexo"');a=s.rfind('<b-row>',0,loc);s=s[:a]+s[a:].replace('<b-row>','<b-row v-if="!projectMode">',1)
p.write_text(s,encoding='utf-8')
p=Path('src/views/Habitaciones.vue');s=p.read_text(encoding='utf-8').replace('<clientes-form','<clientes-form :project-mode="true"');p.write_text(s,encoding='utf-8')
