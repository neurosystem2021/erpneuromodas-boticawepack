from pathlib import Path
p=Path('src/components/InventoryCamera.vue');s=p.read_text(encoding='utf-8').replace('<b-modal :visible="true"','<component :is="embedded ? \'section\' : \'b-modal\'" :class="embedded ? \'project-inline-scanner\' : \'\'" :visible="true"',1).replace('  </b-modal>','  </component>',1).replace('props:{continuous:{type:Boolean,default:false}}','props:{continuous:{type:Boolean,default:false},embedded:{type:Boolean,default:false}}',1).replace("mounted() { document.addEventListener('visibilitychange', this.visibilityChanged); },","mounted() { document.addEventListener('visibilitychange', this.visibilityChanged); if(this.embedded)this.$nextTick(()=>this.start()); },",1)
p.write_text(s,encoding='utf-8')
p=Path('src/components/PreOrdenFormDet.vue');s=p.read_text(encoding='utf-8').replace('<inventory-camera :continuous="true"','<inventory-camera :embedded="true" :continuous="true"',1)
s=s.replace('import config from','import InventoryCamera from "@/components/InventoryCamera";\nimport config from',1).replace('components:{InventoryCamera:resolve=>require(["@/components/InventoryCamera"],resolve)}','components:{InventoryCamera}',1)
p.write_text(s,encoding='utf-8')
