import fs from 'node:fs/promises';
import {FileBlob,SpreadsheetFile} from '@oai/artifact-tool';
const w=await SpreadsheetFile.importXlsx(await FileBlob.load('output/pdf/OrdenCompra_2026_5.xlsx'));
console.log(await w.inspect({kind:'region',sheetId:'Orden de compra',range:'A11:E24',maxChars:2500,tableMaxRows:14,tableMaxCols:5}));
await w.recalculate();
const p=await w.render({sheetName:'Orden de compra',range:'A1:E26',scale:1,format:'png'});
await fs.writeFile('.tmp-order-workbook/preview.png',new Uint8Array(await p.arrayBuffer()));
