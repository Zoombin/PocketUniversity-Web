<?php if (!defined('THINK_PATH')) exit();?><ul>
    <?php if(is_array($list)): ?><?php $i = 0;?><?php $__LIST__ = $list?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><li>
        <div class="content_m_sub3_menu <?php if($i==2){echo'color1';}if($i==3){echo'color2';}?> b"><a href="<?php echo ($vo["more"]); ?>"><?php echo ($vo["title"]); ?> >></a></div>
        <div class="content_m_sub3_sublist <?php if($i==2){echo'color3';}if($i==3){echo'color4';}?>">
            <ul>
                <?php if(is_array($vo['list'])): ?><?php $j = 0;?><?php $__LIST__ = $vo['list']?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo2): ?><?php ++$j;?><?php $mod = ($j % 2 )?><li>
        <?php if($vo['title'] == '课件'):?>
                <a href="<?php echo ($vo2['url']); ?>" title="<?php echo ($vo2['title']); ?>"><?php echo ($vo2['title']); ?></a>
        <?php else:?>
                <div class="content_m_sub3_sublist_icon">
                    <?php if($vo2['path'] != ''):?>
                    <img width="16px;" height="16px;" src="<?php echo ($vo2['path']); ?>"  />
                    <?php endif;?>
                </div>
               <div class="content_m_sub3_sublist_title"><a href="<?php echo ($vo2['url']); ?>" title="<?php echo ($vo2['title']); ?>"><?php echo ($vo2['title']); ?></a></div>
        <?php endif;?>
                    </li><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
            </ul>
        </div>
    </li><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
</ul>