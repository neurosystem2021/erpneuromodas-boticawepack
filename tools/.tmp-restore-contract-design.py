from pathlib import Path

root = Path(__file__).resolve().parent.parent
template = root / 'integration/proforma-edit/proforma-contract-template.php'
raw = template.read_bytes()
lines = raw.splitlines(keepends=True)
override = [line for line in lines if line.lstrip().startswith(b'.contract-plain{')]
assert len(override) == 1, 'Expected exactly one plain-design override'
updated = b''.join(line for line in lines if line not in override)
assert updated.count(b'<body class="contract-plain">') == 1
updated = updated.replace(b'<body class="contract-plain">', b'<body>')
assert b'qcDisplaySignature($data)' in updated
template.write_bytes(updated)

api = root / 'tools/.tmp-plain-contract-api.cjs'
script = api.read_text(encoding='utf-8')
script = script.replace("assert(html.includes('class=\"contract-plain\"'));", "assert(!html.includes('contract-plain'));assert(html.includes('.reference{margin:9px 0 13px;background:#fff4f6;border:1px solid #ead6dc}'));assert(html.includes('text-align:center}.consultant-rubric-image{display:inline-block'));")
script = script.replace('contrato-200-2026-sin-fondos.pdf', 'contrato-200-2026-firma-corregida.pdf')
script = script.replace('white/plain template', 'restored corporate design')
(root / 'tools/.tmp-corrected-signature-api.cjs').write_text(script, encoding='utf-8')
print('Restored original corporate design; retained targeted signature cleanup and centering.')
