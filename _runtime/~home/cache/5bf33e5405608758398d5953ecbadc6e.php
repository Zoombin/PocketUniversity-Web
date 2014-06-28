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
<script type="text/javascript" src="__PUBLIC__/js/simplefoucs.js"></script>
<div class="content_left_login">
    <div class="content_l_login">
        <div class="content_l_login_menu">立刻<span style="color:#9bcc38">登录</span></div>
        <div class="c1_login">
        <div class="clear"></div>
        <div id="ppcontid" class="logform">
            <form action="<?php echo U('home/Public/doLogin');?>" method="post">
                <div class="frm" style="height:29px;*height:auto; position:relative">
                    <label id="_login_school_label" class="form_label" style="display:block;" onclick="$(this).hide();selectSchool()">选择学校</label>
                    <input type="text" id="selectarea" class="text" value="" readonly="true" OnFocus="$('#_login_school_label').hide();selectSchool()" />
                    <input type="hidden" id="current" name="sid" />
                </div>
                <div class="frm" style="height:29px;*height:auto; position:relative">
                    <label id="_login_email_label" class="form_label" style="display:block;" onclick="$(this).hide();$('#number').focus();">请输入学号</label>
                    <input type="text" id="number" name="number" class="text" title="学号" autocomplete="off" value="" onblur="if($(this).val()=='') $('#_login_email_label').show();" onfocus="$('#_login_email_label').hide();" />
                </div>
                <div class="frm" style="height:29px;*height:auto; position:relative">
                    <label id="_login_password_label" class="form_label" style="display:block;" onclick="$(this).hide();$('#password').focus();" >请输入密码，初始密码为6个1</label>
                    <input type="password" title="密码" value="" id="password" name="password" class="text" style="display: inline;" onblur="if($(this).val()=='') $('#_login_password_label').show();" onfocus="$('#_login_password_label').hide();" />
                </div>
                <div class="frm" style="margin-top:8px; float:left;">
                    <label>
                        <input name="remember" type="checkbox" value="1" />
                        记住登录状态</label>
                    <a class="fuc0" href="<?php echo U('home/Public/sendPassword');?>">忘记密码？</a>
                </div>
                <div class="frm" style="margin:5px auto 25px auto; padding:0px; width:85px"><input type="submit" value="登 录" class="logbtn" /> </div>
            </form>
        </div>
        </div>
        <script>
            $(document).ready(function(){
                if($('#selectarea').val()!='') $('#_login_school_label').hide();
                setInterval(function(){
                    if('' != $('#number').val())$('#_login_email_label').hide();
                    if('' != $('#password').val())$('#_login_password_label').hide();
                }, 100);
            });
            function selectSchool(){
                ui.box.load(U('home/Public/citys'),{title:'选择学校',noCheck:'yes'});
            }
        </script>
    </div>

    <div class="content_l_ph">
        <div class="content_l_login_menu">公告<span style="color:#9bcc38">活动</span></div>
        <div class="c_update">
         <ul>
            <?php if(is_array($noticeList)): ?><?php $i = 0;?><?php $__LIST__ = $noticeList?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><li>
                  <a href="<?php echo U('announce/Index/noticeDetail', array('id'=>$vo['id']));?>"><span class="cRed">[ <?php echo ($vo["front"]); ?> ]</span> <?php echo (getShort($vo["title"],14)); ?></a>
              </li><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
         </ul>
         <div class="c_update_more"><a href="<?php echo U('announce/Index/notice');?>">更多>></a></div>
        </div>



    </div>
  <?php if(($adleft)  !=  ""): ?><div class="login_ad1">
        <a href="<?php echo ($adleft[0]['url']); ?>"  target="_blank"  >
            <img src="<?php echo (getImgAttachOri($adleft[0]['coverId'])); ?>" alt="<?php echo ($adleft[0]['title']); ?>" />
        </a>
    </div><?php endif; ?>
</div>
<div class="content_middle">
    <?php if(($adhome)  !=  ""): ?><div class="login_ad2">
        <div id="focus">
            <ul>
                <?php if(is_array($adhome)): ?><?php $i = 0;?><?php $__LIST__ = $adhome?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><li><a href="<?php echo ($vo['url']); ?>"  target="_blank" >
                            <img src="<?php echo (getImgAttachOri($vo['coverId'])); ?>" alt="<?php echo ($vo["title"]); ?>" /></a></li><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
            </ul>
        </div>
    </div><?php endif; ?>
    <?php echo W('Event',array('mid'=>$mid,'limit'=>3));?>
</div>
<div class="c3 i_login">
    <div class="content_r_login">
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
        <div class="c3_menu i_login1 b">正在<span style="color:#14a0cd">发生的</span></div>
        <div class="c3_list">
            <?php echo W('WeiboList1', array('mid'=>$mid, 'list'=>$lastest_weibo, 'insert'=>0, 'num'=>10));?>
        </div>
    </div>
</div>
<script type="text/javascript">
  function showBimg(path){
      ui.box.show('<div style="width:300px;height:300px;"><img src="'+path+'" /></div>',{title:'二维码'});
  }
</script>
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