<template>
    <div>
      <b-container class="bv-example-row">
        <b-row>
					<b-card-group deck>
						<b-card bg-variant="success"
										text-variant="white"
										class="text-center">
								<h1>H - 101</h1>
								<div slot="footer">
									<b-button variant="success" size="sm">Liberar</b-button>	
									<b-button variant="warning" size="sm">Reservar</b-button>	
									<b-button variant="danger" size="sm">Alquilar</b-button>	
									<b-button variant="primary" size="sm">:D</b-button>	
								</div>
						</b-card>
						<b-card bg-variant="secondary"
										text-variant="white"
										header="Secondary"
										class="text-center">
								<p class="card-text">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
						</b-card>
						<b-card bg-variant="success"
										text-variant="white"
										header="Success"
										class="text-center">
								<p class="card-text">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
						</b-card>
						<b-card bg-variant="success"
										text-variant="white"
										header="Success"
										class="text-center">
								<p class="card-text">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
						</b-card>	
    			</b-card-group>
				</b-row>
      </b-container>
    </div>
</template>
<script>
export default {
    
}
</script>
<style scoped>
.card-footer {
	padding-left: 0;
	padding-right: 0;
}
</style>

