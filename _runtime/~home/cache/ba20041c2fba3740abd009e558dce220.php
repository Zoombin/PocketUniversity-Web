<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<!-- QQ登录 -->
<meta property="qc:admins" content="61701556566401633636375" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>
<?php if(($ts['site']['page_title'])  !=  ""): ?><?php echo ($ts['site']['page_title']); ?> <?php if(($ts['app']['app_alias'])  !=  ""): ?>- <?php echo ($ts['app']['app_alias']); ?><?php endif; ?>- <?php echo ($ts['site']['site_name']); ?>
<?php else: ?>
    <?php echo ($ts['site']['site_name']); ?> <?php if(($ts['site']['site_slogan'])  !=  ""): ?>- <?php echo ($ts['site']['site_slogan']); ?><?php endif; ?><?php endif; ?>
</title>
<link rel="shortcut icon" href="__THEME__/favicon.ico" />
<meta property="wb:webmaster" content="a4bee774a4fe89c7" />
<meta name="keywords" content="<?php echo ($ts['site']['site_header_keywords']); ?>" />
<meta name="description" content="<?php echo ($ts['site']['site_header_description']); ?>" />
<script>
	var _UID_   = <?php echo (int) $uid; ?>;
	var _MID_   = <?php echo (int) $mid; ?>;
	var _ROOT_  = '__ROOT__';
	var _THEME_ = '__THEME__';
	var _PUBLIC_ = '__PUBLIC__';
	var _LENGTH_ = <?php echo (int) $GLOBALS['ts']['site']['length']; ?>;
	var _LANG_SET_ = '<?php echo LANG_SET; ?>';
	var $CONFIG = {};
		$CONFIG['uid'] = _UID_;
		$CONFIG['mid'] = _MID_;
		$CONFIG['root_path'] =_ROOT_;
		$CONFIG['theme_path'] = _THEME_;
		$CONFIG['public_path'] = _PUBLIC_;
		$CONFIG['weibo_length'] = <?php echo (int) $GLOBALS['ts']['site']['length']; ?>;
		$CONFIG['lang'] =  '<?php echo LANG_SET; ?>';
    var bgerr;
    try { document.execCommand('BackgroundImageCache', false, true);} catch(e) {  bgerr = e;}
</script>
<link href="__THEME__/public.css?20140519" rel="stylesheet" type="text/css" />
<link href="__THEME__/layout.css?20140428" rel="stylesheet" type="text/css" />
<link href="__THEME__/form.css?20140331" rel="stylesheet" type="text/css" />
<link href="__THEME__/link.css" rel="stylesheet" type="text/css" />
<link href="__THEME__/menu.css" rel="stylesheet" type="text/css" />
<link href="__THEME__/style.css?20140213" rel="stylesheet" type="text/css" />
<link href="__THEME__/css/school.css" rel="stylesheet" type="text/css" />
<link href="__PUBLIC__/js/tbox/box.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="__PUBLIC__/js/jquery.js"></script>
<script type="text/javascript" src="__PUBLIC__/js/common.js?20130805"></script>
<script type="text/javascript" src="__PUBLIC__/js/tbox/box.js?131220"></script>
<script type="text/javascript" src="__PUBLIC__/js/scrolltopcontrol.js"></script>
<link href="__THEME__/css/ad_1.css?20131206" rel="stylesheet" type="text/css" />
<script src="__PUBLIC__/js/jquery.jgrow.min.js"></script>
<script src="__PUBLIC__/js/jquery.isotope.min.js"></script>

<!-- 编辑器样式文件 -->
<link href="__PUBLIC__/js/editor/editor/theme/base-min.css" rel="stylesheet"/>
<!--[if lt IE 8]><!-->
<link href="__PUBLIC__/js/editor/editor/theme/cool/editor-pkg-sprite-min.css" rel="stylesheet"/>
<!--<![endif]-->
<!--[if gte IE 8]><!-->
<link href="__PUBLIC__/js/editor/editor/theme/cool/editor-pkg-min-datauri.css" rel="stylesheet"/>
<!--<![endif]-->
<?php echo Addons::hook('public_head',array('uid'=>$uid));?>
</head>

