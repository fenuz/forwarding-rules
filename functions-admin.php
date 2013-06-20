<?php

function fr_admin_html_head() {
    echo parse_template('head');
}

function fr_ajax_add_rule() {
    global $ydb;
    yourls_verify_nonce( 'add_rule', $_REQUEST['nonce'], false, 'omg error' );

    $domain = $_REQUEST['domain'];
    $pattern = $_REQUEST['pattern'];
    $url = $_REQUEST['url'];
    $ip = yourls_get_IP();
	$timestamp = date('Y-m-d H:i:s');
    $sql = 
        'INSERT INTO `'.FR_DB_RULES_TABLE.'`'.
        ' SET `domain` = "'.$ydb->escape(mb_strtolower($domain)).'",'.
        ' `pattern` = "'.$ydb->escape($pattern).'",'.
        ' `url` = "'.$ydb->escape($url).'",'.
        ' `timestamp` = "'.$ydb->escape($timestamp).'", '.
        ' `clicks` = 0, '.
        ' `ip` = "'.$ip.'", '.
        ' `user` = "'.$ydb->escape(YOURLS_USER).'"';

    $insert = $ydb->query($sql);
    $return = array();
    if ($insert) {
        $id = $ydb->insert_id;
        $return['id'] = $id;
        $return['message'] = 'Forwarding Rule Added!';
        $return['status'] = 'success';
        $return['domain'] = $domain;
        $return['pattern'] = $pattern;
        $return['url'] = $url;
        $return['timestamp'] = $timestamp;
        $return['html'] = fr_html_add_row($id, $domain, $pattern, $url, strtotime($timestamp), $ip, 0, YOURLS_USER);
    } else {
        $return['status'] = 'fail';
        $return['message'] = 'Failed to add forwarding rule';
    }
         
    echo json_encode($return);
}

function fr_ajax_edit_rule_save() { 
    global $ydb;
    $id = $_REQUEST['id'];
    yourls_verify_nonce( 'edit_rule_'.$id, $_REQUEST['nonce'], false, 'omg error' );

    $domain = $_REQUEST['domain'];
    $pattern = $_REQUEST['pattern'];
    $url = $_REQUEST['url'];
    $ip = yourls_get_IP();
	$timestamp = date('Y-m-d H:i:s');

    $sql =
        'UPDATE `'.FR_DB_RULES_TABLE.'`'.
        ' SET `domain` = "'.$ydb->escape(mb_strtolower($domain)).'",'.
        ' `pattern` = "'.$ydb->escape($pattern).'",'.
        ' `url` = "'.$ydb->escape($url).'",'.
        ' `timestamp` = "'.$ydb->escape($timestamp).'", '.
        ' `ip` = "'.$ip.'", '.
        ' `user` = "'.$ydb->escape(YOURLS_USER).'"'.
        ' WHERE `id` = "'.$ydb->escape($id).'"';

    $update = $ydb->query($sql);
    $result = array();
    if ($update) {
        $rule = fr_admin_get_rule($id);
        $result['status'] = 'success';
        $result['rule'] = array(
            'id' => $id,
            'domain' => $rule->domain,
            'pattern' => $rule->pattern,
            'url' => $rule->url,
            'ip' => $rule->ip,
            'timestamp' => $rule->timestamp,
            'clicks' => $rule->clicks,
            'user' => $rule->user
        );
        $result['html'] = fr_html_add_row($id, $rule->domain, $rule->pattern, $rule->url, strtotime($timestamp), $rule->ip, $rule->clicks, $rule->user);
        $result['message'] = 'Rule updated in the database';
    } else {
        $result['status'] = 'fail';
        $result['message'] = 'Error while editing the forwarding rule';
    }

    echo json_encode($result);
}

// return the edit form for an forwarding rule
function fr_ajax_edit_rule_display() {
    global $ydb;
    $id = $_REQUEST['id'];
    yourls_verify_nonce( 'edit_rule_'.$id, $_REQUEST['nonce'], false, 'omg error' );	
    $rule = fr_admin_get_rule($id);
    $row = parse_template('edit_rule_row', get_object_vars($rule)); 
    echo json_encode( array('html' => $row) );
}

// handle ajax request to delete a forwarding rule
function fr_ajax_delete_rule() {
    global $ydb;
    $id = $_REQUEST['id'];
    yourls_verify_nonce( 'delete_rule_'.$id, $_REQUEST['nonce'], false, 'omg error' );	
	$table = FR_DB_RULES_TABLE;
	$delete = $ydb->query("DELETE FROM `$table` WHERE `id` = '".$ydb->escape($id)."';");
    echo json_encode(array('success'=>$delete));
}

// render admin form for new rule creation
function fr_admin_rules_table_display() {
	// Allow plugins to short-circuit the whole function
	$pre = yourls_apply_filter( 'shunt_fr_rules_table_display', false );
	if ( false !== $pre )
		return $pre;

    $rules = fr_admin_get_rules();
    echo parse_template('forwarding_table', array('rules' => $rules));
}

// render a row for the rule table
function fr_html_add_row($id, $domain, $pattern, $url, $timestamp, $ip, $clicks, $user) {
    $vars = array(
        'id' => $id,
        'ip' => $ip,
        'display_domain' => htmlentities($domain),
        'display_pattern' => htmlentities($pattern),
        'display_url' => htmlentities($url),
        'date' => date( 'M d, Y H:i', $timestamp+( YOURLS_HOURS_OFFSET * 3600 ) ),
        'clicks' => number_format( $clicks, 0, '', ''),
        'user' => $user ? htmlentities($user) : ''
    );

    $delete_link = yourls_nonce_url( 'delete_rule_'.$id,
		yourls_add_query_arg( array( 'id' => $id, 'action' => 'delete_rule' ), yourls_admin_url( 'admin-ajax.php' ) ) 
	);
	
	$edit_link = yourls_nonce_url( 'edit_rule_'.$id,
		yourls_add_query_arg( array( 'id' => $id, 'action' => 'edit_rule' ), yourls_admin_url( 'admin-ajax.php' ) ) 
	);
	
	// Action button links
	$actions = array(
		'edit' => array(
			'href'    => $edit_link,
			'id'      => "edit-rule-button-$id",
			'title'   => 'Edit',
			'anchor'  => 'Edit',
			'onclick' => "edit_forwarding_rule('$id');return false;",
		),
		'delete' => array(
			'href'    => $delete_link,
			'id'      => "delete-rule-button-$id",
			'title'   => 'Delete',
			'anchor'  => 'Delete',
			'onclick' => "remove_forwarding_rule('$id');return false;",
		)
	);
	$action_links = '';
	foreach( $actions as $key => $action ) {
		$onclick = isset( $action['onclick'] ) ? 'onclick="' . $action['onclick'] . '"' : '' ;
		$action_links .= sprintf( '<a href="%s" id="%s" title="%s" class="%s" %s>%s</a>',
			$action['href'], $action['id'], $action['title'], 'button button_'.$key, $onclick, $action['anchor']
		);
	}
    $vars['action_links'] = $action_links;

    return parse_template('rule_row', $vars);
}

// return all redirections
function fr_admin_get_rules() {
    global $ydb;
    $sql = 
        'SELECT * FROM `'.FR_DB_RULES_TABLE.'`'.
        ' ORDER BY `domain`, `pattern`';
    return $ydb->get_results($sql);
}

function fr_admin_get_rule($id) {
    global $ydb;
    $sql = 
        'SELECT * FROM `'.FR_DB_RULES_TABLE.'`'.
        ' WHERE `id` = "'.$ydb->escape($id).'"';
    return $ydb->get_row($sql);
}

