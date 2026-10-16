from pathlib import Path
p=Path('src/views/CentroSoporte.vue');s=p.read_text(encoding='utf-8')
s=s.replace('<h3>¿Cómo fue tu atención?</h3>', '''<h3>Califica esta atención</h3><p>Tu opinión ayuda a mejorar el servicio de {{detail.ticket.assigned_name || 'nuestro equipo'}}.</p><div class="support-rating-stars" role="group" aria-label="Puntuación de esta atención"><button v-for="n in 5" :key="n" type="button" :class="{chosen:Number(survey.solution)>=n}" :aria-pressed="Number(survey.solution)===n?'true':'false'" :aria-label="n+' de 5 estrellas'" @click="setRating(n)">★</button><span>{{survey.solution?survey.solution+' / 5 · '+ratingLabels[Number(survey.solution)-1]:'Selecciona de 1 a 5 estrellas'}}</span></div>''')
s=s.replace('<div class="support-form-grid"><label v-for="(name,key) in surveyLabels"', '<details class="support-rating-breakdown"><summary>Valorar rapidez, trato y solución por separado</summary><div class="support-form-grid"><label v-for="(name,key) in surveyLabels"')
s=s.replace('</select></label></div><label><input type="checkbox" v-model="survey.recommend">','</select></label></div></details><label><input type="checkbox" v-model="survey.recommend">')
s=s.replace("surveyLabels:{speed:","ratingLabels:['Muy mala','Regular','Buena','Muy buena','Excelente'],surveyLabels:{speed:")
s=s.replace('draftReply(requestInfo){','setRating(n){this.survey.speed=n;this.survey.treatment=n;this.survey.solution=n},draftReply(requestInfo){')
s=s.replace("this.acceptMinutes='';this.estimate={minutes:'',reason:''};", "this.acceptMinutes='';this.estimate={minutes:'',reason:''};this.survey={solved:true,speed:'',treatment:'',solution:'',recommend:false,comment:''};")
p.write_text(s,encoding='utf-8')
