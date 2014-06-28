<?php

/**
 * 广告
 *
 * @version $id$
 * @copyright 2013-2015 陆冬云
 * @author 陆冬云 <rechner00@hotmail.com>
 * @license PHP Version 5.3
 */
import('home.Action.PubackAction');

class AdAction extends PubackAction {

    private $ad;

    public function _initialize() {
        parent::_initialize();
        $this->ad = D('Ad','admin');
    }

    public function ad() {
        $map['is_del'] = 0;
        $list =  $this->ad->where($map)->order('`level` ASC,`id` ASC')->findPage(20);
        $this->assign( $list);
        $this->assign('place_array', array('首页中间', '首页左下', 'HOME中间', 'HOME左下', '课件广告','客户端活动页','客户端微博页','客户端部落页','客户端好友页','客户端公告页','客户端应用页','客户端更多页'));
        $this->display();
    }

    public function addAd() {
        $citys = M('citys')->findAll();
        $thisYear = date('y', time());
        $years = array();
        for ($i = $thisYear - 3; $i <= $thisYear; $i++) {
            $years[] = sprintf("%02d", $i);
        }
        $this->assign('years', $years); //年级
        $this->assign('citys', $citys);
        $this->display();
    }

    public function doAddAd() {
        $_POST['title'] = h(t($_POST['title']));
        if (empty($_POST['title']))
            $this->error('标题不能为空');
        $map['sTime'] = $this->_pDate($_POST['sTime']);
        $map['eTime'] = $this->_pDate($_POST['eTime']);
        if ($map['sTime'] >= $map['eTime']) {
            $this->error("结束时间不得早于开始时间");
        }
        if (empty($_FILES['cover']['name'])) {
            $this->error('请上传广告图片');
        }
        //得到上传的图片
        $images = tsUploadImg($this->mid,'ad',true);
        if (!$images['status'] && $images['info'] != '没有选择上传文件') {
            $this->error($images['info']);
        }
        $_POST['url'] = $_POST['url'];
        if (!$_POST['url']) {
            $this->error('广告链接不能为空');
        }
        if (!$_POST['years']) {
            $this->error('请选择投放年级');
        }
        //学校处理
        $cityIds = M('citys')->field('id')->findAll();
        $schoolId = array();
        $sids = '';
        foreach ($cityIds as $v) {
            $str = 'schools' . $v['id'];
            if (isset($_POST[$str])) {
                if ($sids) {
                    $sids = $sids . ",";
                }
                $schoolId[$v['id']] = $_POST[$str];
                $sids .= implode(',', $schoolId[$v['id']]);
            }
        }
        //地区处理
        if (in_array(0, $_POST['area'])||$_POST['place']==0||$_POST['place']==1) {
            $map['areaId'] = 0;
            $sids = 0;
        } else {
            $map['areaId'] = implode(',', $_POST['area']);
        }
        //届数处理
        if (in_array(0, $_POST['years'])) {
            $years = 0;
            $map['year'] = 0;
        } else {
            $years = $_POST['years'];
            $map['year'] = implode(',', $_POST['years']);
        }
        if ($_POST['type'] == 1) {
            if ($_POST['price'] > $_POST['fund']) {
                $this->error('资金库里的钱不得小于单次点击价');
            }
            $map['price'] = $_POST['price'];
            $map['fund'] = $_POST['fund'];
        } else {
            $map['price'] = 0;
            $map['fund'] = 0;
        }
        if (($_POST['place'] == 0 || $_POST['place'] == 1) && $_POST['type'] == 1) {
            $this->error('选择首页中间，首页左下位置时，广告类型不能选择CPC类型');
        }
        $map['uid'] = $this->mid;
        $map['title'] = t($_POST['title']);
        $map['type'] = intval($_POST['type']);
        $map['place'] = intval($_POST['place']);
        $map['sid'] = $sids;
        $map['url'] = $_POST['url'];
        $map['level'] = intval($_POST['level']);
        $addId = $this->ad->doAddAd($map, $images);
        if ($addId) {
            $daoadLine = M('ad_line');
            $data['adId'] = $addId;
            if ($map['areaId'] == 0) {
                if ($years) {
                    foreach ($years as $val) {
                        $data['areaId'] = 0;
                        $data['sid'] = 0;
                        $data['year'] = $val;
                        $daoadLine->add($data);
                    }
                } else {
                    $data['areaId'] = 0;
                    $data['sid'] = 0;
                    $data['year'] = 0;
                    $daoadLine->add($data);
                }
            } else {
                foreach ($schoolId as $key => $v) {
                    foreach ($v as $value) {
                        if ($years) {
                            foreach ($years as $val) {
                                $data['year'] = $val;
                                $data['areaId'] = $key;
                                $data['sid'] = $value;
                                $daoadLine->add($data);
                            }
                        } else {
                            $data['year'] = 0;
                            $data['areaId'] = $key;
                            $data['sid'] = $value;
                            $daoadLine->add($data);
                        }
                    }
                }
            }
            $this->success('创建成功');
        } else {
            $this->error('添加失败');
        }
    }

