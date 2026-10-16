# Gateway WhatsApp local

Servicio aislado basado en WPPConnect. Escucha únicamente en
`127.0.0.1:8787`; las sesiones se almacenan en `.sessions/` y no se versionan.

## Instalar

```powershell
cd C:\xampp\htdocs\boticav1-crm\whatsapp-gateway
npm install
```

## Iniciar

```powershell
powershell -ExecutionPolicy Bypass -File .\start.ps1
```

Después abrir el CRM, entrar en **Comunicaciones** y seleccionar
**Generar código QR**. En el teléfono: WhatsApp → Dispositivos vinculados →
Vincular dispositivo.

El QR y la sesión son credenciales. No deben compartirse ni publicarse.
Para producción, sustituir la clave local y evaluar la API oficial de Meta.
