<?php
class GroupApi extends Api{


	function test(){
		return 1;
	}

	function allgroup() {
        $group = D('Group', 'group');
        $user_sid = M('user')->getField('sid', 'uid=' . $this->mid);
        $school = $_REQUEST['school'] ? $_REQUEST['school'] : $user_sid;
        $dpart = intval($_REQUEST['dpart']);
        $sid1 = intval($_REQUEST['sid1']);
        $year = intval($_REQUEST['year']);
        $sort = intval($_REQUEST['sort']);
        $count = intval($_REQUEST['count']);
        if ($count <= 0) {
            $count = 20;
        }
        $condition[] = 'status=1';

        if ($school > 0) {
            $condition[] = 'school=' . $school;
        }
        if ($dpart > 0) {
            $condition[] = 'category=' . $dpart;
        }

        if ($sid1 > 0) {
            $condition[] = 'sid1=' . $sid1;
        }
        if ($year > 0) {
            $condition[] = 'year=' . $year;
        }

        if ($sort > 0) {
            $condition[] = 'cid0=' . $sort;
        }
        $order = 'membercount DESC';
        $list = $group->getGroupList(1, $condition, 'id,id AS gid,name,logo,membercount,school,ctime,cid0,sid1', $order, $count, 0);
		unset($list['html']);

		if(empty($list['data'])) {
			$list['data'] = array();
		}

		foreach($list['data'] as &$g) {
			$title = D('Category','group')->getField('title','id='.$g['cid0']);
			$g['cname0'] = $title;
			if($g['sid1']>0) {
				$g['schoolname'] = tsGetSchoolName($g['school']);
				$g['yname'] = tsGetSchoolName($g['sid1']);
			}
		}
		$result = $list;
		return $result;
	}

	function hotgroup(){
		$group = D('Group','group');

		$school = intval($_REQUEST['school']);

		$list = $group->getHotListForSchool($school);

		foreach($list as &$g) {
			$title = D('Category','group')->getField('title','id='.$g['cid0']);
			$g['cname0'] = $title;
			$title = D('Category','group')->getField('title','id='.$g['cid1']);
			$g['cname1'] = $title;
			if($g['school']>0) {
				$g['schoolname'] = tsGetSchoolName($g['school']);
			}
		}

		$result['data'] = $list;
		return $result;
	}

	function mygroup() {
        $group = D('Group', 'group');
        $page = intval($_REQUEST['page']);
        if (!$page) {
            $page = 1;
        }
        $count = intval($_REQUEST['count']);
        if (!$count) {
            $count = 10;
        }
        $list = $group->apiGetAllMyGroup($this->mid,0,$count,$page);
        foreach ($list as &$g) {
            $title = D('Category', 'group')->getField('title', 'id=' . $g['cid0']);
            $g['cname0'] = $title;
            $title = D('Category', 'group')->getField('title', 'id=' . $g['cid1']);
            $g['cname1'] = $title;
        }
        $result['data'] = $list;
        return $result;
    }

	function mygroupcount() {
		$group = D('Member','group');
		$count = $group->where('uid='.$this->mid." AND level != 0 ")->count();
		$response['response'] = $count;
		return $response;
	}

	function group() {
		$gid = trim(h($_REQUEST['gid']));

		$groupinfo = $this->getgroupinfo($gid);

	    $groupinfo['cname0'] 	= D('Category','group')->getField('title', array('id'=>$groupinfo['cid0']));
	    $groupinfo['cname1'] 	= D('Category','group')->getField('title', array('id'=>$groupinfo['cid1']));

		if($groupinfo['school']>0) {
			$groupinfo['schoolname'] = tsGetSchoolName($groupinfo['school']);
		}

	    $groupinfo['type_name'] = $groupinfo['brower_level'] == -1?'公开':'私密';
	    $groupinfo['tags']		= D('GroupTag','group')->getGroupTagList($gid);
		if(!is_array($groupinfo['tags'])) {
			$groupinfo['tags'] = array();
		}
		$groupinfo['usrename'] = getUserRealName($groupinfo['uid']);
		$groupinfo['filecount'] = D('Dir','group')->where('gid=' . $gid . ' AND is_del=0')->count()."";
		$groupinfo['topiccount'] = D('Topic','group')->where('gid=' . $gid . ' AND is_del=0')->count()."";


	  	// 判读当前用户的成员状态
      	$member_info = M('group_member')->where("uid={$this->mid} AND gid={$gid}")->find();
      	if ($member_info) {
      		if ($member_info['level'] > 0) {
				$groupinfo['ismember'] = 1;
     			if ($member_info['level'] == 1 || $member_info['level'] == 2) {
     				$groupinfo['isadmin'] = 1;
     			}
      		} else {
            	// 邀请加入
            	if (M('group_invite_verify')->where("gid={$gid} AND uid={$this->mid} AND is_used=0")->find()) {
					$groupinfo['isinvited'] = 1;
            	}
			}
      	}

		$result['data'] = $groupinfo;
		return $result;

	}

