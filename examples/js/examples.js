$(function(){
    $( ".tabs" ).tabs();
    $( ".ui-tabs .ui-corner-top").click(function(){
        $(this).find('.ui-tabs-anchor').trigger('click');
        return false;
    });
});