    public function editAd() {
        $map['id'] = intval($_GET['id']);
        $map['is_del'] = 0;
        $ad = $this->ad->where($map)->find();
        if (empty($ad))
            $this->error('参数错误');
        $citys = M('citys')->findAll();
        $thisYear = date('y', time());
        $years = array();
        for ($i = $thisYear - 3; $i <= $thisYear; $i++) {
            $years[] = sprintf("%02d", $i);
        }

        $this->assign('years', $years); //年级

        if ($ad['areaId']) {
            $area = M('ad_line')->where('adId=' . $ad['id'])->group('areaId')->field('areaId')->findAll();
            $dao = M('school');
            foreach ($area as $k => $v) {
                $area[$k]['school'] = $dao->where('cityId=' . $v['areaId'])->field('id,title')->findAll();
            }
            $this->assign('area', $area);
            $res = M('ad_line')->where('adId=' . $ad['id'])->field('sid')->findAll();
            $sids = getSubByKey($res, 'sid');
            $this->assign('sids', $sids);
        }
        $this->assign('citys', $citys);
        $this->assign($ad);
        $this->display();
    }

    public function doEditAd() {
        if (($_POST['id'] = intval($_POST['id'])) <= 0){
             $this->error("错误链接");
        }

            $id = $_POST['id'];
        if (!$obj = $this->ad->where(array('id' => $id))->find()) {
            $this->error('广告不存在或已删除');
        }
            $_POST['title'] = h(t($_POST['title']));
        if (empty($_POST['title']))
            $this->error('标题不能为空');
        $map['sTime'] = $this->_pDate($_POST['sTime']);
        $map['eTime'] = $this->_pDate($_POST['eTime']);
        if ($map['sTime'] >= $map['eTime']) {
            $this->error("结束时间不得早于开始时间");
        }

        //得到上传的图片
        $images = tsUploadImg($this->mid,'ad',true);
        if (!$images['status'] && $images['info'] != '没有选择上传文件') {
            $this->error($images['info']);
        }
        $_POST['url'] = $_POST['url'];
        if (!$_POST['url']) {
            $this->error('广告链接不能为空');
        }
          if (!$_POST['years']) {
            $this->error('请选择投放年级');
        }
        //学校处理
        $cityIds = M('citys')->field('id')->findAll();
        $schoolId = array();
        $sids = '';
        foreach ($cityIds as $v) {
            $str = 'schools' . $v['id'];
            if (isset($_POST[$str])) {
                if ($sids) {
                    $sids = $sids . ",";
                }
                $schoolId[$v['id']] = $_POST[$str];
                $sids .= implode(',', $schoolId[$v['id']]);
            }
        }
        //地区处理
       if (in_array(0, $_POST['area'])||$_POST['place']==0||$_POST['place']==1) {
            $map['areaId'] = 0;
            $sids = 0;
        } else {
            $map['areaId'] = implode(',', $_POST['area']);
        }

          //届数处理
        if (in_array(0, $_POST['years'])) {
            $years = 0;
            $map['year'] = 0;
        } else {
            $years = $_POST['years'];
            $map['year'] = implode(',', $_POST['years']);
        }
         if ($_POST['type'] == 1) {
            if ($_POST['price'] > $_POST['fund']) {
                $this->error('资金库里的钱不得小于单次点击价');
            }
            $map['price'] = $_POST['price'];
            $map['fund'] = $_POST['fund'];
        } else {
            $map['price'] = 0;
            $map['fund'] = 0;
        }
           if (($_POST['place'] == 0||$_POST['place'] == 1)&&$_POST['type'] == 1) {
                $this->error('选择首页中间，首页左下位置时，广告类型不能选择CPC类型');
           }

        $map['uid'] = $this->mid;
        $map['title'] = t($_POST['title']);
        $map['type'] = intval($_POST['type']);
        $map['place'] = intval($_POST['place']);
        $map['sid'] = $sids;
        $map['url'] = $_POST['url'];
        $map['level'] = intval($_POST['level']);
        $saveId = $this->ad->doEditAd($map, $images, $obj);
        if ($saveId) {
            $daoadLine = M('ad_line');
            $daoadLine->where('adId=' . $obj['id'])->delete();
            $data['adId'] = $obj['id'];
            if ($map['areaId'] == 0) {
                if ($years) {
                    foreach ($years as $val) {
                        $data['areaId'] = 0;
                        $data['sid'] = 0;
                        $data['year'] = $val;
                        $daoadLine->add($data);
                    }
                } else {
                    $data['areaId'] = 0;
                    $data['sid'] = 0;
                    $data['year'] = 0;
                    $daoadLine->add($data);
                }
            } else {
                foreach ($schoolId as $key => $v) {
                    foreach ($v as $value) {
                        if ($years) {
                            foreach ($years as $val) {
                                $data['year'] = $val;
                                $data['areaId'] = $key;
                                $data['sid'] = $value;
                                $daoadLine->add($data);
                            }
                        } else {
                            $data['year'] = 0;
                            $data['areaId'] = $key;
                            $data['sid'] = $value;
                            $daoadLine->add($data);
                        }
                    }
                }
            }
            $this->success('修改成功');
        } else {
            $this->error('修改失败');
        }
    }

