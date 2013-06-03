<?php
return <<<ROW
<tr id="rule-$id">
    <td class="url">$display_domain</td>
    <td class="url">$display_pattern</td>
    <td class="url">$display_url</td>
    <td class="timestamp">$date</td>
    <td class="ip">$ip</td>
    <td class="clicks">$clicks</td>
    <td id="rule-actions-$id" class="actions">$action_links</td>
</tr>
ROW;
