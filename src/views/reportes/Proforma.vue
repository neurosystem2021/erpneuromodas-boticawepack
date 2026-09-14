<template>
	<div id="invoice-container">
		<header class="inv-header">
			<div id="logo">
				<img v-if="proforma.IdAlmacen !== 3 && proforma.IdAlmacen !== 4" src="/resources/images/logo.png">
				<img v-if="proforma.IdAlmacen == 3 || proforma.IdAlmacen == 4" src="/resources/images/logotowan.png">
			</div>
			<div id="company">
				<h2 class="name">{{ empresa.RAZONSOCIAL }}</h2>
				<div class="ruc">RUC: <b>{{ empresa.RUC }}</b></div>
				<div class="dir">{{ proforma.AlmacenDireccion }}</div>
			</div>
		</header>

		<main>
			<div id="details" class="clearfix">
				<div id="client">
					<div class="to">PROFORMA A</div>
					<h2 class="name">{{ proforma.Cliente }}</h2>
					<div class="doc"><b>{{ proforma.ClienteDniRuc }}</b></div>
					<div class="address" v-if="proforma.ClienteDireccion">{{ proforma.ClienteDireccion }}</div>
					<div class="email" v-if="proforma.ClienteEmail">
						<a :href="'mailto:' + proforma.ClienteEmail">{{ proforma.ClienteEmail }}</a>
					</div>
				</div>
				<div id="invoice">
					<div class="badge">PROFORMA</div>
					<h1>{{ proforma.Anio }}-{{ proforma.Numero }}</h1>
					<div class="date">Fecha: {{ proforma.FechaReg }}</div>
				</div>
			</div>

			<table border="0" cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th class="no">COD.</th>
						<th class="qty">CANT.</th>
						<th class="pres">PRESENT.</th>
						<th class="desc prod">PRODUCTO</th>
						<th class="desc">LABO.</th>
						<th class="desc">F. VEN.</th>
						<th class="desc">LOTE</th>
						<th class="unit">P. UNIT.</th>
						<th class="total">VALOR</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="(prod, index) in proforma.productos" :key="index">
						<td class="no">{{ prod.Codigo }}</td>
						<td class="qty">{{ prod.Cantidad }}</td>
						<td class="pres">{{ prod.Presentacion }}</td>
						<td class="desc"><h3>{{ prod.Producto }}</h3></td>
						<td class="lab">{{ prod.ProductoMarca }}</td>
						<td class="fven">{{ prod.FechaVen }}</td>
						<td class="lote">{{ prod.Lote }}</td>
						<td class="unit">S/. {{ prod.Precio | moneda }}</td>
						<td class="total">
							S/. {{ (prod.Precio * prod.Cantidad) - prod.Descuento | redondear }}
							<span class="dsct" v-if="prod.Descuento">Dsct: {{ prod.Descuento | moneda('S/. ') }}</span>
						</td>
					</tr>
				</tbody>
				<tfoot>
					<tr>
						<td colspan="7"></td>
						<td class="foot-label">SUBTOTAL</td>
						<td class="foot-val">S/. {{ subTotal | redondear }}</td>
					</tr>
					<tr class="row-total">
						<td colspan="7"></td>
						<td class="foot-label">TOTAL</td>
						<td class="foot-val">S/. {{ subTotal | redondear }}</td>
					</tr>
				</tfoot>
			</table>

			<div id="thanks">¡Muchas Gracias por su preferencia!</div>

			<div id="cuentas">
				<div class="cuentas-titulo">CUENTAS BANCARIAS</div>
				<table class="tabla-cuentas">
					<tbody>
						<tr>
							<td class="banco" rowspan="2">BCP</td>
							<td class="cuenta-label">Cuenta Corriente</td>
							<td class="cuenta-valor">48014968355012</td>
						</tr>
						<tr>
							<td class="cuenta-label">CCI</td>
							<td class="cuenta-valor">00248011496835501227</td>
						</tr>
					</tbody>
				</table>
			</div>

			<div id="notices">
				<div class="notice-title">
					<i class="notice-icon">i</i> NOTA
				</div>
				<div class="notice">Esta proforma tiene validez durante los 5 días posteriores a su emisión.</div>
			</div>
		</main>
	</div>
