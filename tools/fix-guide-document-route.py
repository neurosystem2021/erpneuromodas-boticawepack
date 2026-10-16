from pathlib import Path
p=Path('src/router/index.js');s=p.read_text(encoding='utf-8');s=s.replace('      alias: "/remision/ver/:idDocVenta/:Serie/:Numero",\n','');needle='    {\n      path: "/remision-lista",';assert needle in s;s=s.replace(needle,'    {path:"/remision/ver/:idDocVenta/:Serie/:Numero",name:"RemisionVer",component:Remision},\n'+needle);p.write_text(s,encoding='utf-8')