	function category() {
		$result['data'] = D('Category','group')->_makeTree($pid);
		return $result;
	}

	function topic() {
		$p = intval($_REQUEST['p']);
		$count = intval($_REQUEST['count']);
		$gid = trim(h($_REQUEST['gid']));
		$dao = D('Topic','group');
		$list = $dao
						  ->order('top DESC,replytime DESC')
						  ->where('is_del=0 AND gid=' . $gid)
						  ->findPage($count);
		unset($list['html']);
		if(empty($list['data'])) {
			$list['data'] = array();
		}
		return $list;
	}

	function doc() {
		$gid = trim(h($_REQUEST['gid']));
		$dao = D('Dir','group');
		$map[] = "gid={$gid}";
		$list = $dao->getFileList($html=1, $map, null, 'ctime DESC');
		unset($list['html']);
		if(empty($list['data'])) {
			$list['data'] = array();
		}
		return $list;
	}

	function member() {
		$p = intval($_REQUEST['p']);
		$count = intval($_REQUEST['count']);
		$gid = trim(h($_REQUEST['gid']));

		$isadmin = $this->isadmin($this->mid, $gid);

		$order = 'ctime DESC';
		$dao = M('group_member');

                if(!$_REQUEST['ismember']){
		$list = $dao->order($order)->where('gid=' . $gid . " AND status=1 AND level>0")->findPage($count);
                }else{
		$list = $dao->order($order)->where('gid=' . $gid . " AND status=1 AND level=0")->findPage($count);
                }
		if(empty($list['data'])) {
			$list['data'] = array();
		}
		foreach($list['data'] as &$g) {
			$g['face'] = getUserFace($g['uid'], "m");

			$user = D('User', 'home')->getUserByIdentifier($g['uid'], 'uid');
		    $g['user_school1'] = tsGetSchoolName($user['sid']);
		    $g['user_school2'] = tsGetSchoolName($user['sid1']);
		    //将来 年级，专业
		    //$res['year'] = $user['year'];
		    //$res['major'] = $user['major'];
			if($isadmin) {
		    	$g['user_mobile'] = $user['mobile'];
			}
		}
		unset($list['html']);

		return $list;
	}