</template>

<script>
import {API} from '@/store/config.js'

export default {
	mounted () {
		this.getProforma()
		this.getEmpresa()
	},
	data () {
		return {
			API: API,
			idProforma: this.$route.params.id,
			proforma: { productos: [] },
			empresa: {}
		}
	},
	computed: {
		subTotal () {
			let sumatoria = 0
			this.proforma.productos.forEach(prod => {
				sumatoria += (prod.Cantidad * prod.Precio) - prod.Descuento
			})
			return sumatoria
		}
	},
	methods: {
		getProforma () {
			this.axios.get(API + 'proformas/id/' + this.idProforma)
				.then(res => {
					this.proforma = res.data
				})
		},
		getEmpresa () {
			this.axios.get(API + 'empresa/id/' + 1)
				.then(res => {
					this.empresa = res.data
				})
		}
	}
}
</script>

<style scoped>
:root {
	--teal: #0d9c93;
	--teal-dark: #087a73;
	--ink: #22313a;
	--muted: #6b7c85;
	--line: #e2e8ea;
	--bg-soft: #f4f8f8;
}

#invoice-container {
	max-width: 900px;
	margin: 0 auto;
	background: #fff;
	padding: 40px 48px;
	font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
	color: var(--ink);
	box-shadow: 0 4px 24px rgba(13, 156, 147, 0.08);
	border-radius: 10px;
}

/* HEADER */
.inv-header {
	display: flex;
	justify-content: space-between;
	align-items: flex-start;
	padding-bottom: 24px;
	border-bottom: 3px solid var(--teal);
	margin-bottom: 28px;
}

#logo img {
	max-height: 68px;
	object-fit: contain;
}

#company {
	text-align: right;
}

#company .name {
	margin: 0 0 4px;
	font-size: 20px;
	font-weight: 700;
	color: var(--teal-dark);
	letter-spacing: 0.3px;
}

#company .ruc {
	font-size: 13px;
	color: var(--ink);
	margin-bottom: 2px;
}

#company .dir {
	font-size: 12px;
	color: var(--muted);
	max-width: 320px;
}

/* CLIENT / INVOICE DETAILS */
#details {
	display: flex;
	justify-content: space-between;
	align-items: flex-start;
	margin-bottom: 28px;
	gap: 24px;
}

#client {
	background: var(--bg-soft);
	border-left: 4px solid var(--teal);
	border-radius: 8px;
	padding: 14px 18px;
	flex: 1;
}

#client .to {
	font-size: 11px;
	font-weight: 700;
	letter-spacing: 1px;
	color: var(--teal-dark);
	margin-bottom: 4px;
}

#client .name {
	margin: 0 0 6px;
	font-size: 18px;
	font-weight: 700;
}

#client .doc,
#client .address,
#client .email {
	font-size: 13px;
	color: var(--muted);
	line-height: 1.5;
}

#client .email a {
	color: var(--teal-dark);
	text-decoration: none;
}

#invoice {
	text-align: right;
	min-width: 220px;
}

#invoice .badge {
	display: inline-block;
	background: var(--teal);
	color: #fff;
	font-size: 11px;
	font-weight: 700;
	letter-spacing: 1.5px;
	padding: 4px 12px;
	border-radius: 20px;
	margin-bottom: 8px;
}

#invoice h1 {
	margin: 0 0 6px;
	font-size: 28px;
	font-weight: 800;
	color: var(--ink);
}

#invoice .date {
	font-size: 13px;
	color: var(--muted);
}

/* TABLE */
table {
	width: 100%;
	border-collapse: collapse;
	margin-bottom: 28px;
}