    public function doDeleteAd() {
        if (empty($_POST['ids'])) {
            echo 0;
            exit;
        }
        $map['id'] = array('in', t($_POST['ids']));
        $maps['adId'] = array('in', t($_POST['ids']));
        echo $this->ad->where($map)->setField('is_del', 1) ? '1' : '0';
        M('ad_line')->where($maps)->delete();
    }


    //点击广告成员
    public function adClick() {
        $adid = intval($_REQUEST['id']);
        $list = M('ad_click')->where('adId=' . $adid)->field('uid')->findPage(15);
        $dao = M('user');
        foreach ($list['data'] as $k => $val) {
            $list['data'][$k] = $dao->where('uid=' . $val['uid'])->field('email,year,sex,realname,uid')->find();
            $list['data'][$k]['email'] = getUserEmailNum($list['data'][$k]['email']);
        }
        $this->assign($list);
        $this->display();
    }


    public function excel() {
        set_time_limit(0);
         $adid = intval($_REQUEST['id']);
         $title = $this->ad->getField('title','id='.$adid);
        $list = M('ad_click')->where('adId=' . $adid)->field('uid')->findAll();
        $dao = M('user');
        foreach ($list as $k => $val) {
            $list[$k] = $dao->where('uid=' . $val['uid'])->field('realname,email,uid,major,year,sex')->find();
            $list[$k]['email'] = getUserEmailNum($list[$k]['email']);
            $list[$k]['uid'] = tsGetSchoolByUid($val['uid']);
            $list[$k]['sex'] = 1==$list[$k]['sex'] ? '男' : '女';
        }
        closeDb();
        $arr = array('姓名', '学号', '学校', '专业', '年级','性别');
        array_unshift($list, $arr);
        service('Excel')->export2($list,$title);
    }

    private function _pDate($date) {
        $date_list = explode(' ', $date);
        list( $year, $month, $day ) = explode('-', $date_list[0]);
        list( $hour, $minute, $second ) = explode(':', $date_list[1]);
        return mktime($hour, $minute, $second, $month, $day, $year);
    }

    public function school() {
        $cityId = intval($_REQUEST['cityId']);
        if (!$cityId) {
            exit(json_encode(array()));
        }
        $schools = M('school')->where('cityId=' . $cityId)->field('id,title')->findAll();
        if ($schools) {
            exit(json_encode($schools));
        }
        exit(json_encode(array()));
    }

}

?>