    function search() {
        $user_sid = M('user')->getField('sid', 'uid=' . $this->mid);
        $key = t(h($_REQUEST['key']));
        $school = $user_sid;
        $count = intval($_REQUEST['count']);
        $page = intval($_REQUEST['p']);

        if ($count <= 0) {
            $count = 20;
        }
        if ($page <= 0) {
            $page = 1;
        }
        $offset = ($page - 1) * $count;

        // 关键字不能超过30个字符
        if (mb_strlen($key, 'UTF8') > 30) {
            $key = mb_substr($key, 0, 30, 'UTF8');
        }
        if (mb_strlen($key, 'UTF8') < 2) {
            $group_list['count'] = 0;
            $group_list['data'] = array();
            return $group_list;
        }
        $search_key = $key;

        $db_prefix = C('DB_PREFIX');
        if ($search_key) {
            $map['is_del'] = 0;
            $map['school'] = $school;
            $map['name'] = array('like','%'.$search_key.'%');
            $res = D('Group', 'group')
                    ->field('id,name,logo,membercount,ctime,cid0')
                    ->where($map)->limit("$offset,$count")->select();
            $dao = D('Category', 'group');
            foreach ($res as $k=>$v) {
                $res[$k]['cname0'] = $dao->getField('title', array('id' => $v['cid0']));
                unset($res[$k]['cid0']);
            }
            $group_list = array();
            $group_list['data'] = $res;
            return $group_list;

            //$tag_id = M('tag')->getField('tag_id', "tag_name='{$search_key}'");
            //$map = "g.is_del=0 AND (g.name LIKE '%{$search_key}%' OR g.intro LIKE '%{$search_key}%'";
            $map = "g.is_del=0 AND (g.name LIKE '%{$search_key}%'";
//            if ($tag_id) {
//                $map .= ' OR t.tag_id=' . $tag_id;
//                $tag_id_score = "+IF(t.tag_id={$tag_id},2,0)";
//            }
            $map .= ')';
            if ($school > 0) {
                $map .= " AND g.school=" . $school;
            }
            $group_count = D('Group', 'group')->field('COUNT(DISTINCT(g.id)) AS count')
                    ->table("{$db_prefix}group AS g LEFT JOIN {$db_prefix}group_tag AS t ON g.id=t.gid")
                    ->where($map)
                    ->find();
            if ($count * ($page - 1) <= $group_count['count']) {
                $group_list = D('Group', 'group')->field('DISTINCT(g.id),g.name,g.intro,g.logo,g.cid0,g.cid1,g.membercount,g.ctime,g.school')
                                ->table("{$db_prefix}group AS g LEFT JOIN {$db_prefix}group_tag AS t ON g.id=t.gid")
                                ->where($map)->findPage($count, $group_count['count']);

                unset($group_list['html']);
            }
        }

        if (empty($group_list['data'])) {
            $group_list['data'] = array();
            return $group_list;
        }

        foreach ($group_list['data'] as &$group) {
            // 群分类
            $group['cname0'] = D('Category', 'group')->getField('title', array('id' => $group['cid0']));
            $group['cname1'] = D('Category', 'group')->getField('title', array('id' => $group['cid1']));

            if ($group['school'] > 0) {
                $group['schoolname'] = tsGetSchoolName($group['school']);
            }
        }
        return $group_list;
    }
	// 社团创建的邀请
    public function invite() {
        $res['status'] = 0;
        $gid = trim(h($_REQUEST['gid']));
        $ids = trim($_REQUEST["ids"]);
        $toUserIds = explode(",", $ids);

        if (empty($toUserIds) || empty($toUserIds[0])) {
            $res['msg'] = '请选择要邀请的朋友';
            return $res;
        }

        $ismember = $this->isJoinGroup($this->mid, $gid);

        if (!$ismember) {
            $res['msg'] = '您不是社团成员,无法邀请';
            return $res;
        }
        $groupinfo = M('group')->field('name,school')->where('id=' . $gid . " AND is_del=0 and status>0")->find();
        if (!$groupinfo) {
            $res['msg'] = '该社团不存在，或者被删除';
            return $res;
        }
        $daoUser = M('user');
        foreach ($toUserIds as $k => $v) {
            if (!$v)
                continue;
            if (M('group_member')->where("gid={$gid} AND uid={$v}")->count() > 0) {
                unset($toUserIds[$k]);
                continue;
            }
            $userSid = $daoUser->getField('sid','uid='.$v);
            if(!$userSid || $userSid != $groupinfo['school']){
                $res['msg'] = '邀请失败,不可邀请其它学校成员加入该社团！';
                return $res;
            }
//            if ($isadmin) {
//                $invite_verify_data['gid'] = $gid;
//                $invite_verify_data['uid'] = $v;
//                M('group_invite_verify')->add($invite_verify_data);
//            }
        }

        $message_data['title'] = "邀您加入部落 {$groupinfo['name']}";
        $domain = M('school')->getField('domain', 'id='.$groupinfo['school']);
        $url = 'http://'.$domain.'.pocketuni.net/index.php?app=event&mod=GroupTopic&act=index&gid='.$gid;
        $message_data['content'] = "你好，诚邀您加入“{$groupinfo['name']}” 部落，" . $url;
        foreach ($toUserIds as $t_u_k => $t_u_v) {
            $message_data['to'] = $t_u_v;
            $mes = model('Message')->postMessage($message_data, $this->mid);
            if (!$mes) {
                unset($toUserIds[$t_u_k]);
            }
        }
        if (count($toUserIds) > 0) {
            $res['status'] = 1;
            $res['msg'] = '邀请成功！';
            return $res;
        } else {
            $res['msg'] = '邀请失败,您的好友可能已经加入了该部落！';
            return $res;
        }
        $res['msg'] = '邀请失败';
        return $res;
    }


