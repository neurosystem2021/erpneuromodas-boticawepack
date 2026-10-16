import fs from 'node:fs';
const path='src/views/Contabilidad.vue';let s=fs.readFileSync(path,'utf8');
s=s.replace('accountingRoles,accountingFlows}', 'accountingRoles,accountingFlows,accountingNatures,accountingClasses,accountingGroups,accountingAccountLabel}');
s=s.replace('formatter:monetary.includes(key)?accountingMoney:undefined','formatter:monetary.includes(key)?accountingMoney:([\'Nature\',\'Class\',\'ReportGroup\'].includes(key)?value=>accountingAccountLabel(value,key):undefined)');
s=s.replace(':options="[\'debit\',\'credit\']"',':options="natureOptions"').replace(':options="[\'asset\',\'liability\',\'equity\',\'income\',\'expense\',\'memo\']"',':options="classOptions"');
s=s.replace('<label>Rubro de reporte<b-form-input v-model="account.group" required/></label>','<label>Rubro en los reportes<b-form-select v-model="account.group" :options="groupOptions" required/></label>');
s=s.replace('<label>Padre<b-form-input v-model="account.parent"/></label>','<label>Cuenta superior (código)<b-form-input v-model="account.parent" placeholder="Vacío si es una cuenta principal"/></label>');
s=s.replace('computed:{companyOptions()', 'computed:{natureOptions(){return accountingNatures;},classOptions(){return accountingClasses;},groupOptions(){const options=Object.keys(accountingGroups).map(value=>({value,text:accountingGroups[value]}));if(this.account.group&&!accountingGroups[this.account.group])options.push({value:this.account.group,text:this.account.group+\' (rubro personalizado)\'});return options;},companyOptions()');
fs.writeFileSync(path,s);
