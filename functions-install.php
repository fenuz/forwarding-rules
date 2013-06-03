<?php

// run plugin installation on activation
function fr_activated_plugin($plugin) {
    if ($plugin[0] == basename(__DIR__).'/plugin.php') {
        fr_install();
    }
}

// create rules tables (if they do not exist)
function fr_install() {
    global $ydb;

    $create_tables = array();
    $create_tables[FR_DB_RULES_TABLE] =
        'CREATE TABLE IF NOT EXISTS `'.FR_DB_RULES_TABLE.'` ('.
        '`id` MEDIUMINT NOT NULL AUTO_INCREMENT,'.
        '`domain` varchar(200) NOT NULL,'.
        '`pattern` varchar(2000) NOT NULL,'.
        '`url` text NOT NULL,'.
        '`timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,'.
        '`ip` VARCHAR(41) NOT NULL,'.
        '`clicks` INT(10) UNSIGNED NOT NULL,'.
        ' PRIMARY KEY  (`id`),'.
        ' KEY `domain` (`domain`),'.
        ' KEY `timestamp` (`timestamp`),'.
        ' KEY `ip` (`ip`)'.
        ');';
    
    $ydb->show_errors = true;
    
    // Create tables
    foreach ( $create_tables as $table_name => $table_query ) {
        $ydb->query( $table_query );
    }
        
    // Insert data into tables
    yourls_update_option( 'fr_version', FR_VERSION );
    yourls_update_option( 'fr_db_version', FR_DB_VERSION ); 
}