	//做创建操作
	public function create()
	{
             $this->MobileError('无法创建');
		$config = model('Xdata')->lget('group');

		if (0 == $config['createGroup']) {
			// 系统后台配置关闭创建
			$this->MobileError('社团创已经关闭');
		} else if ($config['createMaxGroup'] <= D('Group','group')->where('is_del=0 AND uid='.$this->mid)->count()) {
			//系统后台配置要求，如果超过，则不可以创建
			$this->MobileError('你不可以再创建了，超过系统规定数目');
		}


		$group['uid']   = $this->mid;
		$group['name']  = h(t($_REQUEST['name']));
		$group['intro'] = h(t($_REQUEST['intro']));
		$group['cid0']  = intval($_REQUEST['cid0']);
		intval($_REQUEST['cid1']) > 0	&& $group['cid1']  = intval($_REQUEST['cid1']);

		$group['school']  = (intval($_POST['school0']) > 0)?intval($_POST['school0']):0;

		if (!$group['name']) {
			$this->MobileError('标题不能为空');
		} else if (get_str_length($_REQUEST['name']) > 20) {
			$this->MobileError('标题不能超过20个字');
		}

		if (D('Category','group')->getField('id', 'name=' . $group['name'])) {
			$this->MobileError('请选择群分类');
		}
		if (get_str_length($_REQUEST['intro']) > 60) {
			$this->MobileError('社团简介请不要超过60个字');
		}

		//if (!preg_replace("/[,\s]*/i", '' ,$_REQUEST['tags']) || count(array_filter(explode(',', $_REQUEST['tags']))) > 5) {
		//	$this->MobileError('标签不能为空或者不要超过五个');
		//}


		$group['type']  = $_REQUEST['type'] == 'open'?'open':'close';

		/*
		$group['need_invite'] = intval($config[$group['type'] . '_invite']);  //是否需要邀请
		$group['need_verify'] = intval($config[$group['type'] . '_review']);   //申请是否需要同意
		$group['actor_level'] = intval($config[$group['type'] . '_sayMember']);  //发表话题权限
		$group['brower_level'] = intval($config[$group['type'] . '_viewMember']); //浏览权限
		*/
		$group['need_invite']  = intval($config[$group['type'] . '_invite']);  //是否需要邀请
		$group['brower_level'] = $_REQUEST['type'] == 'open'?'-1':'1'; //浏览权限

		//fix shetuan permission
		$group['type'] = "open";
		$group['need_invite'] = 1;
		$group['brower_level'] = "-1";

		$group['openUploadFile'] = intval($config['openUploadFile']);
		$group['whoUploadFile'] = intval($config['whoUploadFile']);
		$group['whoDownloadFile'] = 3;
		$group['openAlbum'] = intval($config['openAlbum']);
		$group['whoCreateAlbum'] = intval($config['whoCreateAlbum']);
		$group['whoUploadPic'] = intval($config['whoUploadPic']);
		$group['anno'] = intval($_REQUEST['anno']);
		$group['ctime'] = time();

		if (1 == $config['createAudit']) {
			$group['status'] = 0;
		}

		if($_FILES['logo']['size'] > 0) {
	        // 社团LOGO
	 		$options['userId']		=	$this->mid;
			$options['max_size']    =   10*1024*1024;  //2MB
			$options['allow_exts']	=	'jpg,png';
	        $info	=	X('Xattach')->upload('group_logo',$options);
		    if($info['status']) {
			    $group['logo'] = $info['info'][0]['savepath'] . $info['info'][0]['savename'];
		    }
		}

	    $gid = D('Group','group')->add($group);

		if($gid) {
			// 把自己添加到成员里面
			D('Group','group')->joingroup($this->mid, $gid, 1, $incMemberCount=true);

			// 添加社团标签
			D('GroupTag','group')->setGroupTag($_REQUEST['tags'], $gid);

			// 积分操作
			//X('Credit')->setUserCredit($this->mid,'add_group');

			S('Cache_MyGroup_'.$this->mid,null);

			if (1 == $config['createAudit']) {
				$response['response'] = '创建成功，请等待审核';
				return $response;
			} else {
				$response['response'] = '创建成功';
				return $response;
			}
		} else {
			$this->MobileError('创建失败');
		}

		$this->MobileError('创建失败');
	}

	public function modify() {
		$gid = trim(h($_REQUEST['gid']));
		$group['intro'] = h(t($_REQUEST['intro']));

		if (get_str_length($group['intro']) > 60) {
			$this->MobileError('社团简介请不要超过60个字');
		}
		//if (!preg_replace("/[,\s]*/i", '', $_REQUEST['tags']) || count(array_filter(explode(',', $_REQUEST['tags']))) > 5) {
		//	$this->MobileError('标签不能为空或者不要超过5个');
		//}

		if($_FILES['logo']['size'] > 0) {
			// 社团LOGO
	 		$options['userId']		=	$this->mid;
			$options['max_size']    =   10*1024*1024;  //2MB
			$options['allow_exts']	=	'jpg,png';
	        $info	=	X('Xattach')->upload('group_logo',$options);
		    if($info['status']) {
			    $group['logo'] = $info['info'][0]['savepath'] . $info['info'][0]['savename'];
		    }
	    }

		$res = D('Group','group')->where('id=' . $gid)->save($group);

		if ($res !== false) {
			//D('Log','group')->writeLog($gid, $this->mid, '修改社团基本信息', 'setting');
			// 更新社团标签
			D('GroupTag','group')->setGroupTag($_REQUEST['tags'], $gid);
			$response['response'] = '保存成功';
			return $response;
		}
		$this->MobileError('保存失败');
	}

	//退出社团
    public function quitGroup() {

		$gid = trim(h($_REQUEST['gid']));

		$ismember = $this->isJoinGroup($this->mid,$gid);

        if($this->iscreater($this->mid,$gid) || !$ismember) {
			//社团不可以退出
			$this->MobileError('您不可以退出本社团');
		}
        $res = M('group_member')->where("uid={$this->mid} AND gid={$gid}")->delete();  //用户退出
        if($res){
            D('Group','group')->setDec('membercount', 'id=' . $gid);     //用户数量减少1
            S('Cache_MyGroup_'.$this->mid,null);
			$response['response'] = '退出成功';
			return $response;
        }
    }

	//群公告
	public function announce()
	{
		$gid = trim(h($_REQUEST['gid']));
		$groupinfo['announce'] = t(getShort($_REQUEST['announce'], 60));

		if(!$this->isadmin($this->mid,$gid)) {
			$this->MobileError('您没有权限');
		}
		$res = D('Group','group')->where('id='.$gid)->save($groupinfo);

		if ($res !== false) {
			$response['response'] = '操作成功';
			return $response;
		} else {
			$this->error('操作失败');
		}
	}

    //删除社团
    public function delGroup() {
       $this->MobileError('您不可以解散本社团');
		$gid = trim(h($_REQUEST['gid']));
        if(!$this->iscreater($this->mid,$gid))  $this->MobileError('您没有权限解散社团');
        D('Group','group')->remove($gid);
		$response['response'] = '解散成功';
		return $response;
    }

