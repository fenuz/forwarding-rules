// initialize
$(document).ready(function() {
    $('#forwarding_table').dataTable();
});

// Add a new forwarding rule
function add_forwarding_rule() {
    if ($('#add-rule-button').hasClass('disabled')) {
        return false;
    }
    
    // get new rule values
    var ndomain = $.trim($("#add-rule-domain").val());
    var npattern = $.trim($("#add-rule-pattern").val());
    var newurl = $.trim($("#add-rule-url").val());
    var nonce = $("#nonce-add-rule").val();
    
    // validation
    if (!ndomain) {
        return;
    }
    if (!npattern) {
        return;
    }
    if (!newurl || newurl === 'http://' || newurl === 'https://') {
        return;
    }
    
    // submit new rule
    add_loading("#add-rule-button");
    $.getJSON(
        ajaxurl,
        {
            action: 'add_rule', 
            url: newurl, 
            domain: ndomain, 
            pattern: npattern, 
            nonce: nonce
        }, 
        function(data) {
            if (data.status === 'success') {
                $('#forwarding_table tbody').prepend(data.html).trigger("update");
                zebra_forwarding_table();
                increment();
            }

            reset_forwarding_rule();
            end_loading("#add-rule-button");
            end_disable("#add-rule-button");

            feedback(data.message, data.status);
        }
    );
}

// Display the edit interface
function edit_forwarding_rule(id) {
    if ($('#edit-rule-button-' + id).hasClass('disabled')) {
        return false;
    }
    
    add_loading('#rule-actions-' + id + ' .button');
    var nonce = get_var_from_query($('#edit-rule-button-' + id).attr('href'), 'nonce');
    $.getJSON(
        ajaxurl,
        {
            action: "edit_rule_display", 
            nonce: nonce, 
            id: id
        },
        function(data) {
            $("#rule-" + id).after(data.html);
            $("#edit-rule-domain-" + id).focus();
            end_loading('#rule-actions-' + id + ' .button');
        }
    );
}

// Delete a rule
function remove_forwarding_rule(id) {
    if ($('#delete-rule-button-' + id).hasClass('disabled')) {
        return false;
    }
    if (!confirm('Really delete?')) {
        return;
    }
    var nonce = get_var_from_query($('#delete-rule-button-' + id).attr('href'), 'nonce');
    $.getJSON(
        ajaxurl,
        {
            action: "delete_rule", 
            id: id, 
            nonce: nonce
        },
        function(data) {
            if (data.success === 1) {
                $("#rule-" + id).fadeOut(function() {
                    $(this).remove();
                    if ($('#forwarding_table tbody tr').length === 1) {
                        $('#nourl_found').css('display', '');
                    }
                    zebra_forwarding_table();
                });
            } else {
                alert('something wrong happened while deleting :/');
            }
        }
    );
}

// Ready to add another rule
function reset_forwarding_rule() {
    $('#add-rule-domain').val('').focus();
    $('#add-rule-pattern').val('/');
    $('#add-rule-url').val('http://');
}

// Prettify table with odd & even rows
function zebra_forwarding_table() {
    $("#forwarding_table tbody tr:even").removeClass('odd').addClass('even');
    $("#forwarding_table tbody tr:odd").removeClass('even').addClass('odd');
    $('#forwarding_table tbody').trigger("update");
}

// Cancel edition of a link
function hide_rule_edit(id) {
    $("#edit-rule-" + id).fadeOut(200, function() {
        $(this).remove();
        end_disable('#rule-actions-' + id + ' .button');
    });
}

// Save edition of a link
function edit_rule_save(id) {
    add_loading("#edit-rule-close-button-" + id);
    var newdomain = $.trim($("#edit-rule-domain-" + id).val());
    var newpattern = $.trim($("#edit-rule-pattern-" + id).val());
    var newurl = $.trim($("#edit-rule-url-" + id).val());
    var nonce = $('#nonce-edit-rule-' + id).val();
    $.getJSON(
        ajaxurl,
        {
            action: 'edit_rule_save', 
            url: newurl, 
            id: id, 
            domain: newdomain, 
            pattern: newpattern, 
            nonce: nonce
        },
        function(data) {
            if (data.status === 'success') {
                $('#rule-' + id).replaceWith(data.html);
            }
            feedback(data.message, data.status);
            end_loading("#edit-rule-close-button-" + id);
            end_disable("#rule-actions-" + id + ' .button');
            hide_rule_edit(id);
        }
    );
}


