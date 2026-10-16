from pathlib import Path
p=Path('tools/install-product-labor.php');p.write_text(p.read_text(encoding='utf-8-sig'),encoding='utf-8')
p=Path('tools/deploy-product-guide.ps1');s=p.read_text().replace('D68AFE66FD266F3F49BC2AA0416FE77191E9DFD0222EF533211EBD4617357B84','PLACEHOLDER_CURRENT').replace("@('ali-technical-expert.php'", "@('ali-product-labor.php','ali-technical-expert.php'");p.write_text(s)