	public function joinGroup() {

		$gid = trim(h($_REQUEST['gid']));
		$reason = h(t($_REQUEST['reason']));

		$level = 0;
        $incMemberCount = false;

		if($this->isJoinGroup($this->mid,$gid)) {
			$this->MobileError('您己经是社团成员');
		}

		if($this->isPendingJoinGroup($this->mid,$gid)) {
			$this->MobileError('您正在等待管理员审核');
		}

		$response = array();
		$groupinfo = $this->getgroupinfo($gid);
$user_sid = M('user')->getField('sid', 'uid='.$this->mid);
if(!$user_sid || $user_sid != $groupinfo['school']){
    $this->MobileError('您不是该校成员，不可加入');
}
        if ($this->isInvited($this->mid, $gid)) {
            M('group_invite_verify')->where("gid={$gid} AND uid={$this->mid} AND is_used=0")->save(array('is_used'=>1));
            if (0 === intval($_REQUEST['accept'])) {
				$response['response'] = '拒绝加入';
				return $response;
            } else {
                // 接受邀请加入
                $level = 3;
                $incMemberCount = ture;
				$response['response'] = '加入成功';
            }
        } else if ($groupinfo['need_invite'] == 0) {
            // 直接加入
            $level = 3;
            $incMemberCount = ture;
			$response['response'] = '加入成功';
        } else if ($groupinfo['need_invite'] == 1) {
            // 需要审批，发送私信到管理员
            $level = 0;
            $incMemberCount = false;
            // 添加通知
            $toUserIds = M('group_member')->field('uid')->where('gid='.$gid.' AND (level=1 or level=2)')->findAll();
            foreach ($toUserIds as $k=>$v) {
                $toUserIds[$k] = $v['uid'];
            }

             $domain = M('school')->getField('domain', 'id=' . $groupinfo['school']);
            $url = 'http://' . $domain . '.pocketuni.net/index.php?app=event&mod=GroupManage&act=memberManage&gid=' . $gid.'&type=apply';
            $message_data['title'] = "申请加入校园部落 {$groupinfo['name']}";
            $message_data['content'] = "你好，请求你批准加入“{$groupinfo['name']}” 校园部落，"
                    . '<a href="' . $url . '"> 【点此】 </a>' . '进行操作。';
            $message_data['to'] = $toUserIds;
            $res = model('Message')->postMessage($message_data, $this->mid, false);

			$message = "己提交加入申请,等待审核";
			$response['message'] = $message;
        }

        $result = D('Group','group')->joinGroup($this->mid, $gid, $level, $incMemberCount, $reason);   //加入
        S('Cache_MyGroup_'.$this->mid,null);

		return $response;
	}

	public function kissy(){

		//执行附件上传操作
		$attach_type	=	'kissy';
		$options['uid']			=	$this->mid;
		$options['allow_exts']	=	'jpg,jpeg,bmp,png,gif';
		$info	=	X('Xattach')->upload($attach_type,$options);

		if(is_array($info['info'])){
			$image_url	=	SITE_URL.'/data/uploads/'.$info['info'][0]['savepath'].$info['info'][0]['savename'];
		}

		//上传成功
		if($info['status']==true){
			$response['response'] = $image_url;
		}else{
			$response['message'] = $info['info'];
		}

		return $response;
	}

