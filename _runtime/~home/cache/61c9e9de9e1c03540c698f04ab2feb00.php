<?php if (!defined('THINK_PATH')) exit();?><div class="content_m_sub2">
    <div class="content_m_sub1_menu">活动<span style="color:#666">推荐</span>
        <?php if(isset($schoolDomain)):?>
            <span style="padding-left: 40px; font-size:16px;"><a href="<?php echo ($schoolDomain); ?>" target="_blank">您的学校开通了校方活动=>由此进入</a></span>
        <?php endif; ?>
    </div>
    <div class="content_m_sub2_list">
        <?php echo W('Weibo',array('tpl_name'=>'event_share_weibo','button_title'=>'分享'));?>
        <ul>
            <?php if(is_array($list)): ?><?php $i = 0;?><?php $__LIST__ = $list?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><li>
                <div class="content_m_sub2_list_thumb"><img src="<?php echo ($vo['cover']); ?>" alt="" /></div>
                <div class="content_m_sub2_list_infor">
                    <div class="content_m_sub2_title b">
                        <a href="<?php echo U('event/Front/index', array('id'=>$vo['id']));?>" target="_blank"><?php echo ($vo['title']); ?></a>
                    </div>
                    <div class="content_m_sub2_time">活动时间：<?php echo date('Y年m月d日 H:i', $vo['sTime'])?> </div>
                    <div class="content_m_sub2_publish">归属组织：<?php echo (getEventOrga($vo["sid"])); ?></div>
                    <div class="content_m_sub2_add">参加人数（<span class="bule"><?php echo ($vo['joinCount']); ?></span>）</div>
                    <div class="content_m_sub2_btn">
                        <?php if($mid):?>
                            <?php if($vo['is_prov_event']): ?>
                                <span class="content_m_sub2_btn1"><a href="<?php echo U('event/Front/index',array('id'=>$vo['id']));?>" target="_blank">我要投票</a></span>
                            <?php elseif( $vo['deadline']>time()): ?>
                                    <?php if( $vo['limitCount'] >0 ): ?>
                                        <span class="content_m_sub2_btn1"><a href="<?php echo U('event/Front/join',array('id'=>$vo['id']));?>" target="_blank">我要参加</a></span>
                                    <?php else: ?>
                                        <span class="content_m_sub2_btn1">名额已满</span>
                                    <?php endif; ?>
                            <?php else: ?>
                                <span class="content_m_sub2_btn1">报名已结束</span>
                            <?php endif; ?>

                            <?php
                                    $tpl_data = urlencode(serialize(array(
                                    'author'=>getUserName($vo['uid']),
                                    'title'=>$vo['title'],
                                    'url'=>U('event/Front/index',array('id'=>$vo['id'],'uid'=>$vo['uid'])),
                                    )));
                                ?>
                            <span class="content_m_sub2_btn2"><a href="javascript:void(0);" onclick="_widget_weibo_start('', '<?php echo ($tpl_data); ?>');" >分享活动</a></span>
                         <?php endif; ?>
                    </div>
                </div>
            </li><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
        </ul>
    </div>
</div>