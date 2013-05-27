<?php
// this is an external file to push it in the global namespace
// while the function is still provided as soon as the Gettext class is called

if(!function_exists ('__')){
  function __() {
    $args = func_get_args();
    $mainTerm = array_shift($args);
    return vsprintf(gettext($mainTerm), $args);
  }
}