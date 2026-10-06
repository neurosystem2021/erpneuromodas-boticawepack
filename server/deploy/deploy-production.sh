#!/usr/bin/env bash

# Despliegue y verificacion estandar para los ERP alojados en OpenLiteSpeed.
# No almacena secretos: config/database.local.php se crea una sola vez mediante
# install-production.sh y esta excluido de Git.

set -Eeuo pipefail

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)"
PROJECT_ROOT="$(cd -- "$SCRIPT_DIR/../.." && pwd -P)"
DOMAIN=""
WEB_USER=""
WEB_GROUP=""
GLOBAL_WEB_USER=""
GLOBAL_WEB_GROUP=""
PHP_BIN=""
RESTART_SERVER=1
HTTP_CHECK=1
ALIGN_WEB_PHP=1

usage() {
  cat <<'EOF'
Uso: sudo bash build/deploy/deploy-production.sh [opciones]

Opciones:
  --project-root RUTA   Raiz del proyecto (se detecta automaticamente)
  --domain DOMINIO      Dominio del sitio (por defecto, nombre del directorio)
  --web-user USUARIO    Usuario de OpenLiteSpeed (se detecta automaticamente)
  --web-group GRUPO     Grupo de OpenLiteSpeed (se detecta automaticamente)
  --php-bin RUTA        Binario PHP CLI para las verificaciones
  --keep-web-php        No alinear el manejador web heredado con PHP 7.4
  --no-restart          No reiniciar OpenLiteSpeed
  --no-http-check       No comprobar la API publica
  -h, --help            Mostrar esta ayuda
EOF
}

while (($#)); do
  case "$1" in
    --project-root) PROJECT_ROOT="$2"; shift 2 ;;
    --domain) DOMAIN="$2"; shift 2 ;;
    --web-user) WEB_USER="$2"; shift 2 ;;
    --web-group) WEB_GROUP="$2"; shift 2 ;;
    --php-bin) PHP_BIN="$2"; shift 2 ;;
    --keep-web-php) ALIGN_WEB_PHP=0; shift ;;
    --no-restart) RESTART_SERVER=0; shift ;;
    --no-http-check) HTTP_CHECK=0; shift ;;
    -h|--help) usage; exit 0 ;;
    *) echo "[ERROR] Opcion desconocida: $1" >&2; usage >&2; exit 2 ;;
  esac
done

PROJECT_ROOT="$(cd -- "$PROJECT_ROOT" && pwd -P)"
[[ -n "$DOMAIN" ]] || DOMAIN="$(basename -- "$PROJECT_ROOT")"
if [[ ! "$DOMAIN" =~ ^[A-Za-z0-9]([A-Za-z0-9.-]*[A-Za-z0-9])?$ ]]; then
  echo "[ERROR] Dominio invalido: $DOMAIN" >&2
  exit 2
fi
SITE_HOME="$(dirname -- "$PROJECT_ROOT")"
SITE_NAME="$(basename -- "$SITE_HOME")"
VHOST_CONFIG="/usr/local/lsws/conf/vhosts/$DOMAIN/vhost.conf"
GLOBAL_CONFIG="/usr/local/lsws/conf/httpd_config.conf"

if [[ "${EUID:-$(id -u)}" -ne 0 ]]; then
  echo '[ERROR] Ejecute este verificador con sudo para poder corregir permisos y reiniciar OpenLiteSpeed.' >&2
  exit 1
fi

read_config_value() {
  local key="$1"
  local file="$2"
  [[ -r "$file" ]] || return 1
  awk -v wanted="$key" '$1 == wanted { print $2; exit }' "$file"
}

# OpenLiteSpeed puede declarar un usuario propio para el vhost y, aun asi,
# ejecutar LSPHP con el usuario global cuando suExec esta desactivado. Se
# conservan ambas identidades para que la configuracion privada sea legible en
# los dos modos sin dejarla disponible para todos los usuarios del servidor.
GLOBAL_WEB_USER="$(read_config_value user "$GLOBAL_CONFIG" || true)"
[[ "$GLOBAL_WEB_USER" != *'$'* ]] || GLOBAL_WEB_USER=""
[[ -n "$GLOBAL_WEB_USER" ]] || GLOBAL_WEB_USER="nobody"

GLOBAL_WEB_GROUP="$(read_config_value group "$GLOBAL_CONFIG" || true)"
[[ "$GLOBAL_WEB_GROUP" != *'$'* ]] || GLOBAL_WEB_GROUP=""
[[ -n "$GLOBAL_WEB_GROUP" ]] || GLOBAL_WEB_GROUP="$(id -gn "$GLOBAL_WEB_USER" 2>/dev/null || true)"
[[ -n "$GLOBAL_WEB_GROUP" ]] || GLOBAL_WEB_GROUP="nogroup"

