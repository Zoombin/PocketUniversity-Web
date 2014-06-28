<?php
//获取应用配置
function getConfig($key=NULL){
	$config = model('Xdata')->lget('event');
	$config['limitpage']    || $config['limitpage'] =10;
	$config['canCreate']===0 || $config['canCreat']=1;
    ($config['credit'] > 0   || '0' === $config['credit']) || $config['credit']=100;
    $config['credit_type']  || $config['credit_type'] ='experience';
	($config['limittime']   || $config['limittime']==='0') || $config['limittime']=10;//换算为秒
        $config['createAudit'] === 0 || $config['createAudit'] = 1;

	if($key){
		return $config[$key];
	}else{
		return $config;
	}
}
function getPhotoConfig($key=NULL){
	$config = model('Xdata')->lget('photo');
	$config['album_raws'] || $config['album_raws']=6;
	$config['photo_raws'] || $config['photo_raws']=8;
	$config['photo_preview']==0 || $config['photo_preview']=1;
	($config['photo_max_size']=floatval($config['photo_max_size'])*1024*1024) || $config['photo_max_size']=-1;
	$config['photo_file_ext'] || $config['photo_file_ext']='jpeg,gif,jpg,png';
	$config['max_flash_upload_num'] || $config['max_flash_upload_num']=10;
	$config['open_watermark']==0 || $config['open_watermark']=1;
	$config['watermark_file'] || $config['watermark_file']='public/images/watermark.png';
	if($key==NULL){
		return $config;
	}else{
		return $config[$key];
	}
}

//获取活动封面存储地址
function getCover($coverId,$width=125,$height=125,$t='f'){
    return tsGetCover($coverId, $width, $height, $t);
}
//获取活动封面存储地址
function getLogo($logoId){
    $cover = D('Attach')->field('savepath,savename')->find($logoId);
    if($logoId){
            $cover	=	get_photo_url($cover['savepath'].$cover['savename']);
    }else{
            $cover	=	SITE_URL."/apps/event/Tpl/default/Public/images/hdpic1.gif";
    }
    return $cover;
}
//根据存储路径，获取图片真实URL
function get_photo_url($savepath) {
    if(strpos($savepath, '/')){
        return SITE_URL.'/data/uploads/'.$savepath;
    }else{
        return SITE_URL.'/data/uploads/event/'.$savepath;
    }
}
function realityImageURL($img) {
    $imgURL = sprintf('%s/apps/event/Tpl/default/Public/hold/%s', SITE_URL, $img); //默认的礼物图片地址
    if (file_exists(sprintf('./apps/event/Tpl/default/Public/hold/%s', $img))) {
        return $imgURL;
    } else {//若默认里没有则返回自定义的礼物图片地址
        return sprintf('%s/data/uploads/event/%s', SITE_URL, $img);
    }
}
/**
 * getBlogShort
 * 去除标签，截取blog的长度
 * @param mixed $content
 * @param mixed $length
 * @access public
 * @return void
 */
function getBlogShort($content,$length = 40) {
	$content	=	stripslashes($content);
	$content	=	strip_tags($content);
	$content	=	getShort($content,$length);
	return $content;
}

function getThumb($filename,$width=190,$height=240,$t='f') {
    if(empty($filename)){
        $thumb = SITE_URL . '/apps/event/Tpl/default/Public/images/user_pic_big.gif';
    }else{
        if(strpos($filename, '/')){
            $thumb = tsMakeThumbUp($filename,$width,$height,$t);
        }else{
            $thumb = tsMakeThumbUp('event/'.$filename,$width,$height,$t);
        }
    }
    return $thumb;
}

function getGroupThumb($filename,$width=80,$height=80,$t='f') {
    if(empty($filename) || $filename=='default.gif'){
        $thumb = SITE_URL . '/data/uploads/default.gif';
    }else{
        $thumb = tsMakeThumbUp($filename,$width,$height,$t);
    }
    return $thumb;
}
function getThumb1($filename,$width=190,$height=240,$t='f') {
    if(empty($filename)){
        $thumb = '__THEME__/images/no_photo.gif';
    }else{
        if(strpos($filename, '/')){
            $thumb = tsMakeThumbUp($filename,$width,$height,$t);
        }else{
            $thumb = tsMakeThumbUp('event/'.$filename,$width,$height,$t);
        }
    }
    return $thumb;
}

