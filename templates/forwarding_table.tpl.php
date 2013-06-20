<?php
$output = <<<TEMPLATE
<h2>Forwarding Rules</h2>
<div id="new_forwarding_rule"><div>
    <form id="new_forwarding_rule_form" action="javascript:add_forwarding_rule();" method="get"><div>
        <strong>Domain</strong>:
        <input type="text" id="add-rule-domain" value="" class="text" size="40">
        <strong>Src</strong>:
        <input type="text" id="add-rule-pattern" value="/" class="text" size="40">
        <strong>Dest</strong>:
        <input type="text" id="add-rule-url" value="http://" class="text" size="40">
TEMPLATE;
$output .= yourls_nonce_field( 'add_rule', 'nonce-add-rule', false, false ); 
$output .= <<<TEMPLATE
        <input type="button" id="add-rule-button" value="Add Rule" class="button" onclick="add_forwarding_rule();">
    </div></form>
</div></div>
<div class="forwarding-table-container">
<table id="forwarding_table" class="tblSorter" cellpadding="0" cellspacing="1">
    <thead>
        <tr>
            <th>Domain</th>
            <th>Source Pattern</th>
            <th>Destination Pattern</th>
            <th class="timestamp">Date</th>
TEMPLATE;

if (FR_SHOW_IP_COLUMN) {
    $output .= "<th>IP</th>";    
}
if (FR_SHOW_CLICKS_COLUMN) {
    $output .= "<th>Clicks</th>";    
}
if (FR_SHOW_USER_COLUMN) {
    $output .= "<th>User</th>";    
}

$output .= <<<TEMPLATE
            <th class="actions">Actions</th>
        </tr>
    </thead>
    <tbody>
TEMPLATE;

foreach($rules as $r) {
    $output .= fr_html_add_row($r->id, $r->domain, $r->pattern, $r->url, strtotime( $r->timestamp ), $r->ip, $r->clicks, $r->user);
}

$output .= <<<TEMPLATE
    </tbody>
</table>
</div>
TEMPLATE;

return $output;
