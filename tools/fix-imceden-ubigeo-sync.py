from pathlib import Path
p=Path('src/legacy-migrations/remision-modern.js');s=p.read_text(encoding='utf-8')
s=s.replace("var input = box.querySelector('input');", "var input = box.querySelector('input');\n      input.required = true;\n      input.setCustomValidity('Seleccione un distrito del catálogo.');\n      if(nativeInput) nativeInput.removeAttribute('required');")
s=s.replace("function choose(item) {\n        input.value", "function choose(item) {\n        input.setCustomValidity('');\n        input.value")
s=s.replace("      departmentSelect.addEventListener('change', function () {", """      function clearCode(){
        input.value='';input.setCustomValidity('Seleccione un distrito del catálogo.');
        if(nativeInput){nativeInput.value='';nativeInput.dispatchEvent(new Event('input',{bubbles:true}));}
      }
      departmentSelect.addEventListener('change', function () {
        clearCode();""")
s=s.replace("      provinceSelect.addEventListener('change', function () {", "      provinceSelect.addEventListener('change', function () {\n        clearCode();")
s=s.replace("input.addEventListener('input', render);", "input.addEventListener('input', function(){input.setCustomValidity('Seleccione un distrito del catálogo.');if(nativeInput){nativeInput.value='';nativeInput.dispatchEvent(new Event('input',{bubbles:true}));}render();});")
s=s.replace("fillSelect(departmentSelect, uniqueValues(items, 'department'), 'Seleccione departamento');", "fillSelect(departmentSelect, uniqueValues(items, 'department'), 'Seleccione departamento');\n        var initialCode=nativeInput && nativeInput.value;var initialItem=catalog.find(function(item){return item.code===initialCode;});if(initialItem)choose(initialItem);")
s=s.replace("      document.addEventListener('click', function (event) { if (!box.contains(event.target)) results.hidden = true; });", "      document.addEventListener('click', function (event) { if (!box.contains(event.target)) results.hidden = true; });\n      form.addEventListener('nst-remision-reset',function(){clearCode();departmentSelect.value='';fillSelect(provinceSelect,[],'Seleccione provincia');fillSelect(districtSelect,[],'Seleccione distrito');showSelection('','','');});")
p.write_text(s,encoding='utf-8')
p=Path('src/views/Remision.vue');s=p.read_text(encoding='utf-8').replace("this.obtenerNumero();       ","this.obtenerNumero();\n          this.$nextTick(()=>document.getElementById('remision-form').dispatchEvent(new Event('nst-remision-reset')));       ");p.write_text(s,encoding='utf-8')
