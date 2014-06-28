<?php if (!defined('THINK_PATH')) exit();?><div class="school_d" >
    <div class="school_province jun">
        <ul>
            <?php if(is_array($citys)): ?><?php $i = 0;?><?php $__LIST__ = $citys?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><li <?php if(($first)  ==  $vo['id']): ?>class='school1'<?php endif; ?>><a href="javascript:void(0);" val="<?php echo ($vo["id"]); ?>"><?php echo ($vo["city"]); ?></a></li><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
        </ul>
    </div>
    <div class="school_college" id="subschool" >
        <ul>
            <?php if(is_array($schools)): ?><?php $i = 0;?><?php $__LIST__ = $schools?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><li id="node_<?php echo ($vo["id"]); ?>">
                 <a onclick="save(<?php echo ($vo["id"]); ?>,'<?php echo ($vo["title"]); ?>')" href="javascript:void(0);"><?php echo ($vo["title"]); ?></a>
                </li><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
        </ul>
    </div>

    <div class="school_close"><input name="close" type="button" value="关闭" class="school_btn" onclick="javascript:ui.box.close();" /></div>

</div>
<script>
    $(function(){
        $('.jun ul li a ').click(function(){
            var cityId=$(this).attr('val');
            $('.jun ul li').removeClass()
            $(this).parent('li').addClass('school1');
            $.post(U('home/Public/ajaxCitySchool'), {cityId:cityId},
            function(data){
                var json= $.parseJSON(data);
                $("#subschool").html('');
                $.each( json, function(i,n){
                    $("#subschool").append(addnode(n));
                });
            });
        });
    })
    
    function addnode(n){
        return "<li id='node_"+n.id+"'><a href='javascript:void(0);' onclick=save("+n.id+",\'"+n.title+"\') >"+n.title+"</a></li>";
    }

    function save(id,title){
        if(!title){
            alert('请选择学校');
        }else{
            parent.$('#current').val(id);
            parent.$('#selectarea').val(title);
            ui.box.close();
        }
    }
 
</script>