	// 添加内容
	public function newtopic()
	{

		$gid = trim(h($_REQUEST['gid']));

		$ismember = $this->isJoinGroup($this->mid,$gid);

		if(!$ismember) {
			$this->MobileError('您不是社团成员,无法发贴');
		}

		$title = getShort($_REQUEST['title'], 30);
		if(empty($title)) $this->MobileError('标题不能为空');
		$this->__checkContent($_REQUEST['content'], 4, 5000);
		$topic['attach'] = $this->_setTopicAttach($gid);	// 附件信息
		$topic['gid'] = $gid;
		$topic['uid'] = $this->mid;
		$topic['name'] = getUserName($this->mid);
		$topic['title'] = h(t($title));
		$topic['cid']   = intval($_REQUEST['cid']);
		$topic['addtime'] = time();
		$topic['replytime'] = time();

		$flash = trim(h($_REQUEST['flash']));

		$content = t(h($_REQUEST['content']));

		$attach_type	=	'kissy';
		$options['uid']			=	$this->mid;
		$options['allow_exts']	=	'jpg,jpeg,bmp,png,gif';
		$info	=	X('Xattach')->upload($attach_type,$options);

		if(is_array($info['info'])){
			$image_url	=	SITE_URL.'/data/uploads/'.$info['info'][0]['savepath'].$info['info'][0]['savename'];
		}

		//上传成功
		if($info['status']==true) {
			$content = $content."<img src=\"".$image_url."\" class=\"hand\">";
		}

		if(strlen($flash)>0) {
			$content = $content."<object width=\"300\" height=\"300\" style=\"margin:5px;float:none;\" wmode=\"transparent\" classid=\"clsid:d27cdb6e-ae6d-11cf-96b8-444553540000\"><param name=\"quality\" value=\"high\"><param name=\"movie\" value=\"".$flash."\"><param name=\"wmode\" value=\"transparent\"><embed width=\"300\" height=\"300\" style=\"margin:5px;float:none;\" wmode=\"transparent\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" quality=\"high\" src=\"".$flash."\" type=\"application/x-shockwave-flash\"></object>";
		}
		if($tid = D('Topic','group')->add($topic)) {
			$post['gid'] = $gid;
			$post['uid'] = $this->mid;
			$post['tid'] = $tid;
			$post['content'] = $content;
			$post['istopic'] = 1;
			$post['ctime'] = time();
			$post['ip'] = get_client_ip();
			$post_id = D('Post','group')->add($post);

			$response = $post_id;
			return $response;
		}

		$this->MobileError('发帖失败');

	}
	// 话题显示
	public function viewtopic()
	{
		$topic = D('Topic', 'group');
		$post = D('Post', 'group');

		$tid = intval($_REQUEST['tid']) > 0 ? $_REQUEST['tid'] : 0;

		$count = intval($_REQUEST['count']);
		if($count<=0) {
			$count = 20;
		}

		$p = $_REQUEST[C('VAR_PAGE')] ? intval($_REQUEST[C('VAR_PAGE')]) : 1;
		if($tid == 0) $this->error('参数错误');

		$topic->setInc('viewcount','id='.$tid);
		$thread = $topic->getThread($tid);     //获取主题

		// 判读帖子存不存在
		if(!$thread) $this->error('帖子不存在');

		$thread['face'] = getUserFace($thread['uid'], "m");
		$thread['content'] = html_entity_decode($thread['content'], ENT_QUOTES);
		//print_r($thread['content']);
		$thread['content'] = strip_tags($thread['content'], "<img><a>");

		$postlist = $post->order('istopic DESC')->where('is_del = 0 AND tid='.$tid)->findPage($count);
		unset($postlist['html']);
		if(empty($postlist['data'])) {
			$postlist['data'] = array();
		}
		if($postlist['nowPage']<$p) {
			$postlist['data'] = array();
		}


		foreach($postlist['data'] as &$g) {
			$g['name'] = getUserName($g['uid']);
			$g['content'] = html_entity_decode($g['content'], ENT_QUOTES);
			$g['content'] = strip_tags($g['content'], "<img><a>");
		}

		$response['topic'] = $thread;
		$response['tid'] = $tid;
		$response['reply'] = $postlist['data'];
		$response['start_floor'] = intval((1 == $p) ? (($p - 1) * $limit + 1) : (($p - 1) * $limit) );

		return $response;

	}
	//话题回复
	public function replytopic()
	{

		$gid = trim(h($_REQUEST['gid']));

		//权限判读
		$tid = is_numeric($_REQUEST['tid']) ? intval($_REQUEST['tid']) : 0;

		if($tid > 0) {
			$topic = D('Topic','group')->field('id,uid,title,`lock`')->where("gid={$gid} AND id={$tid} AND is_del=0")->find();  //获取话题内容
			if (!$topic) {
				$this->error('帖子不存在或已被删除');
			} else if($topic['lock'] == 1) {
				$this->error('帖子已被锁定，不可回复');
			}

			$this->__checkContent($_REQUEST['content'], 4, 10000);

			$post['gid'] = $gid;
			$post['uid'] = $this->mid;
			$post['tid'] = $tid;
			$post['content'] = t(h($_REQUEST['content']));
			$post['istopic'] = 0;
			$post['ctime'] = time();
			$post['ip'] = get_client_ip();
			//print_r($post);exit;
			$result = D('Post','group')->add($post);  //添加回复
			if($result) {
				if ($topic['uid'] != $this->mid && $post_info['uid'] != $topic['uid']) {
					// 发送通知
					$notify_dao = service ( 'Notify' );
					$notify_data = array (
									'title'   => $topic['title'],
									'content' => strip_tags(getShort(html_entity_decode($post['content']), 60, '...')),
								   	'gid' 	  => $gid,
									'tid'	  => $topic['id'],
								   );
					$notify_dao->send($topic['uid'], 'event_group_topic_reply', $notify_data, $this->mid);
    				D('GroupUserCount','group')->addCount($post_info['uid'], 'bbs');
				}
				D('Topic','group')->setField('replytime', time(), 'id='.$tid);
				D('Topic','group')->setInc('replycount', 'id='.$tid);
			}
			$response = 1;
			return $response;
		}
		$this->error('帖子参数错误');
	}

