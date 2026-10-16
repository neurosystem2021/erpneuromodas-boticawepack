import fs from 'node:fs/promises';
import {FileBlob,SpreadsheetFile} from '@oai/artifact-tool';
const book=await SpreadsheetFile.importXlsx(await FileBlob.load('output/management-report-runtime.xlsx'));
book.recalculate();
console.log((await book.inspect({kind:'table',range:'Resumen!A5:E16',include:'values,formulas',tableMaxRows:13,tableMaxCols:5,maxChars:2500})).ndjson);
const view=await book.render({sheetName:'Resumen',autoCrop:'all',scale:1,format:'png'});
await fs.writeFile('output/management-report-preview.png',new Uint8Array(await view.arrayBuffer()));
await (await SpreadsheetFile.exportXlsx(book)).save('output/Reporte-Gerencial-Octubre-2026.xlsx');
console.log('Verified workbook recalculation, preview and XLSX export.');