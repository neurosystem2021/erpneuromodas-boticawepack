from pathlib import Path
p=Path('src/views/Habitaciones.vue');s=p.read_text(encoding='utf-8');needle='<b-button type="button" size="sm" variant="outline-info" @click="editProjectPhone">';assert needle in s;s=s.replace(needle,'''<div class="project-phone-links"><a v-if="projectPhoneNumber(selectProducto.Telefono)" class="btn btn-success" :href="'https://wa.me/' + projectPhoneNumber(selectProducto.Telefono,true)" target="_blank" rel="noopener noreferrer"><i class="fab fa-whatsapp" aria-hidden="true"></i> WhatsApp</a><a v-if="projectPhoneNumber(selectProducto.Telefono)" class="btn btn-outline-info" :href="'tel:' + projectPhoneNumber(selectProducto.Telefono)"><i class="fas fa-phone" aria-hidden="true"></i> Llamar</a>'''+needle,1)
s=s.replace('Editar teléfono</b-button></div><div v-else','Editar teléfono</b-button></div></div><div v-else',1)
s=s.replace('  methods: {','''  methods: {
    projectPhoneNumber(value,whatsapp=false) {
      const first=String(value || '').split(/[;,/]|(?:\\s+-\\s+)/)[0];
      const digits=first.replace(/\\D/g,'');
      if(digits.length<7) return '';
      if(whatsapp) return digits.length===9 ? '51'+digits : digits;
      return first.trim().startsWith('+') ? '+'+digits : digits;
    },''',1)
p.write_text(s,encoding='utf-8')