<body>
<?php
                define('HOLD_START', true);
            ?>
        <?php if(isset($_SESSION["userInfo"])): ?><!--顶部导航-->
	<script type="text/javascript">
function MM_swapImgRestore() { //v3.0
  var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
}
function MM_preloadImages() { //v3.0
  var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
    var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
    if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
}

function MM_findObj(n, d) { //v4.01
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && d.getElementById) x=d.getElementById(n); return x;
}

function MM_swapImage() { //v3.0
  var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
   if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
}
    </script>
	<body>
	<div class="header_holder">
	 <div class="header">
	 <div class="logo_holder">
	    <div class="logo"><a href="<?php echo U('home/Index');?>" ><?php if(($ts['site']['site_logo'])  !=  ""): ?><img src="<?php echo ($ts['site']['site_logo']); ?>" /><?php else: ?><img src="__THEME__/images/logo.gif" /><?php endif; ?></a></div>

	    <div class="nav">
	      <ul>
	        <?php if(isset($userInfoCache['schoolEvent']['domain'])):?>
                <li>
                    <a href="<?php echo (getDomainLink($userInfoCache['schoolEvent']['domain'])); ?>" class="fb14">大学生成长服务平台</a>
                </li>
                <?php endif; ?>
                <?php if(canTabSchool($mid)):?>
                <li><a href="javascript:void(0)" class="fb14" title="切换学校" onClick="changeSchoolDomain()">[切换]</a></li>
                <script>
                function changeSchoolDomain(){
                    ui.box.load(U('home/Public/changeSchoolDomain'),{title:'选择学校',noCheck:'yes'});
                }
                </script>
                <?php endif; ?>
	        <li class="header_dropdown"><a href="<?php echo U('home/Index/addapp');?>" class="application">应用<span class="ico_arrow"></span></a>
	          <div class="dropmenu">
	            <dl class="app_list">
	                <?php foreach ($ts['user_app'] as $_temp_type => $_temp_apps) { ?>
	                <?php foreach ($_temp_apps as $_temp_app) { ?>
	                    <dd>
	                        <?php if($_temp_type == 'local_app' || $_temp_type == 'local_default_app') { ?>
	                        <a href="<?php echo $_temp_app['app_entry'];?>" class="a14">
	                            <img class="app_ico" src="<?php echo $_temp_app['icon_url'];?>" />
	                            <?php echo $_temp_app['app_alias'];?>
	                        </a>
	                        <?php }else { ?>
	                        <a href="__ROOT__/apps/myop/userapp.php?id=<?php echo $_temp_app['app_id'];?>" class="a14">
	                            <img class="app_ico" src="http://appicon.manyou.com/icons/<?php echo $_temp_app['app_id'];?>" />
	                            <?php echo $_temp_app['app_alias'];?>
	                        </a>
	                        <?php }?>
	                    </dd>
	                <?php } // end of foreach?>
	                <?php } // end of foreach?>
	                </dl>
	                <dl class="app_list_add">
	                <dd><a href="<?php echo U('home/Index/addapp');?>"><span class="ico_app_add"></span>添加更多应用</a></dd>
	                </dl>
	          </div>
	        </li>
	      </ul>
	    </div>
	 </div>
		<!--个人信息区-->
	    <ul class="person">
			<li><?php echo getUserSpace($mid,'fb14 username nocard','','',false);?></li>
                        <li><a href="<?php echo U('home/Account/recharge');?>" class="fb14">PU币：<?php echo (money2xs($userInfoCache["money"])); ?></a>
                        </li>
			<li class="header_dropdown"><a href="<?php echo U('home/message/index');?>" class="application">消息<span class="ico_arrow"></span></a>
	          <div class="dropmenu">
	                <ul class="message_list_container message_list_new">
	                </ul>
	                <dl class="message">
						<dd><a href="<?php echo U('home/message/index');?>">查看私信<?php if(($userCount['message'])  >  "0"): ?>(<?php echo ($userCount["message"]); ?>)<?php endif; ?></a></dd>
						<dd><a href="<?php echo U('home/user/atme');?>">查看@我<?php if(($userCount['atme'])  >  "0"): ?>(<?php echo ($userCount["atme"]); ?>)<?php endif; ?></a></dd>
						<dd><a href="<?php echo U('home/user/comments');?>">查看评论<?php if(($userCount['comment'])  >  "0"): ?>(<?php echo ($userCount["comment"]); ?>)<?php endif; ?></a></dd>
						<dd><a href="<?php echo U('home/message/notify');?>">系统通知<?php if(($userCount['notify'])  >  "0"): ?>(<?php echo ($userCount["notify"]); ?>)<?php endif; ?></a></dd>
						<dd><a href="<?php echo U('home/message/appmessage');?>">应用消息<?php if(($userCount['appmessage'])  >  "0"): ?>(<?php echo ($userCount["appmessage"]); ?>)<?php endif; ?></a></dd>
	                </dl>
	                <dl class="square_list">
	                <dd><a href="javascript:ui.sendmessage(0)">发私信</a></dd>
	                </dl>
	          </div>
	        </li>
			<li class="header_dropdown" onClick="userManage(this);"><a href="javascript:void(0)" class="application">管理中心<span class="ico_arrow"></span></a>
	          <div class="dropmenu">
	                <dl class="setup">
	                <dd><a href="<?php echo U('home/User/findfriend');?>"><span class="ico_pub ico_pub_find"></span>找人</a></dd>
	                <dd><a href="<?php echo U('home/Account');?>"><span class="ico_pub ico_pub_set"></span>个人中心</a></dd>
                        <dd><a href="<?php echo U('home/Account/recharge');?>"><span class="ico_pub ico_pub_skin"></span>充值中心</a></dd>
                        <dd><a href="<?php echo U('shop/Myshop/myDonate');?>"><span class="ico_pub ico_pub_gift"></span>我的乐购</a></dd>
                        <?php if(isset($userInfoCache['schoolEvent']['domain'])):?>
	                <dd><a href="<?php echo (getDomainLink($userInfoCache['schoolEvent']['domain'])); ?>"><span class="ico_pub ico_pub_activity"></span>成长服务平台</a></dd>
                        <?php endif; ?>
	                <?php if(($isSystemAdmin)  ==  "TRUE"): ?><dd><a href="<?php echo U('admin/index/index');?>"><span class="ico_pub ico_pub_admin"></span>后台管理</a></dd><?php endif; ?>
	                </dl>
	                <dl class="square_list_add">
	                <dd><a href="<?php echo U('home/Public/logout');?>"><span class="ico_pub ico_pub_signout"></span>退出</a></dd>
	                </dl>
	          </div>
	        </li>
	    </ul>
		<!--/个人信息区-->
		<!--消息提示框-->
	    <div id="message_list_container" class="layer_massage_box" style="display:none;">
	    	<ul class="message_list_container">
	        </ul>
	        <a href="javascript:void(0)" onClick="ui.closeCountList(this)" class="del"></a>
	    </div>
		<!--/消息提示框-->
	  </div>
      <!--新加导航开始-->
      <div class="pu_menu">
       <div class="pu_mlist">
              <ul>
               <li><a href="<?php echo U('home/Index/index');?>"><img src="__THEME__/images/menu/menu_1.png" onMouseOver="src='__THEME__/images/menu/menu_11.png'"  onmouseout="src='__THEME__/images/menu/menu_1.png'"/></a></li>
               <li><a href="<?php echo U('announce/Index/notice');?>"><img src="__THEME__/images/menu/menu_2.png" onMouseOver="src='__THEME__/images/menu/menu_22.png'"  onmouseout="src='__THEME__/images/menu/menu_2.png'"/></a></li>
              </ul>
              </div>
             </div>
             <!--新加导航结束-->
	</div>
	<!--/顶部导航--><?php endif; ?>
	<?php if( !isset($_SESSION["userInfo"])): ?><div class="header_holder">
	    <div class="header">
	      <div class="logo"><a href="http://<?php echo get_host_needle();?>/index.php?app=home&mod=Public&act=login"><?php if(($ts['site']['site_logo'])  !=  ""): ?><img src="<?php echo ($ts['site']['site_logo']); ?>" /><?php else: ?><img src="__THEME__/images/logo.png" /><?php endif; ?></a></div>
	      <div id="indt" class="nav_sub br3">
	        <p>
	      	<?php if(($ts['site']['site_anonymous_square'])  ==  "1"): ?><a href="<?php echo U('home/Square');?>">微博广场</a>&nbsp;|&nbsp;<?php endif; ?>
	      	<a href="<?php echo U('home/Public/login');?>">登录</a>

	        <p>
	      </div>
	  </div>
            <!--新加导航开始-->
      <div class="pu_menu">
       <div class="pu_mlist">
              <ul>
               <li><a href="http://<?php echo get_host_needle();?>/index.php?app=home&mod=Public&act=login"><img src="__THEME__/images/menu/menu_1.png" onMouseOver="src='__THEME__/images/menu/menu_11.png'"  onmouseout="src='__THEME__/images/menu/menu_1.png'"/></a></li>
               <li><a href="<?php echo U('announce/Index/notice');?>"><img src="__THEME__/images/menu/menu_2.png" onMouseOver="src='__THEME__/images/menu/menu_22.png'"  onmouseout="src='__THEME__/images/menu/menu_2.png'"/></a></li>
              </ul>
              </div>
             </div>
             <!--新加导航结束-->
	</div><?php endif; ?>
