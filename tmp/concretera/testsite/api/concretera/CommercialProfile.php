<?php
// Copy this profile into each new version; reprints must not read changing defaults.
function cxCommercialProfile($ruc) {
    if(!in_array((string)$ruc,['20573152531','20602664989'],true))return null;
    return json_decode(file_get_contents(__DIR__.'/../../data/perfil-cotizacion.json'),true,512,JSON_THROW_ON_ERROR);
}
