from pathlib import Path
p=Path('src/components/ProformaContrato.vue');s=p.read_text(encoding='utf-8')
s=s.replace(' mounted(){this.load();},', " watch:{form:{deep:true,handler(){this.reviewed=false;}}},\n mounted(){this.load();},")
s=s.replace("this.form=Object.assign({},this.form,data.contract.form);this.reviewed=false;", "this.form=Object.assign({},this.form,data.contract.form);if(data.contract.stale&&this.form.recibido){this.form.modoAdelanto='monto';this.form.adelanto=data.contract.form.adelantoCentimos/100;}this.reviewed=false;")
p.write_text(s,encoding='utf-8')
