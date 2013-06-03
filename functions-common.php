<?php

function parse_template($template, array $vars = array()) {
    extract($vars, EXTR_SKIP);
    return require( dirname(__FILE__).'/templates/'.$template.'.tpl.php' );
}