<?php
define('HOLD_END', true);
            ?>
<script>
    $(document).ready(function () {
        $(".header_dropdown").hover(
        function(){ $(this).addClass("hover"); },
        function(){ $(this).removeClass("hover"); }
        );
        <?php if($mid > 0) { ?>
            ui.countNew();
        setInterval("ui.countNew()",100000);
            <?php } ?>

        $('.platform').bind('mouseover', function() {
            $('.platform-menu').removeClass('hidden');
        });
        $('.platform').bind('mouseout', function() {
            $('.platform-menu').addClass('hidden');
        });
    });
    function userManage(o){
        $(o).toggleClass('hover');
    }
</script>



<div class="bg">
  <div class="main">
    <div class="content">
<script type="text/javascript" src="__PUBLIC__/js/jquery.form.js"></script>
<script type="text/javascript" src="__PUBLIC__/js/weibo1.js?20140317"></script>
<script type="text/javascript" src="__PUBLIC__/js/jquery.autocomplete.js"></script>
<script type="text/javascript" src="__PUBLIC__/js/simplefoucs.js"></script>
<script src="../Public/js/slides.min.jquery.js"></script>
<style>
.feed_list .feed_wb {
    float: right;
    margin: 7px 0 10px 5px;
    position: relative;
    width: 235px;
}
.new_position textarea {
    margin-right: 5px;
    width: 160px;
}
.contentObj{}
a.username, .username {
    font-weight: bold;}
