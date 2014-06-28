<?php if (!defined('THINK_PATH')) exit();?><?php if($hasMore){ ?>
<ul id="pic_mini_more_show_<?php echo ($rand); ?>" class="feed_showPic_small">
  <?php if(is_array($data)): ?><?php $i = 0;?><?php $__LIST__ = $data?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><li class="feed_big_img">
    <a href="javascript:void(0)" onclick="switchPicMore(<?php echo ($rand); ?>,'open')">
      <img class="imgicon mini_item_<?php echo ($rand); ?>"  middel="__UPLOAD__/<?php echo ($vo["thumbmiddleurl"]); ?>" src="__UPLOAD__/<?php echo ($vo["thumburl"]); ?>" />
    </a>
    </li><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
</ul>

<ul id="pic_show_more_<?php echo ($rand); ?>" class="feed_showPic clearfix" style="display:none;">
  <?php if(is_array($data)): ?><?php $i = 0;?><?php $__LIST__ = $data?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><li class="feed_big_img moreimg">
    <span class="check_big_img"><a href="__UPLOAD__/<?php echo ($vo["picurl"]); ?>" target="_blank" class="check">查看大图</a></span>
    <a href="javascript:void(0)" onclick="switchPicMore(<?php echo ($rand); ?>,'close')">
      <img class="imgSmall middel_item_<?php echo ($rand); ?>" src="" />
      <img class="loadimg middel_loading_item_<?php echo ($rand); ?>" src="__THEME__/images/icon_waiting.gif" style="border: 0px none ; position: absolute; top: 50px; left: 50px; background-color: transparent; width: 16px; height: 16px; z-index: 1001; display: none;">
    </a>
    </li><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
</ul>
<?php }else{ ?>

<div class="feed_img" id="pic_mini_show_<?php echo ($rand); ?>">
	<a href="javascript:void(0)" onclick="switchPic(<?php echo ($rand); ?>,'open','__UPLOAD__/<?php echo ($data["thumbmiddleurl"]); ?>')">
		<img class="imgicon" src="__UPLOAD__/<?php echo ($data["thumburl"]); ?>" />
	</a>
</div>
<div class="feed_quote" style="display:none;" id="pic_show_<?php echo ($rand); ?>"> 
	<div class="q_tit"><img class="q_tit_l" src="__THEME__/images/zw_img.gif" /></div>
	<div class="q_con1"> 
		<p style="margin:5px 0px" class="cGray2">
		<a href="javascript:void(0)" onclick="switchPic(<?php echo ($rand); ?>,'close')">
			<img class="ico_cls" src="__THEME__/images/zw_img.gif" />收起</a>
			&nbsp;&nbsp;|&nbsp;&nbsp;
			<a href="javascript:void(0)" onclick="revolving(<?php echo ($rand); ?>,'left')" >
				<img class="ico_turn_l" src="__THEME__/images/zw_img.gif" />左转</a>
			&nbsp;&nbsp;|&nbsp;&nbsp;
			<a href="javascript:void(0)" onclick="revolving(<?php echo ($rand); ?>,'right')" >
				<img class="ico_turn_r" src="__THEME__/images/zw_img.gif" />右转</a>
			&nbsp;&nbsp;|&nbsp;&nbsp;
			<a href="__UPLOAD__/<?php echo ($data["picurl"]); ?>" target="_blank">
				<img class="ico_original" src="__THEME__/images/zw_img.gif" />查看原图</a>
			</p>
		<a href="javascript:void(0)" onclick="switchPic(<?php echo ($rand); ?>,'close')">
			<img maxWidth="320" src="" class="imgSmall" >
		</a>
	</div>
	<div class="q_btm"><img class="q_btm_l" src="__THEME__/images/zw_img.gif" /></div>
</div>

<?php } ?>