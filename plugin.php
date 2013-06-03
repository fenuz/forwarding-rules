<?php
/*
Plugin Name: Forwarding Rules 
Plugin URI: http://www.kennisnet.nl
Description: Allows administrators to define redirect/forwarding rules. A forwarding rule translates and redirects URL's that match a Source pattern to URL's defined by a destination pattern.
Version: 1.1
Author: Frank Matheron <frankmatheron@gmail.com>
Author URI: https://github.com/fenuz
*/

require(dirname(__FILE__) . '/functions-common.php');
require(dirname(__FILE__) . '/functions-install.php');
require(dirname(__FILE__) . '/functions-admin.php');
require(dirname(__FILE__) . '/functions-redirect.php');

define('FR_VERSION', '1.0');
define('FR_DB_VERSION', '1.0');
define('FR_DB_RULES_TABLE', 'fr_rules');

// plugin activation hook (for db setup)
yourls_add_action('activated_plugin', 'fr_activated_plugin');

// admin form hooks
yourls_add_action('html_head', 'fr_admin_html_head');
yourls_add_action('admin_page_after_table', 'fr_admin_rules_table_display');
// admin ajax hooks
yourls_add_action('yourls_ajax_add_rule', 'fr_ajax_add_rule');
yourls_add_action('yourls_ajax_delete_rule', 'fr_ajax_delete_rule');
yourls_add_action('yourls_ajax_edit_rule_display', 'fr_ajax_edit_rule_display');
yourls_add_action('yourls_ajax_edit_rule_save', 'fr_ajax_edit_rule_save');

// hook in which we deal with redirection 
yourls_add_action('pre_load_template', 'fr_load_template');


/**
 * Authmgr Plugin Integration
 */
class FRAuthMgrCapabilities {
    const ShowRules = 'ShowForwardingRules';
    const AddRule = 'AddForwardingRule';
    const EditRule = 'EditForwardingRule';
    const DeleteRule = 'DeleteForwardingRule';
}

yourls_add_filter('authmgr_role_capabilities_filter', 'fr_authmgr_add_capabilities');
function fr_authmgr_add_capabilities($authmgr_role_capabilities) {
    $authmgr_role_capabilities[AuthmgrRoles::Administrator][] = FRAuthMgrCapabilities::ShowRules; 
    $authmgr_role_capabilities[AuthmgrRoles::Administrator][] = FRAuthMgrCapabilities::AddRule; 
    $authmgr_role_capabilities[AuthmgrRoles::Administrator][] = FRAuthMgrCapabilities::EditRule; 
    $authmgr_role_capabilities[AuthmgrRoles::Administrator][] = FRAuthMgrCapabilities::DeleteRule; 
    return $authmgr_role_capabilities;
}

yourls_add_filter('authmgr_action_capability_map_filter', 'fr_authmgr_action_capability_map_filter');
function fr_authmgr_action_capability_map_filter($action_capability_map) {
    $action_capability_map['add_rule'] = FRAuthMgrCapabilities::AddRule;
    $action_capability_map['edit_rule_save'] = FRAuthMgrCapabilities::EditRule; 
    $action_capability_map['edit_rule_display'] = FRAuthMgrCapabilities::EditRule; 
    $action_capability_map['delete_rule'] = FRAuthMgrCapabilities::DeleteRule; 
    return $action_capability_map;	
}

yourls_add_filter('shunt_fr_rules_table_display', 'fr_authmgr_intercept_rules_table_display');
function fr_authmgr_intercept_rules_table_display($default) {
    if (function_exists('authmgr_have_capability')) {
        return !authmgr_have_capability( FRAuthMgrCapabilities::ShowRules );     
    }
    return $default;
}

