function changeType(o){
    var typeId = $(o).val();
    if(typeId != 0){
        var change = '';
        var maxNum = $(o).find("option:selected").attr("banner");
        for (var i=1;i<=maxNum;i++){
            change += '<label><input name="banner" type="radio" value="'+i+'" /> <img width="440px" height="100px" src="/apps/event/Tpl/default/Public/images/schoolevent/'
            +typeId+'_'+i+'.jpg" /></label><br/>'
        }
        $('#bannerImg').html(change);
    }
}
function clearRadio(){
    $(":radio[name=banner]:checked").removeAttr('checked');
}