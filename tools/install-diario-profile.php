<?php
function installDiaryProgrammerProfile($db){
 $db->beginTransaction();try{
 $s=$db->prepare("SELECT IdUsuarioPerfil FROM Seg_UsuarioPerfil WHERE UPPER(TRIM(UsuarioPerfil))='PROGRAMADOR' AND IFNULL(Anulado,0)=0 LIMIT 1");$s->execute();$profile=(int)$s->fetchColumn();if(!$profile){$s=$db->prepare('INSERT INTO Seg_UsuarioPerfil(UsuarioPerfil,Anulado,FechaReg,UsuarioReg) VALUES(?,0,NOW(),?)');$s->execute(['PROGRAMADOR','sistema']);$profile=(int)$db->lastInsertId();}
 $s=$db->prepare('SELECT IdUsuarioModulo FROM Seg_UsuarioModulo WHERE LOWER(UsuarioModulo)=? LIMIT 1');$s->execute(['diario.php']);$module=(int)$s->fetchColumn();if(!$module){$s=$db->prepare('INSERT INTO Seg_UsuarioModulo(UsuarioModulo) VALUES(?)');$s->execute(['diario.php']);$module=(int)$db->lastInsertId();}
 $s=$db->prepare('SELECT IdUsuarioPerfil FROM Seg_UsuarioModulo_has_UsuarioPerfil WHERE IdUsuarioPerfil=? AND IdUsuarioModulo=?');$s->execute([$profile,$module]);if(!$s->fetchColumn()){$s=$db->prepare('INSERT INTO Seg_UsuarioModulo_has_UsuarioPerfil(IdUsuarioPerfil,IdUsuarioModulo,Lectura,Escritura,Anulado) VALUES(?,?,1,1,0)');$s->execute([$profile,$module]);}
 $db->commit();return $profile;
 }catch(Throwable $e){if($db->inTransaction())$db->rollBack();throw $e;}
}