	//上传文件
	function uploadfile() {

		$gid = trim(h($_REQUEST['gid']));

		if(!$this->isJoinGroup($this->mid,$gid)) {
			$this->error('对不起，您不是社团内成员');
		}

		$isadmin = $this->isadmin($this->mid,$gid);

		//系统后台配置仅管理员可以上传
		if($this->groupinfo['whoUploadFile'] == 2 && !$isadmin) {
			$this->error('对不起，仅管理员可以上传文件');
		}

		$config = model('Xdata')->lget('group');

		$usedSpace = D('Dir','group')->where('gid='.$gid.' AND is_del=0')->sum('filesize'); //判读空间大小
		if($usedSpace >= $config['spaceSize']*1024*1024) {
			$this->error('空间已经使用完');//如果使用完，提示错误信息
		}

		if ($_FILES['uploadfile']['size'] <= 0) {
			$this->error('请选择上传文件');
		}

		//上传参数
		$upload['max_size']   = $config['simpleFileSize']*1024*1024;
		$info = X('Xattach')->upload('group_file',$upload);

    	//执行上传操作
    	if($info['status']){  //上传成功
    		list($uploadFileInfo) = $info['info'];

    		$attchement['gid'] = $gid;
    		$attchement['uid'] = $this->mid;
    		$attchement['attachId'] = $uploadFileInfo['id'];
    		$attchement['name'] = $uploadFileInfo['name'];
    		$attchement['note'] = !empty($_POST['note']) ? t($_POST['note']) : '';
        	$attchement['filesize'] = $uploadFileInfo['size'];
        	$attchement['filetype'] = $uploadFileInfo['extension'];
        	$attchement['fileurl'] = $uploadFileInfo['savepath'] . $uploadFileInfo['savename'];
        	$attchement['ctime'] = time();
        	$result = D('Dir','group')->add($attchement);

        	if($result) {
				$response['response'] = "上传成功";
				return $response;
        	}else{
        		$this->error('保存文件失败');
        	}
    	}else{
    		$this->error($info['info']);
    	}

		$this->error('保存文件失败');
	}

	public function user()
	{
		$response = getUserInfo($this->mid);
		return $response;
	}

	private function __checkContent($content, $mix = 5, $max = 5000)
	{
			$content_length = get_str_length($content, true);
			if (0 == $content_length) {
				$this->MobileError('内容不能为空');
			} else if ($content_length < $mix) {
			 	$this->MobileError('内容不能少于' . $mix . '个中文或者'.($mix*2).'个英文');
			} else if ($content_length > $max) {
			 	$this->MobileError('内容不能超过' . $max . '个中文或者'.($max*2).'个英文');
			}
	}

		// 处理表单附件信息
		private function _setTopicAttach($gid, $old_attach = '')
		{
			$isadmin = $this->isadmin($this->mid, $gid);

			$groupinfo = $this->getgroupinfo($gid);

			$attach = $_REQUEST['attach'];
			// 文件功能是否开启
			if ($groupinfo['openUploadFile']) {
				// 文件上传权限
				if ($groupinfo['whoUploadFile'] == 3 || ($groupinfo['whoUploadFile'] == 2 && $isadmin)) {
					// 添加附件
					if ($attach) {
						if (count($attach) > 3){
							$this->MobileError('附件数量不能超过3个');
						}
						array_map('intval', $attach);
						$map['id'] = array('in', $attach);
						D('Dir','group')->setField('is_del', 0, $map);
					}
					// 处理删除的附件的
					$old_attach = unserialize($old_attach);
					if (is_array($attach) ) {
						$del_attach = array_diff($old_attach, $attach);
					} else {
						$del_attach = $old_attach;
					}
					D('Dir','group')->remove($del_attach);

					return serialize($attach);
				} else {
					return $old_attach;
				}
			} else {
				return $old_attach;
			}
		}


	//判读是不是创建者
	private function iscreater($uid,$gid) {
		return M('group_member')->where("uid=$uid AND gid=$gid AND level=1")->count();
	}


	//判读是不是管理员
	private function isadmin($uid,$gid) {
		$ret = M('group_member')->where("uid=$uid AND gid=$gid AND (level=1 OR level=2)")->count();

		return $ret;
	}

	//判读是不是成员
	private function ismember($uid,$gid) {
		return M('group_member')->where("uid=$uid AND gid=$gid AND level=3")->count();
	}

	//判读是不是在群里面
	private function isJoinGroup($uid,$gid) {
		return M('group_member')->where("uid=$uid AND gid=$gid AND level>0")->count();
	}

	private function isPendingJoinGroup($uid,$gid) {
		return M('group_member')->where("uid=$uid AND gid=$gid AND level=0")->count();
	}

	private function isInvited($uid,$gid) {
		if (M('group_invite_verify')->where("gid={$gid} AND uid={$uid} AND is_used=0")->find()) {
			return true;
		}
		return false;
	}

	private function getgroupinfo($gid) {

		$groupinfo = D('Group','group')->where('id='.$gid." AND is_del=0")->find();
		if (!$groupinfo) {
        	$this->MobileError('该社团不存在，或者被删除');
        } else if (0 == $groupinfo['status']) {
	 		$this->MobileError("该社团正在审核中");
	 	}
		return $groupinfo;
	}

	private function error($error="MobileError"){
		$this->MobileError($error);
	}

	private function MobileError($error="MobileError"){
		header('Content-Type: application/json; charset=utf-8');
		$result['message'] = $error;
		echo json_encode($result);
		exit;
	}

