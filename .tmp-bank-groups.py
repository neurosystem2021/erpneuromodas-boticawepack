from pathlib import Path
p=Path('src/views/reportes/CierreCaja.vue');s=p.read_text(encoding='utf-8').replace('isMainCashMovement, partitionCashMovements, sumMovementAmounts','isMainCashMovement, partitionCashMovements, sumMovementAmounts, groupBankMovements')
for direction,items,total,label in [('Ingresos','ingresosTarjeta','totalIngresosTarjeta','ingresos'),('Salidas','salidasTarjeta','totalSalidasTarjeta','salidas')]:
    start=0
    for i in range(2):
        a=s.index('<b-col v-if="'+total+'">',start);b=s.index('</b-col>',a)+len('</b-col>');part=s[a:b]
        part=part.replace('<b-col v-if="'+total+'">','<b-col cols="12" v-for="group in '+items+'PorCuenta" :key="group.key">')
        part=part.replace(direction+' bancari'+('os' if direction=='Ingresos' else 'as')+' de referencia</h5>',direction+' bancari'+('os' if direction=='Ingresos' else 'as')+' de referencia — {{ group.account }}</h5>')
        part=part.replace('in '+items+'"','in group.rows"').replace('colspan="2"','colspan="3"').replace('Total '+label+' bancarias (referencial)','Total '+label+' {{ group.account }} (referencial)').replace('Total ingresos bancarios (referencial)','Total ingresos {{ group.account }} (referencial)').replace('{{ '+total+' |','{{ group.total |')
        s=s[:a]+part+s[b:];start=a+len(part)
s=s.replace('    totalSalidasTarjeta() {','    ingresosTarjetaPorCuenta() { return groupBankMovements(this.ingresosTarjeta); },\n    salidasTarjetaPorCuenta() { return groupBankMovements(this.salidasTarjeta); },\n    totalSalidasTarjeta() {',1)
p.write_text(s,encoding='utf-8')
