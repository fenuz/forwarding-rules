<?php

$fr_plugin_dirname = basename(dirname(dirname(__FILE__))); 

return 
    '<link rel="stylesheet" href="'.yourls_site_url(false).'/user/plugins/'.$fr_plugin_dirname.'/style.css" type="text/css" media="screen" />'.
    '<script type="text/javascript" src="'.yourls_site_url(false).'/user/plugins/'.$fr_plugin_dirname.'/script.js"></script>';

