<?php
function diaryAccessIdentity(PDO $db,$username){
 $q=$db->prepare('SELECT u.Usuario,u.IdUsuarioPerfil,p.UsuarioPerfil FROM Seg_Usuario u JOIN Seg_UsuarioPerfil p ON p.IdUsuarioPerfil=u.IdUsuarioPerfil WHERE u.Usuario=? AND COALESCE(u.Anulado,0)=0');$q->execute([$username]);$u=$q->fetch(PDO::FETCH_ASSOC);
 $super=$u&&(int)$u['IdUsuarioPerfil']===1&&strcasecmp($u['Usuario'],'superadmin')===0;
 $programmer=$u&&strcasecmp(trim($u['UsuarioPerfil']),'PROGRAMADOR')===0;
 return ['allowed'=>(bool)($super||$programmer),'superadmin'=>(bool)$super,'programador'=>(bool)$programmer];
}