function deletePath($savepath){
    $path = SITE_PATH . '/data/uploads/event/' . $savepath;
    if ( is_file($path) )
        unlink($path);
}

function getTypeTitle($typeId,$types=array()){
    if(empty($types)){
        $types = D('EventType')->getType();
    }
    return $types[$typeId];
}
/**
* _paramDate
* 解析日期
* @param mixed $date
* @access private
* @return void
*/
function _paramDate($date) {
   $date_list = explode(' ', $date);
   list( $year, $month, $day ) = explode('-', $date_list[0]);
   list( $hour, $minute, $second ) = explode(':', $date_list[1]);
   return mktime($hour, $minute, $second, $month, $day, $year);
}
function eventUpload() {
    //上传参数
    $options['max_size'] = getPhotoConfig('photo_max_size');
    $options['allow_exts'] = getPhotoConfig('photo_file_ext');
    //$options['save_path'] = UPLOAD_PATH . $path;
    $options['save_to_db'] = false;
    //执行上传操作
    $info = X('Xattach')->upload('event', $options);
    return $info;
}
function imgUploadDb($mid) {
    //上传参数
    $options['userId']		=	$mid;
    $options['max_size'] = getPhotoConfig('photo_max_size');
    $options['allow_exts'] = getPhotoConfig('photo_file_ext');
    //执行上传操作
    $info = X('Xattach')->upload('event', $options);
    return $info;
}
function get_flash_url($host, $flashvar) {
    if(!$host){
        return $flashvar;
    }
    $flashAddr = array(
        'youku.com' => 'http://player.youku.com/player.php/sid/FLASHVAR/v.swf',
        'ku6.com' => 'http://player.ku6.com/refer/FLASHVAR/v.swf',
        //'sina.com.cn' => 'http://vhead.blog.sina.com.cn/player/outer_player.swf?vid=FLASHVAR',
        'sina.com.cn' => 'http://you.video.sina.com.cn/api/sinawebApi/outplayrefer.php/vid=FLASHVAR/s.swf',
        //'tudou.com' => 'http://www.tudou.com/v/FLASHVAR',
        //'tudou.com' => 'http://www.tudou.com/v/FLASHVAR/&autoPlay=true/v.swf',
        //'youtube.com' => 'http://www.youtube.com/v/FLASHVAR',
        //'sohu.com' => 'http://v.blog.sohu.com/fo/v4/FLASHVAR',
        //'sohu.com' => 'http://share.vrs.sohu.com/FLASHVAR/v.swf',
        //'mofile.com' => 'http://tv.mofile.com/cn/xplayer.swf?v=FLASHVAR',
        'yixia.com' => 'http://paikeimg.video.sina.com.cn/splayer1.7.14.swf?scid=FLASHVAR&token=',
        't.cn' => 'http://paikeimg.video.sina.com.cn/splayer1.7.14.swf?scid=FLASHVAR&token=',
        'music' => 'FLASHVAR',
        'flash' => 'FLASHVAR'
    );
    $result = '';
    if (isset($flashAddr[$host])) {
        $result = str_replace('FLASHVAR', $flashvar, $flashAddr[$host]);
    }
    return $result;
}
function get_flash_img($img) {
    if (!$img) {
        return __THEME__ . '/images/nocontent.png';
    } else {
        return $img;
    }
}
function getCost($cost) {
    if ($cost == '0') {
        return '免费';
    } elseif ($cost == '1') {
        return 'AA制';
    } elseif ($cost == '2') {
        return '50元以下';
    } elseif ($cost == '3') {
        return '50-200元';
    } elseif ($cost == '4') {
        return '200-500元';
    } elseif ($cost == '5') {
        return '500-1000元';
    } elseif ($cost == '6') {
        return '1000元以上';
    }
}
function getSchoolDomain($sid){
    $school = M('school')->where('id='.$sid)->find();
    if($school){
        return $school['domain'];
    }
    return 'http://pocketuni.net';
}
function getJyPf(){
    //到课考勤5分，纪律考核5分，学习笔记10分，社会体验10分，团队活动10分，学习总结与规划5分。
    $res[] = array('到课考勤',5);
    $res[] = array('纪律考核',5);
    $res[] = array('学习笔记',10);
    $res[] = array('社会体验',10);
    $res[] = array('团队活动',10);
    $res[] = array('学习总结与规划',5);
    //政治生活锻炼10分，挂职实践10分，村官结对10分，课题调研10分，志愿服务10分，实践总结与党性分析报告5分
    $res[] = array('政治生活锻炼',10);
    $res[] = array('挂职实践',10);
    $res[] = array('村官结对',10);
    $res[] = array('课题调研',10);
    $res[] = array('志愿服务',10);
    $res[] = array('实践总结与党性分析报告',5);
    return $res;
}

