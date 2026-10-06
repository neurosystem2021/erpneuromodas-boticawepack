#!/usr/bin/env bash

# Instalacion de una sola vez. Crea la configuracion privada si no existe,
# instala el hook post-merge y ejecuta la verificacion completa.

set -Eeuo pipefail

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)"
PROJECT_ROOT="$(cd -- "$SCRIPT_DIR/../.." && pwd -P)"
DB_LOCAL="$PROJECT_ROOT/config/database.local.php"
HOOK_FILE="$PROJECT_ROOT/.git/hooks/post-merge"
DEPLOY_SCRIPT="$PROJECT_ROOT/build/deploy/deploy-production.sh"

if [[ "${EUID:-$(id -u)}" -ne 0 ]]; then
  echo '[ERROR] Este instalador debe ejecutarse con sudo.' >&2
  exit 1
fi

find_php() {
  local candidate
  for candidate in \
    /usr/local/lsws/lsphp74/bin/php \
    /usr/local/lsws/lsphp80/bin/php \
    /usr/local/lsws/lsphp81/bin/php \
    /usr/local/lsws/lsphp82/bin/php \
    /usr/bin/php; do
    [[ -x "$candidate" ]] && { printf '%s' "$candidate"; return 0; }
  done
  return 1
}

PHP_BIN="$(find_php || true)"
if [[ -z "$PHP_BIN" ]]; then
  echo '[ERROR] No se encontro PHP para crear la configuracion.' >&2
  exit 1
fi

if [[ ! -f "$DB_LOCAL" ]]; then
  echo 'Configuracion inicial de la base de datos (se guarda fuera de Git).'
  read -r -p 'Host [localhost]: ' DB_HOST
  DB_HOST="${DB_HOST:-localhost}"
  read -r -p 'Puerto [3306]: ' DB_PORT
  DB_PORT="${DB_PORT:-3306}"
  read -r -p 'Base de datos: ' DB_NAME
  read -r -p 'Usuario MySQL: ' DB_USER
  read -r -s -p 'Clave MySQL: ' DB_PASS
  printf '\n'

  if [[ -z "$DB_NAME" || -z "$DB_USER" ]]; then
    echo '[ERROR] La base de datos y el usuario son obligatorios.' >&2
    exit 1
  fi

  umask 077
  export ERP_SETUP_DB_FILE="$DB_LOCAL"
  export ERP_SETUP_DB_HOST="$DB_HOST"
  export ERP_SETUP_DB_PORT="$DB_PORT"
  export ERP_SETUP_DB_NAME="$DB_NAME"
  export ERP_SETUP_DB_USER="$DB_USER"
  export ERP_SETUP_DB_PASS="$DB_PASS"

  "$PHP_BIN" -r '
$config = array("default" => array(
    "host" => getenv("ERP_SETUP_DB_HOST"),
    "port" => getenv("ERP_SETUP_DB_PORT"),
    "dbname" => getenv("ERP_SETUP_DB_NAME"),
    "user" => getenv("ERP_SETUP_DB_USER"),
    "pass" => getenv("ERP_SETUP_DB_PASS"),
    "charset" => "utf8mb4",
));
$contents = "<?php\n\n// Configuracion privada del servidor. No versionar.\nreturn " . var_export($config, true) . ";\n";
if (file_put_contents(getenv("ERP_SETUP_DB_FILE"), $contents) === false) exit(1);
'
  unset ERP_SETUP_DB_FILE ERP_SETUP_DB_HOST ERP_SETUP_DB_PORT ERP_SETUP_DB_NAME ERP_SETUP_DB_USER ERP_SETUP_DB_PASS DB_PASS
  echo "[OK] Configuracion privada creada: $DB_LOCAL"
else
  echo "[OK] Se conserva la configuracion existente: $DB_LOCAL"
fi

if [[ -d "$PROJECT_ROOT/.git" ]]; then
  mkdir -p "$(dirname -- "$HOOK_FILE")"
  if [[ -f "$HOOK_FILE" ]] && ! grep -q '# ERP-AUTO-DEPLOY' "$HOOK_FILE"; then
    cp -p -- "$HOOK_FILE" "$HOOK_FILE.before-erp-standard"
  fi

  if [[ ! -f "$HOOK_FILE" ]]; then
    printf '%s\n' '#!/usr/bin/env bash' > "$HOOK_FILE"
  fi

  if ! grep -q '# ERP-AUTO-DEPLOY' "$HOOK_FILE"; then
    cat >> "$HOOK_FILE" <<'EOF'

# ERP-AUTO-DEPLOY
ERP_PROJECT_ROOT="$(git rev-parse --show-toplevel 2>/dev/null || true)"
if [[ -n "$ERP_PROJECT_ROOT" && -f "$ERP_PROJECT_ROOT/build/deploy/deploy-production.sh" ]]; then
  sudo bash "$ERP_PROJECT_ROOT/build/deploy/deploy-production.sh" --project-root "$ERP_PROJECT_ROOT"
fi
# /ERP-AUTO-DEPLOY
EOF
  fi
  chmod 750 "$HOOK_FILE"
  echo '[OK] Verificacion automatica instalada para los siguientes git pull.'
else
  echo '[AVISO] No se instalo el hook porque esta copia no contiene .git.'
fi

exec bash "$DEPLOY_SCRIPT" --project-root "$PROJECT_ROOT" "$@"
