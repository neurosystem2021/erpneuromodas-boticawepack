from pathlib import Path
p=Path('src/views/Habitaciones.vue');s=p.read_text(encoding='utf-8').replace('import { createDescriptionDictation }','import { projectPrintReport } from "@/utils/projectPrintReport";\nimport { createDescriptionDictation }',1);a=s.index('    imprimir() {');b=s.index('    sumProductos() {',a)
s=s[:a]+'''    async imprimir() {
      const popup=window.open('','_blank','width=1000,height=760');
      if(!popup){this.$toasted.error('Permite las ventanas emergentes para imprimir.');return;}
      popup.document.write('<p>Preparando reporte del proyecto…</p>');
      try {
        let company={};
        try {const result=await this.axios.get(this.API+'empresa/id/'+(sessionStorage.getItem('empresa') || 1));company=result.data || {};}catch(error){}
        const html=projectPrintReport({project:this.selectProducto,products:this.$refs.preOrdenProductos.productos,advances:this.adelantos,expenses:this.gastos,labor:this.totalManoObraBruta,netLabor:this.totalManoObraNeta,company,user:sessionStorage.getItem('User'),date:new Date().toLocaleString('es-PE')});
        popup.document.open();popup.document.write(html);popup.document.close();
        const print=()=>{if(!popup.closed){popup.focus();popup.print();}};
        const logo=popup.document.querySelector('img');
        if(logo && !logo.complete){logo.onload=print;logo.onerror=print;}else setTimeout(print,150);
      }catch(error){popup.close();this.$toasted.error('No se pudo preparar el reporte.');}
    },
'''+s[b:];p.write_text(s,encoding='utf-8')
