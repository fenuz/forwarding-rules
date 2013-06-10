<?php

$colspan = 4;
if (FR_SHOW_IP_COLUMN) {
    $colspan++;
}
if (FR_SHOW_CLICKS_COLUMN) {
    $colspan++;
}

$output = <<<ROW
<tr id="edit-rule-$id" class="edit-row">
    <td colspan="$colspan">
        <strong>Domain</strong>:
        <input type="text" id="edit-rule-domain-$id" value="$domain" class="text" size="40">
        <strong>Src</strong>:
        <input type="text" id="edit-rule-pattern-$id" value="$pattern" class="text" size="40">
        <strong>Dest</strong>:
        <input type="text" id="edit-rule-url-$id" value="$url" class="text" size="40">
    </td>
    <td>
        <input type="button" id="edit-rule-button-$id" value="Save" class="button" onclick="edit_rule_save($id);">
        <input type="button" id="edit-rule-close-button-$id" value="X" class="button" onclick="hide_rule_edit($id);">
ROW;

$output .= yourls_nonce_field( 'edit_rule_'.$id, 'nonce-edit-rule-'.$id, false, false ); 

$output .= <<<ROW
    </td>
</tr>
ROW;

return $output;
