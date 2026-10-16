export function limaInput(date = new Date()) {
 const parts = new Intl.DateTimeFormat('sv-SE', {timeZone:'America/Lima',year:'numeric',month:'2-digit',day:'2-digit',hour:'2-digit',minute:'2-digit',hourCycle:'h23'}).format(date);
 return parts.replace(' ', 'T');
}
export function taskMillis(value) { return value ? Date.parse(value.replace(' ', 'T')+'-05:00') : 0; }
export function reminderPhase(task, now) {
 if(!['pendiente','en_curso','pausada'].includes(task.Estado))return '';
 const end=taskMillis(task.Fin),start=taskMillis(task.Inicio),before=Number(task.Anticipacion)*60000;
 if(now>=end)return 'vencida';
 if(now>=end-before)return 'fin';
 if(task.Estado==='pendiente'&&now>=start-before)return now>=start?'inicio_pendiente':'inicio';
 return '';
}
export function motivation(id) {
 const phrases=['¡Buen trabajo! Cada tarea concluida es un paso hacia tus metas.', '¡Lo lograste! Tu constancia hace la diferencia.', '¡Excelente avance! Sigue construyendo resultados, una tarea a la vez.', '¡Actividad concluida! Gracias por tu esfuerzo y compromiso.', '¡Muy bien! Reconoce tu progreso y sigue adelante.'];
 return phrases[Number(id)%phrases.length];
}
export function productivity(tasks, now) {
 const groups={};
 for(const t of tasks){const key=t.EmpleadoKey?'e:'+t.EmpleadoKey:(t.IdEmpleado?'e:'+t.IdEmpleado:'u:'+t.Usuario);const g=groups[key]||(groups[key]={key,name:t.ResponsableNombre||t.Usuario||'Empleado',cargo:t.Cargo||'Sin cargo vinculado',total:0,done:0,validated:0,onTime:0,late:0,seconds:0,possible:0,earned:0,qualitySum:0});
  g.total++;g.seconds+=Number(t.Segundos||0)+(t.EnCursoDesde?Math.max(0,(now-taskMillis(t.EnCursoDesde))/1000):0);
  if(t.Estado==='cancelada')continue;const weight=Number(t.Puntos)||1;g.possible+=weight;
  if(t.Estado==='concluida'){g.done++;if(taskMillis(t.FinReal)<=taskMillis(t.Fin))g.onTime++;if(Number(t.Calidad)>0){g.validated++;g.qualitySum+=Number(t.Calidad);const result=Number(t.Objetivo)>0?Math.min(1,Number(t.Realizado||0)/Number(t.Objetivo)):1;g.earned+=weight*result*Number(t.Calidad)/5;}}
  else if(taskMillis(t.Fin)<now)g.late++;
 }
 return Object.values(groups).map(g=>Object.assign(g,{achievement:g.possible?Math.round(100*g.earned/g.possible):0,quality:g.validated?(g.qualitySum/g.validated).toFixed(1):'—',earned:Math.round(g.earned*100)/100}));
}

export function ticketNumber(task){if(/^(SUP|DEV|MNT|REU|ACT)-[0-9]{6,}$/.test(task.Ticket||''))return task.Ticket;return ({soporte:'SUP',programacion:'DEV',mantenimiento:'MNT',reunion:'REU'}[task.TipoTrabajo]||'ACT')+'-'+String(task.Id).padStart(6,'0')}