if [[ -z "$WEB_USER" ]]; then
  WEB_USER="$(read_config_value extUser "$VHOST_CONFIG" || true)"
  [[ "$WEB_USER" != *'$'* ]] || WEB_USER=""
  [[ -n "$WEB_USER" ]] || WEB_USER="$(read_config_value user "$GLOBAL_CONFIG" || true)"
  [[ "$WEB_USER" != *'$'* ]] || WEB_USER=""
  [[ -n "$WEB_USER" ]] || WEB_USER="nobody"
fi

if [[ -z "$WEB_GROUP" ]]; then
  WEB_GROUP="$(read_config_value extGroup "$VHOST_CONFIG" || true)"
  [[ "$WEB_GROUP" != *'$'* ]] || WEB_GROUP=""
  [[ -n "$WEB_GROUP" ]] || WEB_GROUP="$(read_config_value group "$GLOBAL_CONFIG" || true)"
  [[ "$WEB_GROUP" != *'$'* ]] || WEB_GROUP=""
  [[ -n "$WEB_GROUP" ]] || WEB_GROUP="$(id -gn "$WEB_USER" 2>/dev/null || true)"
  [[ -n "$WEB_GROUP" ]] || WEB_GROUP="nogroup"
fi

if ! id "$WEB_USER" >/dev/null 2>&1; then
  echo "[ERROR] No existe el usuario web detectado: $WEB_USER" >&2
  exit 1
fi
if ! getent group "$WEB_GROUP" >/dev/null 2>&1; then
  echo "[ERROR] No existe el grupo web detectado: $WEB_GROUP" >&2
  exit 1
fi
if ! id "$GLOBAL_WEB_USER" >/dev/null 2>&1; then
  echo "[ERROR] No existe el usuario global de OpenLiteSpeed: $GLOBAL_WEB_USER" >&2
  exit 1
fi
if ! getent group "$GLOBAL_WEB_GROUP" >/dev/null 2>&1; then
  echo "[ERROR] No existe el grupo global de OpenLiteSpeed: $GLOBAL_WEB_GROUP" >&2
  exit 1
fi

if [[ -z "$PHP_BIN" ]]; then
  for candidate in \
    /usr/local/lsws/lsphp74/bin/php \
    /usr/local/lsws/lsphp80/bin/php \
    /usr/local/lsws/lsphp81/bin/php \
    /usr/local/lsws/lsphp82/bin/php \
    /usr/bin/php; do
    if [[ -x "$candidate" ]]; then
      PHP_BIN="$candidate"
      break
    fi
  done
fi

if [[ -z "$PHP_BIN" || ! -x "$PHP_BIN" ]]; then
  echo '[ERROR] No se encontro un binario PHP ejecutable.' >&2
  exit 1
fi

# Este ERP y sus dependencias heredadas estan validados con PHP 7.4. CyberPanel
# guarda el manejador del sitio en el vhost; si apunta a otra version se corrige
# solamente esa linea y se conserva una copia recuperable del archivo original.
LEGACY_LSPHP="/usr/local/lsws/lsphp74/bin/lsphp"
if ((ALIGN_WEB_PHP)) && [[ -r "$VHOST_CONFIG" && -x "$LEGACY_LSPHP" ]]; then
  CURRENT_LSPHP="$(awk '$1 == "path" && $2 ~ /\/lsphp[0-9]+\/bin\/lsphp$/ { print $2; exit }' "$VHOST_CONFIG")"
  if [[ -n "$CURRENT_LSPHP" && "$CURRENT_LSPHP" != "$LEGACY_LSPHP" ]]; then
    VHOST_BACKUP="$VHOST_CONFIG.before-erp-standard"
    [[ -e "$VHOST_BACKUP" ]] || cp -p -- "$VHOST_CONFIG" "$VHOST_BACKUP"
    VHOST_TEMP="$(mktemp)"
    awk -v php_path="$LEGACY_LSPHP" '
      $1 == "path" && $2 ~ /\/lsphp[0-9]+\/bin\/lsphp$/ {
        match($0, /^[[:space:]]*/)
        print substr($0, RSTART, RLENGTH) "path " php_path
        next
      }
      { print }
    ' "$VHOST_CONFIG" > "$VHOST_TEMP"
    cat "$VHOST_TEMP" > "$VHOST_CONFIG"
    rm -f -- "$VHOST_TEMP"
    echo "[OK] Manejador web alineado: $CURRENT_LSPHP -> $LEGACY_LSPHP"
    echo "[INFO] Respaldo del vhost: $VHOST_BACKUP"
  fi
fi

DB_LOCAL="$PROJECT_ROOT/config/database.local.php"
VERIFY_SCRIPT="$PROJECT_ROOT/build/deploy/verify-production.php"
MIGRATE_SALE_FIELDS_SCRIPT="$PROJECT_ROOT/build/deploy/migrate-sale-fields.php"
MIGRATE_CASH_CLOSE_FIELDS_SCRIPT="$PROJECT_ROOT/build/deploy/migrate-cash-close-fields.php"
LOG_DIR="$SITE_HOME/logs"
ERROR_LOG="$LOG_DIR/$SITE_NAME.error_log"
ACCESS_LOG="$LOG_DIR/$SITE_NAME.access_log"