</style>
<div class="content_left">
    <div class="content_left_user">
    <div class="content_l_sub1">
        <div class="content_l_sub1_photo"><?php echo getUserSpace($mid,'nocard','','{uavatar}') ?></div>
        <div class="c1_infor">
            <div class="c1_name"><?php echo getUserSpace($mid,'','','{uname}') ?></div>
            <div class="c1_jf">PU币：<span class="red"><?php echo($user['money']/100);?></span></div>
            <div class="c1_jf">
                <?php $user_credit = $userInfoCache['credit'];
                    foreach($user_credit as $k => $v) { ?>
                <p><?php echo ($v['alias']); ?>：<a href="<?php echo U('home/Account/credit');?>"><span class="cRed"><?php echo ($v['credit']); ?></span></a></p>
                <?php }
                    unset($user_credit); ?>
            </div>
            <div class="c1_jf"><?php echo (tsGetSchoolName($user["sid"])); ?></div>
        </div>
        <div class="clear"></div>
        <?php if ($announcement['is_open'] && !empty($announcement['content'])) { ?>
        <div id="announcement">
            <div class="c1_come"><a href="javascript:void(0)"><?php echo ($announcement['content']); ?></a></div>
            <div class="c1_close"><img src="__THEME__/images1305/icon_close.gif" alt="关闭" onclick="$('#announcement').hide('slow');" /></div>
        </div>
        <?php } ?>
        <div class="clear"></div>
        <div class="c1_note">
            <table width="230" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td><a href="<?php echo U('home/space/follow',array('type'=>'following', 'uid'=>$mid));?>">关注<br/><?php echo ($userInfoCache['following']); ?></a></td>
                    <td><a href="<?php echo U('home/space/follow',array('type'=>'follower', 'uid'=>$mid));?>">粉丝<br/><?php echo ($userInfoCache['follower']); ?></a></td>
                    <td><a href="<?php echo U('home/space/index',array('uid'=>$uid));?>">微博<br/><?php echo ($userInfoCache['miniNum']); ?></a></td>
                </tr>
            </table>
        </div>
    </div>
