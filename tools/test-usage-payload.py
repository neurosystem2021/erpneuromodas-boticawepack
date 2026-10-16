from pathlib import Path
p=Path('tools/test-product-guide.php');s=p.read_text(encoding='utf-8');needle="$key=aliGuideCacheKey";i=s.index(needle);s=s[:i]+'''guideCheck(strpos($payload['instructions'],'No afirmes compatibilidad sin evidencia')!==false,'technical consultation verifies compatibility');$specPayload=aliGuidePayload($product,'',$config);guideCheck(strpos($specPayload['instructions'],'Usado para:')!==false,'usage generated separately from technical items');
'''+s[i:];p.write_text(s,encoding='utf-8')