if [[ ! -f "$DB_LOCAL" ]]; then
  echo "[ERROR] Falta $DB_LOCAL" >&2
  echo 'Ejecute primero: sudo bash build/deploy/install-production.sh' >&2
  exit 1
fi

echo "[INFO] Proyecto: $PROJECT_ROOT"
echo "[INFO] Dominio: $DOMAIN"
echo "[INFO] Proceso web: $WEB_USER:$WEB_GROUP"
echo "[INFO] Compatibilidad suExec/global: $GLOBAL_WEB_USER:$GLOBAL_WEB_GROUP"
echo "[INFO] PHP de verificacion: $PHP_BIN ($("$PHP_BIN" -r 'echo PHP_VERSION;'))"

# El propietario cubre el modo suExec del vhost y el grupo cubre instalaciones
# donde OpenLiteSpeed termina ejecutando LSPHP con su usuario global.
chown "$WEB_USER:$GLOBAL_WEB_GROUP" "$DB_LOCAL"
chmod 640 "$DB_LOCAL"

# OpenLiteSpeed debe poder abrir sus propios archivos antes de ejecutar PHP.
install -d -o "$WEB_USER" -g "$GLOBAL_WEB_GROUP" -m 770 "$LOG_DIR"
touch "$ERROR_LOG" "$ACCESS_LOG"
chown "$WEB_USER:$GLOBAL_WEB_GROUP" "$ERROR_LOG" "$ACCESS_LOG"
chmod 660 "$ERROR_LOG" "$ACCESS_LOG"

[[ -r "$VERIFY_SCRIPT" ]] || {
  echo "[ERROR] No existe el verificador: $VERIFY_SCRIPT" >&2
  exit 1
}

[[ -r "$MIGRATE_SALE_FIELDS_SCRIPT" ]] || {
  echo "[ERROR] No existe la migracion de ventas: $MIGRATE_SALE_FIELDS_SCRIPT" >&2
  exit 1
}

[[ -r "$MIGRATE_CASH_CLOSE_FIELDS_SCRIPT" ]] || {
  echo "[ERROR] No existe la migracion de cierre de caja: $MIGRATE_CASH_CLOSE_FIELDS_SCRIPT" >&2
  exit 1
}

"$PHP_BIN" -l "$PROJECT_ROOT/config/database.php" >/dev/null
"$PHP_BIN" -l "$PROJECT_ROOT/api/index.php" >/dev/null
"$PHP_BIN" "$MIGRATE_SALE_FIELDS_SCRIPT" "$PROJECT_ROOT"
"$PHP_BIN" "$MIGRATE_CASH_CLOSE_FIELDS_SCRIPT" "$PROJECT_ROOT"
"$PHP_BIN" "$VERIFY_SCRIPT" "$PROJECT_ROOT"

if ((RESTART_SERVER)); then
  # OpenLiteSpeed mantiene procesos PHP separados del servidor principal. Esta
  # marca reinicia de forma segura solo el proceso PHP asociado al sitio.
  touch "$SITE_HOME/.lsphp_restart.txt"
  chown "$WEB_USER:$WEB_GROUP" "$SITE_HOME/.lsphp_restart.txt"
  if [[ -x /usr/local/lsws/bin/lswsctrl ]]; then
    /usr/local/lsws/bin/lswsctrl restart
  else
    echo '[ERROR] No existe /usr/local/lsws/bin/lswsctrl.' >&2
    exit 1
  fi
fi

if ((HTTP_CHECK)); then
  command -v curl >/dev/null 2>&1 || {
    echo '[ERROR] curl es necesario para comprobar la API publica.' >&2
    exit 1
  }

  response_file="$(mktemp)"
  trap 'rm -f -- "$response_file"' EXIT
  http_code="$(curl -sS -k --max-time 25 \
    --resolve "$DOMAIN:443:127.0.0.1" \
    -o "$response_file" -w '%{http_code}' \
    "https://$DOMAIN/api/index.php/ventas/tipos?q=" || true)"

  if [[ "$http_code" != '200' ]] || ! grep -Eq '^[[:space:]]*\[' "$response_file"; then
    echo "[ERROR] La API publica no supero la prueba (HTTP ${http_code:-000})." >&2
    echo "[INFO] Ultimas lineas de $ERROR_LOG:" >&2
    tail -n 40 "$ERROR_LOG" >&2 || true
    exit 1
  fi
  echo '[OK] API publica disponible y devolviendo JSON.'
fi

echo '[OK] DESPLIEGUE VERIFICADO.'
