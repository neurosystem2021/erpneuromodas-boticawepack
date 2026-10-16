from pathlib import Path
root=Path(r'C:\xampp\htdocs\boticav1');out=Path('integration/project-phone')
for rel in ['api/index.php','clases/BnGeneral.php']:
 s=(root/rel).read_text(encoding='utf-8')
 # Include phone wherever receipt/project listing already selects the client identity.
 s=s.replace('Ve_DocVentaCliente.DniRuc, \n            Ve_DocVentaCliente.Cliente,','Ve_DocVentaCliente.DniRuc, \n            Ve_DocVentaCliente.Telefono,\n            Ve_DocVentaCliente.Cliente,')
 p=out/rel;p.parent.mkdir(parents=True,exist_ok=True);p.write_text(s,encoding='utf-8')
rel='imprimir/imprimirReciboServicio.php';s=(root/rel).read_text(encoding='utf-8');needle="    </table>\n    <p> <?php";assert needle in s;s=s.replace(needle,'''        <tr><td>Teléfono:</td><td><?php echo htmlspecialchars($proyecto['Telefono'] ?? 'Sin teléfono registrado',ENT_QUOTES,'UTF-8'); ?></td></tr>
    </table>
    <p> <?php''',1)
s=s.replace("'client'=>$proyecto['Cliente'] ?? '',","'client'=>$proyecto['Cliente'] ?? '', 'phone'=>$proyecto['Telefono'] ?? '',",1).replace("'DNI/RUC: '+receipt.document,","'DNI/RUC: '+receipt.document,'Teléfono: '+(receipt.phone || 'Sin teléfono registrado'),",1)
p=out/rel;p.parent.mkdir(parents=True,exist_ok=True);p.write_text(s,encoding='utf-8')
rel='api/sunat/plugins/dompdf/impirimirReciboServicio.php';s=(root/rel).read_text(encoding='utf-8');needle='    </table>\n    <p>Descripcion:';assert needle in s;s=s.replace(needle,"        <tr><td>Teléfono:</td><td>' . htmlspecialchars($cab['detalle']['Telefono'] ?? 'Sin teléfono registrado',ENT_QUOTES,'UTF-8') . '</td></tr>\n    </table>\n    <p>Descripcion:",1)
p=out/rel;p.parent.mkdir(parents=True,exist_ok=True);p.write_text(s,encoding='utf-8')
p=Path('src/views/Habitaciones.vue');s=p.read_text(encoding='utf-8');needle='<p class="project-client">{{ hab.Cliente || \'Sin cliente asignado\' }}</p>';assert needle in s;s=s.replace(needle,needle+'''<p class="project-card-phone"><i class="fas fa-phone" aria-hidden="true"></i> {{ hab.Telefono || 'Sin teléfono registrado' }}</p>''',1)
needle='              <div class="project-description-section">';assert needle in s;s=s.replace(needle,'''              <p class="project-detail-phone"><i class="fas fa-phone" aria-hidden="true"></i> Teléfono: <b>{{ selectProducto.Telefono || 'Sin teléfono registrado' }}</b></p>
'''+needle,1);p.write_text(s,encoding='utf-8')
