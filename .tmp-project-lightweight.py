from pathlib import Path
p=Path('src/components/PreOrdenFormDet.vue');s=p.read_text(encoding='utf-8').replace('import InventoryCamera from "@/components/InventoryCamera";\n','').replace('  components:{InventoryCamera},','  components:{InventoryCamera:resolve=>require(["@/components/InventoryCamera"],resolve)},',1);p.write_text(s,encoding='utf-8')
p=Path('src/views/Habitaciones.vue');s=p.read_text(encoding='utf-8').replace('    this.getHabitaciones();\n  },','''    this.getHabitaciones();
    this.axios.get(this.API+'empresa/id/'+(sessionStorage.getItem('empresa') || 1)).then(result=>{this._reportCompany=result.data || {};}).catch(()=>{});
  },''',1)
s=s.replace('    async imprimir() {','    imprimir() {',1)
a=s.index('        let company={};',s.index('    imprimir() {'));b=s.index('        const html=projectPrintReport',a);s=s[:a]+'        const company=this._reportCompany || {};\n'+s[b:]
s=s.replace("        if(logo && !logo.complete){logo.onload=print;logo.onerror=print;}else setTimeout(print,150);","        let printed=false;const ready=()=>{if(printed)return;printed=true;print();};\n        if(logo && !logo.complete){logo.onload=ready;logo.onerror=ready;setTimeout(ready,700);}else setTimeout(ready,50);",1)
p.write_text(s,encoding='utf-8')
