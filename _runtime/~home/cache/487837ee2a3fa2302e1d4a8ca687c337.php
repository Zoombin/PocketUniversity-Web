<?php if (!defined('THINK_PATH')) exit();?><ul class="feed_list" <?php if(($insert)  ==  "1"): ?>id="feed_list"<?php endif; ?>>
    <?php $cnt = 0;?>
    <?php if(is_array($list)): ?><?php $i = 0;?><?php $__LIST__ = $list?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><?php if($cnt<$num):?>
        <?php if($vo['type']==0 && $vo['expend']==''):?>
        <?php $cnt++;?>
        <li class="lineD_btm <?php if($cnt==$num)echo'i9';?>" id="list_li_<?php echo ($vo["weibo_id"]); ?>">
            <div>
                <div class="msgCnt">
                    <span class="bule b size"><?php echo getUserSpace($vo["uid"],'','','{uname}') ?>：</span>
                    <p style="vertical-align:top;display:inline"><?php echo (login_emot_format(getShort($vo["content"],60))); ?></p>
                </div>
                <?php if($insert == 1):?>
                <div class="feed_c_btm">
                    <span class="right c3_list_note">
                        <?php if($show == 'detail'): ?><?php echo Addons::hook('weibo_bottom_middle', array('weibo_id'=>$vo['weibo_id'], 'weibo'=>$vo));?><?php endif; ?>
                        <?php if( $vo['uid'] == $mid ){ ?>
                        <a href="javascript:void(0)" onclick="ui.confirm(this,'<?php echo L('del_confirm');?>')" callback="weibo.deleted(<?php echo ($vo["weibo_id"]); ?>)" title="删除">删</a>
                        <i class="vline">|</i>
                        <?php } ?>
                        <a href="javascript:void(0)" onclick="weibo.transpond(<?php echo ($vo["weibo_id"]); ?>)" title="转发">转(<?php echo ($vo["transpond"]); ?>)</a>
                        <i class="vline">|</i><a href="javascript:void(0)" rel="comment" minid="<?php echo ($vo["weibo_id"]); ?>"  title="评论">评(<?php echo ($vo["comment"]); ?>)</a>
                        <?php if($show == 'detail'): ?><?php echo Addons::hook('weibo_bottom_right', array($vo['weibo_id'], $vo));?><?php endif; ?>
                        <i class="vline">|</i>
                            <?php if($vo['is_hearted']){ ?>
                            赞(<?php echo ($vo["heart"]); ?>)
                            <?php }else{ ?>
                            <a href="javascript:void(0)"  onclick="weibo.heart(<?php echo ($vo["weibo_id"]); ?>,this,<?php echo ($vo["heart"]); ?>)"  title="点赞">赞(<?php echo ($vo["heart"]); ?>)</a>
                            <?php } ?>
                    </span>
                </div>
                <div id="comment_list_<?php echo ($vo["weibo_id"]); ?>" style=""></div>
                <?php else:?>
                <div class="c3_list_note"><span class="right"><?php echo (friendlyDate($vo["ctime"])); ?></span></div>
                <?php endif;?>
            </div>
        </li>
        <?php endif;?>
        <?php endif;?><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
</ul>