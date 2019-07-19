jQuery(document).ready(function() {
    // wrap update file elements with form tag ()
    $('input[name="update_file"]').closest('table').wrap('<form></form>');
});

function checkExpanded(a) {
    if($(a).hasClass("expanded")) {
        $(a).parents('table').first(".elb-subtable").find("tbody").first().show();
        $(a).find(".ui-icon").addClass("ui-icon-triangle-1-se");
    } else {
        $(a).parents('table').first(".elb-subtable").find("tbody").first().hide();
        $(a).find(".ui-icon").addClass("ui-icon-triangle-1-e");
    }
}

function toggleSubtable(a, ajax) {
    ajax = ajax || false;
    var td = $(a).closest('table').find('tbody > tr > td');
    if(ajax) {
        if(!td.hasClass('loaded')) {
            var ajaxdata = {
                hook: 'common',
                action: 'toggle_subtable',
                params: {
                    table: $(a).attr('data-table'),
                    position_id: $(a).attr('data-pos-id')
                }
            };
            $.ajax({
                url: '/elbmultiupload/ajax/ajax.php',
                data:ajaxdata,
                dataType: "html",
                type: "POST",
                success:function(data) {
                    td.html(data);
                    td.addClass('loaded');
                    td.find(".classfortooltip").tipTip({maxWidth: "250px", edgeOffset: 10, delay: 50, fadeIn: 50, fadeOut: 50});
                }
            });
        }
    }
    $(a).toggleClass("expanded");
    $(a).find(".ui-icon").removeClass("ui-icon-triangle-1-se");
    $(a).find(".ui-icon").removeClass("ui-icon-triangle-1-e");
    checkExpanded(a);
}

function elb_ajax_dialog(url, title, width, options) {
    if(width==undefined) width=800;
    $("#elb-ajax-dlg-body").html("");
    $("#elb-ajax-dlg-loading").show();
    var dlg_options = {
        modal: true,
        width:width,
        title:title,
        open: function( event, ui ) {
            $.get(url,function(data){
                $("#elb-ajax-dlg-loading").hide();
                $("#elb-ajax-dlg-body").html(data);
                var h = $( "#elb-ajax-dlg" ).dialog().height();
                var wh = $(window).height();
                var lh=wh-200;
                if(h>lh) {
                    $( "#elb-ajax-dlg" ).dialog( "option", "maxHeight",lh);
                    $( "#elb-ajax-dlg" ).dialog().height(lh);
                }
                $( "#elb-ajax-dlg" ).dialog( "option", "position", { my: "center", at: "center", of: window } );
                $("#elb-ajax-dlg-body .classfortooltip").tipTip({maxWidth: "250px", edgeOffset: 10, delay: 50, fadeIn: 50, fadeOut: 50});
            });
        }
    };
    if(options) {
        $.extend(dlg_options, options);
    }
    $("#elb-ajax-dlg").dialog(dlg_options);
}

function elb_ajax_action(url,hook,action,params,form) {
    var ajaxdata = {
        hook: hook,
        action: action,
        params: params
    };
    if(form!=undefined) {
        ajaxdata.formData=form.serialize();
    }
    $.ajax({
        url: url,
        data:ajaxdata,
        dataType: "json",
        type: "POST",
        success:function(data) {
            if(data.type=="log") {
                console.log(data.msg);
            } else if (data.type=="js") {
                eval(data.code);
            }
        }
    });
}