<?php
session_start();
include_once($_SERVER["DOCUMENT_ROOT"] . "/models/DBManager.php");
//include_once '../clases/BnGeneral.php';
if (!isset($_SESSION['failed_attempts'])) {
	$_SESSION['failed_attempts'] = 0;
	$_SESSION['last_attempt_time'] = 0;
}

$max_attempts = 5;  // Máximo intentos fallidos
$lockout_time = 300; // 5 minutos en segundos

if ($_SESSION['failed_attempts'] >= $max_attempts) {
	$time_since_last_attempt = time() - $_SESSION['last_attempt_time'];

	if ($time_since_last_attempt < $lockout_time) {
		$remaining_time = $lockout_time - $time_since_last_attempt;
		die(json_encode(["isLogin" => false, "message" => "Demasiados intentos fallidos. Inténtelo en {$remaining_time} segundos."]));
	} else {
		$_SESSION['failed_attempts'] = 0; // Reiniciar el contador tras el tiempo de bloqueo
	}
}

function isLogin($user = '', $pass = '')
{
	// $Ssql = "SELECT * FROM Seg_Usuario WHERE Usuario='$user' AND Password=SHA2('$pass',256)";
	$Ssql = "SELECT * FROM Seg_Usuario WHERE Usuario='$user' AND Password=SHA2('$pass',256)";
	//echo json_encode($Ssql);exit();
	$result =  getSQLResultSet($Ssql);
	// var_dump($result);
	return $result;
}

function obtenerPermisos($idUsuarioPerfil)
{
	$Ssql = "SELECT Seg_UsuarioModulo_has_UsuarioPerfil.IdUsuarioPerfil, Seg_UsuarioModulo.UsuarioModulo, Seg_UsuarioModulo_has_UsuarioPerfil.Lectura, Seg_UsuarioModulo_has_UsuarioPerfil.Escritura
			FROM Seg_UsuarioModulo_has_UsuarioPerfil
			INNER JOIN Seg_UsuarioModulo ON Seg_UsuarioModulo_has_UsuarioPerfil.IdUsuarioModulo = Seg_UsuarioModulo.IdUsuarioModulo
			WHERE Seg_UsuarioModulo_has_UsuarioPerfil.IdUsuarioPerfil = $idUsuarioPerfil";
	return getSQLResultSet($Ssql);
}

$Usuario = addslashes($_POST["nameUser"]);
$Password = addslashes($_POST["namePass"]);

$isLogin = isLogin($Usuario, $Password);


if ($isLogin) {
	if ($_SESSION['failed_attempts'] < $max_attempts) {
		$isLogin = mysqli_fetch_assoc($isLogin);


		if (empty($isLogin)) {
			$_SESSION['failed_attempts']++;
			$_SESSION['last_attempt_time'] = time();
			$res = 'Datos Incorrectos';
			$val = false;
		} else {
			$_SESSION['failed_attempts'] = 0;
			$idUsuarioPerfil = 0;
			$permisos = obtenerPermisos($isLogin['IdUsuarioPerfil']);
			$permisos = mysqli_fetch_all($permisos, MYSQLI_ASSOC);
			$nuevosPermisos = [];
			foreach ($permisos as $key => $permiso) {
				$idUsuarioPerfil = $permiso['IdUsuarioPerfil'];
				$idUsuarioPerfil = $permiso['IdUsuarioPerfil'];
				$nuevosPermisos[$permiso['UsuarioModulo']] = array(
					'Lectura' => $permiso['Lectura'],
					'Escritura' => $permiso['Escritura']
				);
			}

			$_SESSION['user'] = $Usuario;
			$diaryProfile = getSQLResultSet("SELECT UsuarioPerfil FROM Seg_UsuarioPerfil WHERE IdUsuarioPerfil=".(int)$isLogin['IdUsuarioPerfil']);
			$diaryRole = $diaryProfile ? mysqli_fetch_assoc($diaryProfile) : [];
			$_SESSION['diario_programador'] = strcasecmp(trim((string)($diaryRole['UsuarioPerfil']??'')), 'PROGRAMADOR')===0;
			$_SESSION['permisos'] = $nuevosPermisos;
			$_SESSION['start'] = time();
			$_SESSION['idPerfil'] = $idUsuarioPerfil;
			$_SESSION['limitado'] = $limitado;
			$_SESSION['idAlmacen'] = $isLogin['IdAlmacen'];
			$_SESSION['hostUrl'] = 'https://' . $_SERVER['HTTP_HOST'];;
			// $_SESSION['expire'] = $_SESSION['start'] + (5 * 60 * 60);
			$res = 'Datos Correctos';
			$val = true;
		}
	} else {
		$res = 'Demasiados Intentos';
		if ($max_attempts - $_SESSION['failed_attempts'] <= 2) {
			$res = 'Le queda un intento';
		}
		$val = false;
	}
}


$results = array(
	'isLogin' => $val,
 'redirect' => !empty($_SESSION['diario_programador']) ? '/views/diario.php' : '/views/V_VentaForm.php',
	'message' => $res,
	'cantidad_res' => $max_attempts - $_SESSION['failed_attempts']

);
echo json_encode($results);