function getSiteTitle(){
	return array(
		'my_group'=>'所在部落的最新动态-我的部落-',
		'my_group_new_topic' => '最新话题-我的部落-',
		'my_friend_group' => '好友的部落-',
		'all_group' => '发现部落-',
		'newTopic_all' => '所有话题-最新话题-',
		'newTopic_my_post' => '最新话题-我发表的话题-',
		'newTopic_my_reply' => '我回复的话题-最新话题-',
		'newTopic_my_collect' => '我收藏的话题-最新话题-',
		'issue_topic' => '发布话题-',
		'create_group' => '创建部落-',
		'add_topic' => '发表话题',
		'search_topic' => '搜索话题',
		'dist_topic'  =>'精华话题',
		'edit_topic' =>'编辑话题',
		'topic_index'=>'话题-',
		'album_index'=>'相册',
		'upload_pic'=>'上传图片',
		'all_photo'=>'全部照片',
		'all_album'=>'群相册',
		'file_index'=>'文件',
		'file_upload'=>'上传文件',
		'member_index'=>'成员',

	);
}

function formatsize($fileSize) {
    $size = sprintf("%u", $fileSize);
    if ($size == 0) {
        return("0 Bytes");
    }
    $sizename = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
    return round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . $sizename[$i];
}
function sjType($id=0){
    $roles = array('1'=>'先进单位申报','2'=>'优秀团队申报','3'=>'优秀团队 + 十佳团队申报','4'=>'先进个人申报','5'=>'先进个人 + 十佳使者申报','6'=>'先进工作者申报',
                    '7'=>'优秀社会实践基地申报','8'=>'优秀调研报告申报','9'=>'十佳风尚奖申报');
    if($id){
        return $roles[$id];
    }
    return $roles;
}
function sjStatus($id=0){
    $roles = array('1'=>'待初核','2'=>'初审驳回','3'=>'待终审','4'=>'终审驳回','5'=>'通过');
    if($id){
        return $roles[$id];
    }
    return $roles;
}
function sjTypeLimit($type){
    $roles = array('7'=>1,'8'=>2,'9'=>2);
    if(isset($roles[$type])){
        return $roles[$type];
    }
    return 0;
}
function isSjSchool($sid){
    $all = array(473,599,587,588,584,585,524,594,622,595,526,600,586,596,591,601,603,602,618,480,589,593,592,590,597,
        598,604,606,614,607,610,609,612,611,617,608,620,605,613,615,619,638,623,624,616,625,627,626,629,630,
        628,1,2,639,621,507,631,632,633,640,528,527,531,635,634,636,637,551,550,664,665,666,667,668,669,670,
        671,672,673,674,675,676);
    return in_array($sid, $all);
}
function showSjBack($sid){
    if($sid == 505 || isSjSchool($sid)){
        return true;
    }
    return false;
}
function getEventName($id){
    $res = M('event')->getField('title', 'id='.$id);
    if(!$res){
        $res = '';
    }
    return $res;
}