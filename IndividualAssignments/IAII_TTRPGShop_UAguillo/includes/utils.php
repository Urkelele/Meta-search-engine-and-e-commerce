<?php
function clear_post($redirect = null) {

    // Si no se especifica URL, recarga la misma página
    if ($redirect === null) {
        $redirect = $_SERVER['PHP_SELF'];

        // Mantener los parámetros GET existentes
        if (!empty($_GET)) {
            $redirect .= '?' . http_build_query($_GET);
        }
    }

    // Redirigir a la URL indicada
    header("Location: $redirect");
    exit;
}
?>
