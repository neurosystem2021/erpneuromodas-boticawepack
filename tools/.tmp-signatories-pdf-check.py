from pathlib import Path
import fitz
root=Path(__file__).resolve().parent.parent
for name in ['contrato-sistemas-tres-firmas','proforma-18-contexto']:
    doc=fitz.open(root/'output'/f'{name}.pdf')
    text='\n'.join(p.get_text() for p in doc)
    assert 'Ing. Jhon H. Medrano Martinez' in text, (name,'consultant missing')
    if name.startswith('contrato'):
        assert 'Blanca Martinez Bravo' in text
        pages=[p for p in doc if p.search_for('Firma / rúbrica')]
        assert len(pages)==1,'Signatures should stay together'
        p=pages[0]
        consultant=p.search_for('Ing. Jhon H. Medrano Martinez')[-1]
        manager=p.search_for('Blanca Martinez Bravo')[-1]
        client=p.search_for('MARKET DE MUESTRA S.A.C.')[-1]
        assert consultant.x0<client.x0<manager.x0, 'Signature order incorrect'
        assert abs(consultant.y0-manager.y0)<2,'Unaligned signatures'
    p=doc[-1]
    out=root/'output'/f'{name}-firmas.png'
    p.get_pixmap(matrix=fitz.Matrix(1.5,1.5)).save(out)
    print(name, len(doc), 'pages; consultant visible; signatures verified',str(out))
