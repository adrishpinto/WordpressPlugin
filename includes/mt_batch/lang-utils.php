<?php
// uses cookie to redirect
function activeloc_get_current_lang()
{  
    $supported_langs = array_keys(activeloc_get_supported_languages());

    if (isset($_COOKIE['activeloc_lang']) && in_array($_COOKIE['activeloc_lang'], $supported_langs)) {
        return $_COOKIE['activeloc_lang'];
    }

    return 'en'; 
}
