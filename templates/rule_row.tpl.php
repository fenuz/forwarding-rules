<?php
$row = <<<ROW
<tr id="rule-$id">
    <td class="url" title="$display_domain">$display_domain</td>
    <td class="url" title="$display_pattern">$display_pattern</td>
    <td class="url" title="$display_url">$display_url</td>
    <td class="timestamp" title="$date">$date</td>
ROW;

if (FR_SHOW_IP_COLUMN) {
    $row .= "<td class=\"ip\" title=\"$ip\">$ip</td>";    
}
if (FR_SHOW_CLICKS_COLUMN) {
    $row .= "<td class=\"clicks\" title=\"$clicks\">$clicks</td>";    
}
if (FR_SHOW_USER_COLUMN) {
    $row .= "<td class=\"user\" title=\"$clicks\">$user</td>";    
}

$row .= <<<ROW
    <td id="rule-actions-$id" class="actions">$action_links</td>
</tr>
ROW;

return $row;