</div>
<div class="c1_list1">
    <ul>
        <li><a href="<?php echo U('home/Space/index');?>">我的首页</a></li>
        <li><a href="<?php echo U('home/user/atme');?>" <?php echo getMenuState('user/atme');?>>查看@我</a></li>
        <li><a href="<?php echo U('home/user/collection');?>" <?php echo getMenuState('user/collection');?>>我赞过的</a></li>
        <li><a href="<?php echo U('home/user/comments');?>" <?php echo getMenuState('user/comments');?>>我的评论</a></li>
    </ul>
</div>
<div class="c1_title b">我的应用</div>
<div class="c1_list2_action"><a href="<?php echo U('home/Index/addapp');?>">添加</a> &nbsp; <a href="<?php echo U('home/Index/editapp');?>">管理</a></div>
<div class="c1_list2">
    <ul>
        <li>
            <div class="c1_list2_title b">个人类</div>
            <div class="c1_list2_sublist"><?php echo W('Applist',array('install_app'=>$install_app,'category'=>'个人类'));?></div>
        </li>
        <li>
            <div class="c1_list2_title b">校方类</div>
            <div class="c1_list2_sublist"><?php echo W('Applist',array('install_app'=>$install_app,'category'=>'校方类'));?></div>
        </li>
    </ul>
</div>
<?php function getMenuState($type){
	$type = split('/',$type);
	if( strtolower(MODULE_NAME)==strtolower($type[0]) && ( strtolower(ACTION_NAME)==strtolower($type[1]) || $type[1]=='*') ){
		return 'class="on"';
	}
} ?>
    <?php if(($adleft)  !=  ""): ?><div class="index_ad1">
        <a href="<?php echo ($adleft[0]['url']); ?>"  target="_blank" <?php if(($adleft[0]['type'])  ==  "1"): ?>onclick="adClick(<?php echo ($adleft[0]['id']); ?>)"<?php endif; ?> >
            <img src="<?php echo (getImgAttachOri($adleft[0]['coverId'])); ?>" alt="<?php echo ($adleft[0]['title']); ?>" />
        </a>
    </div><?php endif; ?>
