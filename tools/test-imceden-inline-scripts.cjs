const fs=require('fs'),vm=require('vm');let count=0;
for(const file of fs.readdirSync('.tmp-imceden-rendered')){const html=fs.readFileSync('.tmp-imceden-rendered/'+file,'utf8');const scripts=[...html.matchAll(/<script\b([^>]*)>([\s\S]*?)<\/script>/gi)].filter(m=>!(/src\s*=|application\/json/i.test(m[1]))).map(m=>m[2]);try{new vm.Script(scripts.join('\n;\n'),{filename:file});console.log(file+': '+scripts.length+' scripts valid');count+=scripts.length;}catch(e){console.error(e.stack);process.exitCode=1;}}
console.log(count+' inline scripts parsed');

