<?php if (!defined('THINK_PATH')) exit();?><div class="feed_img" id="video_mini_show_<?php echo ($rand); ?>">
  <a href="javascript:void(0);" onclick="switchVideo(<?php echo ($rand); ?>,'open','<?php echo ($data["host"]); ?>','<?php echo ($data["flashvar"]); ?>')">
    <img src="<?php echo ($data["flashimg"]); ?>" style="width:150px; overflow:hidden" />
  </a>
  <div class="video_play" ><a href="javascript:void(0);" onclick="switchVideo(<?php echo ($rand); ?>,'open','<?php echo ($data["host"]); ?>','<?php echo ($data["flashvar"]); ?>')">
      <img src="__THEME__/images/feedvideoplay.gif" ></a>
  </div>
</div>
<div class="feed_quote" style="width:470px;display:none;" id="video_show_<?php echo ($rand); ?>"> 
  <div class="q_tit">
    <img class="q_tit_l" onclick="switchVideo(<?php echo ($rand); ?>,'open','<?php echo ($data["host"]); ?>','<?php echo ($data["flashvar"]); ?>')" src="__THEME__/images/zw_img.gif" />
  </div>
  <div class="q_con"> 
    <p style="margin:0;margin-bottom:5px" class="cGray2">
    <a href="javascript:void(0)" onclick="switchVideo(<?php echo ($rand); ?>,'close')">
      <img class="ico_cls"  src="__THEME__/images/zw_img.gif" />收起</a>
    &nbsp;&nbsp;|&nbsp;&nbsp;
    <a href="<?php echo ($data["source"]); ?>" target="_blank">
      <img class="ico_original" src="__THEME__/images/zw_img.gif" /><?php echo ($data["title"]); ?></a>
    </p>
    <div id="video_content_<?php echo ($rand); ?>"></div>
  </div>
  <div class="q_btm"><img class="q_btm_l" src="__THEME__/images/zw_img.gif" /></div>
</div>