</div>
<div class="content_middle">
    <div class="content_m_sub1">
        <div class="content_m_sub1_menu">校园<span style="color:#666">通知</span></div>
        <div class="content_m_more"><a href="<?php echo U('announce/Index/', array('school'=>$user['sid']));?>">更多 >></a></div>
        <div class="clear"></div>
        <div class="content_m_sub1_list">
            <ul>
            <?php if(is_array($announces)): ?><?php $i = 0;?><?php $__LIST__ = $announces?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><li>
                    <a href="<?php echo U('announce/Index/details',array('id'=>$vo['id']));?>" title="<?php echo ($vo["title"]); ?>"><?php echo ($vo["title"]); ?></a>
                </li><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
            </ul>
        </div>
    </div>
    <?php if(($adhome)  !=  ""): ?><div class="index_ad2">
        <div id="focus2">
            <ul>
                <?php if(is_array($adhome)): ?><?php $i = 0;?><?php $__LIST__ = $adhome?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><li><a href="<?php echo ($vo['url']); ?>"  target="_blank" <?php if(($vo['type'])  ==  "1"): ?>onclick="adClick(<?php echo ($vo['id']); ?>)"<?php endif; ?>>
                            <img src="<?php echo (getImgAttachOri($vo['coverId'])); ?>" alt="<?php echo ($vo["title"]); ?>" /></a></li><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
            </ul>
        </div>
    </div><?php endif; ?>
<script type="text/javascript">

    function adClick(adid){
        $.post("<?php echo U('home/User/adClick');?>",{adid:adid});
    }
</script>

    <?php echo W('Event',array('mid'=>$mid,'schoolDomain'=>getDomainLink($user['schoolEvent']['domain'])));?>
    <div class="content_m_sub3">
        <?php echo W('Threeapp');?>
    </div>
</div>
<div class="c3" style="*height:994px; overflow:hidden;position:relative;">
    <div class="code">
    <div class="code_thumb"><a href="javascript:void(0)" onclick="showBimg('__THEME__/newimages/pu_android2b.png')"><img src="__THEME__/newimages/pu_android2.png" alt="安卓" /></a></div>
    <div class="code_thumb1"><a href="javascript:void(0)" onclick="showBimg('__THEME__/newimages/pu_ios1b.png')"><img src="__THEME__/newimages/pu_ios1.png" alt="苹果" /></a></div>
        <div class="code_btn">
            <ul>
                <li><a href="<?php echo U('home/Public/android');?>"><img style="width:100px;height:30px;" src="__THEME__/newimages/android.gif" alt="" /></a></li>
                <li><a href="http://itunes.apple.com/cn/app/PocketUni/id685777413?mt=8"><img style="width:100px;height:30px;" src="__THEME__/newimages/iphone.gif" alt="" /></a></li>
            </ul>
        </div>
    </div>
    <div class="c3_pic"><img src="__THEME__/newimages/icon.gif" /></div>
    <form method="post" action="<?php echo U('weibo/operate/publish');?>" id="miniblog_publish" oncomplete="false">
        <input type="hidden" name="publish_type" value="0">
        <div class="numObj c3_count"> 还可以输入<span style="font-size:16px; padding:0px 2px;" id="strconunt"><?php echo ($GLOBALS['ts']['site']['length']); ?></span>字 </div>
        <div class="sync">
            <?php if(Addons::requireHooks("home_index_middle_publish")){ ?>
                <div class="right" style="padding-bottom:5px;_margin-top:-8px; cursor:hand;_padding-top:10px; cursor:pointer"  onclick='weibo.showAndHideMenu("Sync", this, "", "");'>
                同步<a href="#" class="ico_sync_on"></a>
            </div>
            <div id="Sync" style="display:none;position:absolute;left: -50px;top:30px;z-index:9999">
                <div class="topic_app"></div>
                <div class="pop_inner">
                    <?php echo Addons::hook('home_index_middle_publish');?>
                </div>
            </div>
            <?php } ?>
        </div>
        <div class="c3_btn">
            <input name="publish" type="button" class="c3_btn_style buttonObj " id="publish_handle" error="发布失败" value="发 布" />
        </div>
        <div class="c3_publish">
            <textarea name="content" id="content_publish" cols="" rows="4" class="contentObj c3_input"></textarea>
            <div class="txtShadow" style="z-index:-1000"></div>
        </div>
        <div class="c3_icon">
            <div id="publish_type_content_before" class="kind">
                <?php echo Addons::hook('home_index_middle_publish_type',array('position'=>'index'));?>
            </div>
        </div>
        <input type="text" style="display:none" />
    </form>

    <div class="c3_menu b size">正在发生的</div>
    <div class="c3_more"><a href="<?php echo U('home/Square/');?>">更多>></a></div>
    <div class="c3_list">
        <?php echo W('WeiboList1', array('mid'=>$mid, 'list'=>$guanzu['data'], 'insert'=>1));?>
    </div>
    <div class="c3_menu b size">我关注的</div>
    <div class="c3_more"><a href="<?php echo U('home/Square/');?>">更多>></a></div>
    <div class="c3_list">
        <?php echo W('WeiboList1', array('mid'=>$mid, 'list'=>$list['data'], 'insert'=>1));?>
    </div>