	public function ditus() {
		$count = intval($_REQUEST['count']);
		$school = intval($_REQUEST['school']);
		if($count<=0) {
			$count = 20;
		}
		$order = 'sort ASC,id ASC';
		$map = array();
		if($school>0) {
			$map['school'] = $school;
		}
		$items = M('group_ditu_list')->where($map)->order($order)->findPage($count);
		unset($items['html']);
		if(empty($items['data'])) {
			$items['data'] = array();
		}
		return $items;
	}

	public function ditu() {
		$map['listid'] = intval($_REQUEST['id']);
		$count = intval($_REQUEST['count']);
		if($count<=0) {
			$count = 20;
		}
		$order = 'sort ASC,id ASC';
		$items = M('group_ditu')->where($map)->order($order)->findPage($count);
		unset($items['html']);
		if(empty($items['data'])) {
			$items['data'] = array();
		}
		return $items;
	}

        public function groupCategory() {
        $data = array();
          $data['dpart'] = array(
            array('id'=>'1','title'=>'学生部门'),
            array('id'=>'2','title'=>'团支部'),
            array('id'=>'3','title'=>'学生社团')
            );

        $user_sid = M('user')->getField('sid', 'uid=' . $this->mid);
        $data['school'] = model('Schools')->makeLevel0Tree($user_sid);
        $a = array('id' => '-1', 'title' => '校级');
        array_unshift($data['school'], $a);
       $thisYear = date('y', time());
        $years = array();
        for ($i = 9; $i <= $thisYear; $i++) {
            $data['year'][] = sprintf("%02d", $i);
        }
        $data['category'] = M('group_category')->field('id,title')->findAll();
        return $data;
    }

    public function memberAction() {
        $result['status'] =0;
        $gid = intval($_REQUEST['gid']);
        $uid = intval($_REQUEST['uid']);
        if (!isset($_REQUEST['action']) || !in_array($_REQUEST['action'], array('admin', 'normal', 'out', 'allow'))) {
            $this->MobileError('非法操作');
        }
        switch ($_REQUEST['action']) {
            case 'admin':  // 设置成管理员
                if (!$this->_iscreater($this->mid, $gid)) {
                    $result['msg'] = '创建者才有的权限';  // 创建者才可以进行此操作
                }
                $content = '将用户 ' . getUserSpace($uid, 'fn', '_blank', '@' . getUserName($uid)) . '提升为管理员 ';
                $res = D('GroupMember','event')->where('gid=' . $gid . ' AND uid=' . $uid. ' AND level<>1')->setField('level', 2);   //3 普通用户
                break;
            case 'normal':   // 降级成为普通会员
                if (!$this->_iscreater($this->mid, $gid)) {
                    $result['msg'] ='创建者才有的权限';  // 创建者才可以进行此操作
                }
                $content = '将用户 ' . getUserSpace($uid, 'fn', '_blank', '@' . getUserName($uid)) . '降为普通会员 ';
                $res = D('GroupMember','event')->where('gid=' . $gid . ' AND uid=' .$uid . ' AND level=2')->setField('level', 3);   //3 普通用户
                if ($res) {
                    M('event_group')->where('gid=' . $gid . ' AND uid=' . $this->mid)->delete();  //删除社团活动发起权限
                }

                break;
            case 'out':     // 剔除会员
                $content = '将用户 ' . getUserSpace($uid, 'fn', '_blank', '@' . getUserName($uid)) . '剔除部落 ';
                if ($this->_iscreater($this->mid, $gid)) {
                    $level = ' AND level<>1';
                } else {
                    $level = ' AND level<>1 AND level<>2';
                }
                $current_level = D('GroupMember','event')->getField('level', 'gid=' . $gid . ' AND uid=' . $uid . $level);
                $res = D('GroupMember','event')->where('gid=' . $gid . ' AND uid=' . $uid . $level)->delete();   //剔除用户
                if ($res) {
                    if($current_level){
                    D('EventGroup','event')->setDec('membercount', 'id=' . $gid);     //用户数量减少1
                    }
                    M('event_group')->where('gid=' . $gid . ' AND uid=' . $uid)->delete();   //删除社团活动发起权限
                }
                break;
            case 'allow':   // 批准成为会员
                $content = '将用户 ' . getUserSpace($uid, 'fn', '_blank', '@' . getUserName($uid)) . '批准成为会员 ';
                $res = D('GroupMember','event')->where('gid=' . $gid . ' AND uid=' . $uid . ' AND level=0')->setField('level', 3);   //level级别由0 变成 3
                if ($res) {
                    D('EventGroup','event')->setInc('membercount', 'id=' . $gid); //增加一个成员
                }
                break;
        }
            if ($res) {
                $result['status'] = 1;
            D('GroupLog','event')->writeLog($gid, $this->mid, $content, 'member');
        }
      return $result;
    }



    //判读是不是创建者
    private function _iscreater($uid, $gid) {
        return D('GroupMember','event')->where("uid=$uid AND gid=$gid AND level=1")->count();
    }
}
