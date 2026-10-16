const dailyPhrases = [
 'Cada paso cuenta. Hoy puedes avanzar un poco más.',
 'Tu atención y amabilidad pueden mejorar el día de alguien.',
 'Confía en lo que sabes y sigue aprendiendo.',
 'Los pequeños esfuerzos de hoy construyen grandes resultados.',
 'Haz una cosa a la vez, con calma y confianza.',
 'Hoy es una nueva oportunidad para hacerlo bien.',
 'Tu dedicación marca la diferencia.',
 'Cada cliente es una oportunidad para ayudar y aprender.',
 'Celebra tus avances, incluso los pequeños.',
 'La constancia transforma el esfuerzo en progreso.',
 'Escuchar con atención es el primer paso para ayudar.',
 'Trabajar en equipo hace más ligero el camino.',
 'No necesitas hacerlo perfecto para seguir mejorando.',
 'Tu esfuerzo de hoy tiene valor.'
];

function dayContext(now) {
 const parts = new Intl.DateTimeFormat('en-GB', {
  timeZone:'America/Lima', year:'numeric', month:'2-digit', day:'2-digit',
  hour:'2-digit', hourCycle:'h23'
 }).formatToParts(now);
 const values = {};
 parts.forEach(part => { values[part.type] = part.value; });
 const hour = Number(values.hour);
 const day = Math.floor(Date.UTC(Number(values.year), Number(values.month)-1, Number(values.day))/86400000);
 return {hour, phrase:dailyPhrases[day % dailyPhrases.length]};
}

function userName(user) {
 return typeof user === 'string' ? user.replace(/[\u0000-\u001f<>]/g,' ').replace(/\s+/g,' ').trim().slice(0,60) : '';
}

export function aliGreeting(user, now = new Date()) {
 const {hour,phrase} = dayContext(now);
 const greeting = hour >= 5 && hour < 12 ? 'Buenos días' : hour >= 12 && hour < 19 ? 'Buenas tardes' : 'Buenas noches';
 const name = userName(user);
 return greeting+(name?', '+name:'')+'. '+phrase+' ¿En qué te puedo ayudar?';
}

export function aliEncouragement(user, now = new Date()) {
 const name = userName(user);
 return (name?name+', ':'')+dayContext(now).phrase;
}

export function aliFarewell(user, now = new Date()) {
 const {hour} = dayContext(now);
 const wish = hour >= 5 && hour < 12 ? 'un buen día' : hour >= 12 && hour < 19 ? 'una buena tarde' : 'una buena noche';
 const name = userName(user);
 return 'Hasta luego'+(name?', '+name:'')+'. Que tengas '+wish+'. Cuando me necesites, di Hola ALI.';
}