</div>
<div class="clear"></div>

    </div>
    <div class="pu_f">
	<div class="pu_f_note">
       <?php foreach($ts['footer_document'] as $k => $v) {
            $v['url'] = isset($v['url']) ? $v['url'] : U('home/Public/document',array('id'=>$v['document_id']));
            $ts['footer_document'][$k] = '<a href="'.$v['url'].'" target="_blank">'.$v['title'].'</a>';
        }
        echo implode('&nbsp;&nbsp;|&nbsp;&nbsp', $ts['footer_document']); ?>
	</div>
	<div class="footer_note">
            <?php echo W('FooterIcp');?>
            <a href="<?php echo U('home/Public/toW3g');?>">访问W3G版</a>
                    <a href="<?php echo U('home/Public/toWap');?>">访问WAP版</a><br />
                    <a  key ="533937b8efbfb073e058d483"  logo_size="83x30"  logo_type="realname"  href="http://www.anquan.org" ><script src="http://static.anquan.org/static/outer/js/aq_auth.js"></script></a>
					<a  key ="533937b8efbfb073e058d483"  logo_size="83x30"  logo_type="official"  href="http://www.anquan.org" ><script src="http://static.anquan.org/static/outer/js/aq_auth.js"></script></a>
					<a href="http://zhanzhang.anquan.org/physical/report/?domain=www.pocketuni.net" name="QXp4i0sI0VZlYTg60fDhCoHgwVHA0ysWUi7jd9GcTxvSbyTDeT"><img height="30" src="http://zhanzhang.anquan.org/static/common/images/zhanzhang.png"alt="安全联盟站长平台" /></a>
	</div>
            <!--<div class="pu_f_note">客服电话：0512-69330056 | QQ群 <a href="<?php echo U('home/Public/contact_qq/');?>">点击查看</a></div>-->
    </div>
  </div>
</div>
<?php echo Addons::hook('public_footer');?>
</body>
</html>
<script>
var weibo = $.weibo({
    sinceId: parseInt('<?php echo ($sinceId); ?>'),

        <?php if(ACTION_NAME=="index"){ ?>
    timeStep : 30000,
    initForm:true,
    <?php } ?>

    lastId:parseInt('<?php echo ($lastId); ?>'),
    show_feed:parseInt('<?php echo ($show_feed); ?>'),
    follow_gid:parseInt('<?php echo ($follow_gid); ?>'),
    gid:parseInt('<?php echo ($gid); ?>'),
    weiboType:'<?php echo ($type); ?>',
    type:parseInt('<?php echo ($indexType); ?>'),
    typeList:{
        WEIBO:parseInt(<?php echo UserAction::INDEX_TYPE_WEIBO; ?>),
        GROUP:parseInt(<?php echo UserAction::INDEX_TYPE_GROUP; ?>),
        ALL:parseInt(<?php echo UserAction::INDEX_TYPE_ALL; ?>)
    }
});
function showBimg(path){
      ui.box.show('<div style="width:300px;height:300px;"><img src="'+path+'" /></div>',{title:'二维码'});
  }
</script>
<?php echo Addons::hook('weibo_js_plugin');?>