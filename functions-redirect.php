<?php

// handle redirection of forwarding rules 
function fr_load_template($request) {
    $host = $_SERVER['HTTP_HOST'];
    // get all the rules from the current domain
    $rules = fr_get_rules($host);
    $requestUri = $_SERVER['REQUEST_URI']; 

    // Allow other plugins to change the request URI
    $requestUri = yourls_apply_filter('fr_request_uri', $requestUri);

    $variants = fr_get_url_variants($host, $requestUri);
    foreach ($variants as $v) {
        fr_match_rules($rules, $v['host'], $v['requestUri']);
    }

    // Allow other plugins to take action when no rule matches
    yourls_do_action('fr_no_rule_matches');
}

// create variants of the url used. for example: add a / or remove www.
function fr_get_url_variants($host, $requestUri) {
    $variants = array(
        array('host' => $host, 'requestUri' => $requestUri),
    );
    // create variant without 'www.' prefixed
    if (preg_match('/^www\..*$/i', $host)) {
        $variants[] = array('host' => substr($host, 4), 'requestUri' => $requestUri);
    }
    // create variants with '/' appended to requestUri
    foreach($variants as $v) {
        if (!preg_match('/.*\/$/', $v['requestUri'])) {
            $variants[] = array('host' => $v['host'], 'requestUri' => $v['requestUri'] . '/');
        }
    }
    return $variants;
}

// applies the firt rule that matches the host and requestUri
function fr_match_rules($rules, $host, $requestUri) {
    foreach ($rules as $r) {
        // first check if the domain name pattern matches the current hostname
        $hostRegex = fr_create_regex_pattern($r->domain);
        if (preg_match($hostRegex, $host)) {            
            // the domain matches, check if the requestUri matches as well
            $requestRegex = fr_create_regex_pattern($r->pattern);
            if (preg_match($requestRegex, $requestUri)) {
                $replacement = fr_create_replacement_pattern($r->url);
                $destination = preg_replace($requestRegex, $replacement, $requestUri);
                fr_update_clicks($r->id);
                fr_log_redirect($r->id);
                yourls_do_action( 'fr_redirect_rule', $requestUri, $r, $destination );
                yourls_redirect( $destination, 301 );
            }
        }
    }
}


// returns the rules setup for the specified host
function fr_get_rules($host) {
    global $ydb;

    // retrieve all redirect rules that match the domain
    $sql =
        'SELECT `domain`, `id`, `pattern`, `url` FROM `'.FR_DB_RULES_TABLE.'`'.
        ' WHERE "'.$ydb->escape($host).'" RLIKE CONCAT("(www\\.)?", REPLACE(`domain`, "*", ".*"))';
    $rules = $ydb->get_results($sql);
    if (!$rules) {
        $rules = array();
    }
    
    // when matching in this order, non wildcard patterns are matched before 
    // wildcard patterns. this ensures that more specific rules overrule less
    // specific rules.
    uasort($rules, function($a, $b) {  
        // order less wildcards before more wildcards
        $wildcardsA = substr_count($a->domain, '*') + substr_count($a->pattern, '*');
        $wildcardsB = substr_count($b->domain, '*') + substr_count($b->pattern, '*');
        $wildcards = $wildcardsA - $wildcardsB;
        if ($wildcards != 0) {
            return $wildcards;
        } 
     
        // order long domain patterns before short domains
        $lenDomain = strlen($b->domain) - strlen($a->domain);
        if ($lenDomain != 0) {
            return $lenDomain;
        }
        
        // order long resource patterns before short patterns
        $lenPattern = strlen($b->pattern) - strlen($a->pattern);
        if ($lenPattern != 0) {
            return $lenPattern;
        }
        
        // alphabetical order
        return strcmp($a->pattern, $b->pattern);
    });
    
    return $rules;
}

// create regex pattern based on the rule pattern
function fr_create_regex_pattern($pattern) {
    $parts = preg_split('/(\\*+)/', $pattern, 0, PREG_SPLIT_DELIM_CAPTURE);
    $regex = '/^';
    foreach($parts as $p) {
        if ($p == '*') {
            $regex .= '(.*)';
        } else {
            $regex .= preg_quote($p, '/');
        }
    }
    $regex .= '$/i';
    return $regex;
}

// create replacement pattern for the specified url rule pattern
function fr_create_replacement_pattern($url) {
    $parts = preg_split('/(\\*+)/', $url, 0, PREG_SPLIT_DELIM_CAPTURE);
    $pattern = '';
    $refCount = 1;
    foreach($parts as $p) {
        if ($p == '*') {
            $pattern .= '${'.$refCount++.'}';
        } else {
            $pattern .= $p;
        }
    }
    return $pattern;
}

// Log a redirect (for stats)
function fr_log_redirect( $id ) {
    // TODO: implement
}

// Update click count on a short URL. Return 0/1 for error/success.
function fr_update_clicks( $id, $clicks = false ) {
	global $ydb;
	$table = FR_DB_RULES_TABLE;
	if ( $clicks !== false && is_int( $clicks ) && $clicks >= 0 )
		$update = $ydb->query( "UPDATE `$table` SET `clicks` = $clicks WHERE `id` = '$id'" );
	else
		$update = $ydb->query( "UPDATE `$table` SET `clicks` = clicks + 1 WHERE `id` = '$id'" );

	return $update;
}

