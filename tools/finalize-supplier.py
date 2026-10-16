from pathlib import Path
p=Path('integration/order-supplier/api/purchase-order-report.php');s=p.read_text(encoding='utf-8');s=s.encode('cp1252').decode('utf-8');s=s.replace("$s->setCellValue('E'.($r+1),'=SUM(E19:E'.($r-1).')');","$s->setCellValue('E'.($r+1),'=SUM(E19:E'.($r-1).')');$s->getCell('E'.($r+1))->setFormulaAttributes(['t'=>'normal']);")
p.write_text(s,encoding='utf-8')
import fitz
d=fitz.open('output/pdf/OrdenCompra_2026_5.pdf');d[0].get_pixmap(matrix=fitz.Matrix(1,1)).save('.tmp-order-workbook/pdf-preview.png');print(d[0].get_text())
