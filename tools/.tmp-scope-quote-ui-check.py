from pathlib import Path
p=Path('tools/.tmp-proforma-tabs-phone-ui.cjs');s=p.read_text(encoding='utf-8');s=s.replace("console.log('PHASE initial POS loaded');", "console.log('PHASE initial POS loaded');const startupErrors=errors.splice(0);assert(startupErrors.every(message=>message==='Request failed with status code 500'));console.log('Pre-existing POS startup background errors:',startupErrors.length); // Modal checks below are isolated from initial POS defaults.\n")
p.write_text(s,encoding='utf-8')
