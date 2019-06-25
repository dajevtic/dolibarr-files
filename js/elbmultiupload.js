jQuery(document).ready(function() {

    // url anchor
    var anchor = window.location.hash;
    var anchor_arr = anchor.split('-');
    var anchor_nr = anchor_arr[1];

    // add attribute autocomplete="off" on every input
    $('input[type="text"]').each(function(ind,el) {
        $(el).prop('autocomplete','off');
    });

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
                url: '/elb/ajax/ajax.php',
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

function elb_ajax_submit(el) {
    var form=$(el).closest('form');
    var url=form.attr('action');
    url+='?';
    url+=form.serialize();
    url+='&'+$(el).attr('name')+'='+$(el).val();
    $("#elb-ajax-dlg-body").html('');
    $("#elb-ajax-dlg-loading").show();
    $.get(url,function(data){
        $("#elb-ajax-dlg-loading").hide();
        $("#elb-ajax-dlg-body").html(data);
    });
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

function elb_ajax_dialog_response(url,hook,action,getparams,form) {
    var ajaxdata = {
        url: url,
        hook: hook,
        action: action,
        params: {
            params:getparams
        }
    };
    $.ajax({
        url: '../../ajax/ajax.php',
        data:ajaxdata,
        dataType: "html",
        type: "POST",
        success:function(data) {
            $("#dialog-confirm").remove();
            $("body").append(data);
        }
    });
}

function closedialog() {
    $(".ui-dialog").remove();
}

function select_checkboxes(el, chk_class) {
    var checked = $(el).prop('checked');
    $('.'+chk_class).each(function(){
        $(this).prop('checked',checked);
    });
}

function trigger_checkboxes(el, chk_class) {
    var checked = $(el).prop('checked');
    $('.'+chk_class).each(function(){
        $(this).trigger('click');
    });
}

function elb_multi_select(sel, submit_on_select, height) {
    var submit_on_select = (typeof submit_on_select === 'undefined') ? true : submit_on_select;
    var height = (typeof height === 'undefined') ? 245 : height;
    $(sel).multiselect({
        height: height,
        selectedList: 2,
        close: function() {
            if(submit_on_select) {
                $(sel).closest("form").submit();
            }
        }
    });
    $(sel).next().width($(sel).width());
}

function elb_multiselect_translations() {
    // Multiselect translations
    $(".ui-multiselect-all").each( function (index) {
        $(this).children('span').eq(1).text(translations.CheckAll);
    });
    $(".ui-multiselect-none").each( function (index) {
        $(this).children('span').eq(1).text(translations.UncheckAll);
    });
    $("button.ui-multiselect.ui-widget").each(function(index) {
        var getpredefvalue = $(this).children('span').eq(1).text();
        if (getpredefvalue.indexOf(" selected") >= 0) {
            var exploded = getpredefvalue.split(" ");
            var secondpart = translations.Selected;
            var newvalue = exploded[0] + " " + secondpart;
            $(this).children('span').eq(1).text(newvalue);
        } else if (getpredefvalue.indexOf(" options") >= 0) {
            var newvalue = translations.SelectOptions;
            $(this).children('span').eq(1).text(newvalue);
        }
    });
}

function form_submit(el, action) {
    var form = $(el).closest('form');
    var action_field = form.find("[name=action]");
    if(action_field.length == 0) {
        action_field = $('<input type="hidden" name="action" value=""/>');
        form.append(action_field);
    }
    action_field.attr("value",action);
    form.submit();
}