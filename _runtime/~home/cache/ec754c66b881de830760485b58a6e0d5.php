<?php if (!defined('THINK_PATH')) exit();?>    <ul>
        <?php if(is_array($install_app)): ?><?php $i = 0;?><?php $__LIST__ = $install_app?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><?php if(empty($vo['app_alias'])) continue;?>
        <?php if($vo['category'] == $category):?>
        <li>
            <a href="<?php echo ($vo["app_entry"]); ?>" title="<?php echo ($vo["description"]); ?>" class="user_app_link" >
            <div class="c1_list2_sublist_thumb">
                <img src="<?php echo ($vo["large_icon_url"]); ?>" alt="" />
            </div>
            <div class="c1_list2_sublist_title"><?php echo ($vo["app_alias"]); ?></div>
            </a>
        </li>
        <?php endif;?><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
    </ul>