thead th {
	background: var(--teal);
	color: #fff;
	font-size: 11px;
	font-weight: 700;
	letter-spacing: 0.5px;
	text-transform: uppercase;
	padding: 10px 8px;
	text-align: center;
}

thead th.prod {
	text-align: left;
	padding-left: 14px;
}

tbody td {
	padding: 12px 8px;
	font-size: 13px;
	text-align: center;
	border-bottom: 1px solid var(--line);
	vertical-align: middle;
}

tbody tr:nth-child(even) {
	background: var(--bg-soft);
}

tbody tr:hover {
	background: #eef6f5;
}

td.desc {
	text-align: left;
	padding-left: 14px;
}

td.desc h3 {
	margin: 0;
	font-size: 13.5px;
	font-weight: 600;
	color: var(--ink);
}

td.no {
	font-size: 11px;
	color: var(--muted);
	font-family: monospace;
}

td.unit {
	font-weight: 600;
}

td.total {
	background: #eafaf7;
	font-weight: 700;
	color: var(--teal-dark);
}

td.total .dsct {
	display: block;
	font-size: 10.5px;
	font-weight: 500;
	color: #d9534f;
	margin-top: 2px;
}

tfoot td {
	padding: 10px 8px;
	font-size: 13px;
	border: none;
}

tfoot .foot-label {
	text-align: right;
	font-weight: 700;
	color: var(--muted);
	padding-right: 16px;
}

tfoot .foot-val {
	text-align: center;
	font-weight: 700;
}

tfoot .row-total .foot-label {
	font-size: 16px;
	color: var(--ink);
}

tfoot .row-total .foot-val {
	font-size: 20px;
	font-weight: 800;
	color: var(--teal-dark);
	background: #eafaf7;
	border-radius: 6px;
}

/* CUENTAS BANCARIAS */
#cuentas {
	margin-bottom: 22px;
}

.cuentas-titulo {
	font-size: 11px;
	font-weight: 700;
	letter-spacing: 1px;
	color: var(--teal-dark);
	margin-bottom: 8px;
	text-transform: uppercase;
}

.tabla-cuentas {
	width: 100%;
	max-width: 420px;
	border-collapse: collapse;
	border: 1px solid var(--teal);
}

.tabla-cuentas td {
	border: 1px solid var(--teal);
	padding: 8px 12px;
	font-size: 13px;
	vertical-align: middle;
}

.tabla-cuentas .banco {
	text-align: center;
	font-weight: 800;
	font-size: 15px;
	color: #fff;
	background: var(--teal);
	width: 70px;
}

.tabla-cuentas .cuenta-label {
	color: var(--muted);
	font-weight: 600;
	width: 130px;
	background: var(--bg-soft);
}

.tabla-cuentas .cuenta-valor {
	font-weight: 700;
	color: var(--ink);
	letter-spacing: 0.3px;
}

/* THANKS + NOTES */
#thanks {
	text-align: center;
	font-size: 18px;
	font-weight: 700;
	color: var(--teal-dark);
	margin-bottom: 22px;
	letter-spacing: 0.3px;
}

#notices {
	background: #fff8ec;
	border: 1px solid #f3dfae;
	border-radius: 8px;
	padding: 14px 18px;
}

.notice-title {
	font-size: 12px;
	font-weight: 700;
	color: #8a6d1d;
	letter-spacing: 0.5px;
	margin-bottom: 4px;
	display: flex;
	align-items: center;
	gap: 6px;
}

.notice-icon {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 16px;
	height: 16px;
	border-radius: 50%;
	background: #e2b74f;
	color: #fff;
	font-style: normal;
	font-size: 11px;
	font-weight: 700;
}

.notice {
	font-size: 12.5px;
	color: #7a6320;
	line-height: 1.5;
}

@media print {
	#invoice-container {
		box-shadow: none;
		border-radius: 0;
		padding: 20px;
	}
}
</style>