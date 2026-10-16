import fs from 'node:fs/promises';
import {FileBlob,SpreadsheetFile} from '@oai/artifact-tool';
const w=await SpreadsheetFile.importXlsx(await FileBlob.load('output/Planilla_2026-10.xlsx'));
await w.recalculate();
console.log(await w.inspect({kind:'region',sheetId:'Planilla',range:'J6:O11',maxChars:1200,tableMaxRows:6,tableMaxCols:6}));
for(const [name,range,path] of [['Planilla','A1:F11','payroll-preview.png'],['Pagos registrados','A1:K9','payroll-payments.png']]){
 const p=await w.render({sheetName:name,range,scale:1,format:'png'});await fs.writeFile('.tmp-order-workbook/'+path,new Uint8Array(await p.arrayBuffer()));
}
