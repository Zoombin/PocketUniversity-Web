//鼠标移动表格效果
$(document).ready(function(){
        $("[overstyle='on']").hover(
          function () {
            $(this).addClass("bg_hover");
          },
          function () {
            $(this).removeClass("bg_hover");
          }
        );
});
function getChecked() {
    var ids = new Array();
    $.each($('table input:checked'), function(i, n){
        if($(n).val() != ''){
            ids.push( $(n).val() );
        }
    });
    return ids;
}
function checkon(o){
    if( o.checked == true ){
        $(o).parent().parent().addClass('bg_on') ;
    }else{
        $(o).parent().parent().removeClass('bg_on') ;
    }
}
function checkAll(o){
    if( o.checked == true ){
        $('input[name="checkbox"]').attr('checked','true');
        $('input[name="checkbox[]"]').attr('checked','true');
        $('[overstyle="on"]').addClass("bg_on");
    }else{
        $('input[name="checkbox[]"]').removeAttr('checked');
        $('input[name="checkbox"]').removeAttr('checked');
        $('[overstyle="on"]').removeClass("bg_on");
    }
}
function delList(url,nid){
    var nids = nid ? nid : getChecked();
    nids = nids.toString();
    if(nids=='' || nids==0){
        ui.error("请选择要删除的选项");
        return false;
    }
    if( confirm("您确定要删除吗？") ){
        $.post( url,{nid:nids},function(text ){
            json = eval('('+text+')');
            if( json.status == 1 ){
                ui.success( json.info );
                if(nid){
                    $('#list_'+nid).remove();
                }else{
                    var nid_list = nids.split( ',' );
                    for (var j=0 ; j< nid_list.length ; j++   ){
                        $('#list_'+nid_list[j]).remove();
                    }
                }
            }else{
                ui.error( json.info );
            }
        });
    }
}