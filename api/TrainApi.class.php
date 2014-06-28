<?php

class TrainApi extends Api {

    private $train;

    public function __construct() {
        parent::__construct();
        $this->train = D('Train', 'train');
    }

    public function orgList() {
        $orgs = D('TrainOrg','train')->getAllOrgApi();
        return $orgs;
    }

    public function courseList() {
        $provinceId = intval($_REQUEST['provinceId']);
        if ($provinceId) {
            $map['provinceId'] = $provinceId;
        }
        $cityId = intval($_REQUEST['cityId']);
        if ($cityId) {
            $map['cityId'] = $cityId;
        } else {
            $city = t($_REQUEST['city']);
            if ($city) {
                $cityId = M('train_area')->getField('id', "title = '$city'");
                if ($cityId) {
                    $map['cityId'] = $cityId;
                }
            }
        }
        $catId = intval($_REQUEST['catId']);
        if ($catId) {
            $map['catId'] = $catId;
        }

        $page = intval($_REQUEST['page']);
        if (!$page) {
            $page = 1;
        }
        $count = intval($_REQUEST['count']);
        if (!$count) {
            $count = 10;
        }
        $list = $this->train->apiCourseList($map, $count, $page, $this->mid);
        if ($list) {
            return $list;
        } else {
            return array();
        }
    }
    
      public function defaultProvince() {
        $sid = M('user')->getField('sid', 'uid=' . $this->mid);
        $locid = M('school')->getField('cityId', 'id=' . $sid);
        $arr = array();
        $res = M('citys')->where('id=' . $locid)->field('pid,city')->find();
        $arr['city'] = $res['city'];
        $arr['pro'] = M('province')->getField('title', 'id=' . $res['pid']);
        return $arr;
    }

    public function cat() {
        return D('TrainCat','train')->getAllCatApi();
    }

    public function area() {
        $pid = intval($_REQUEST['pid']);
        return D('TrainArea','train')->getAllArea($pid);
    }

    public function collect() {
        $id = intval($_REQUEST['id']);
        $dao = D('TrainCollect','train');
        if ($dao->doCollect($this->mid,$id)) {
            return '收藏成功';
        }else{
            return $dao->getError();
        }
    }

    public function cancelCollect() {
        $id = intval($_REQUEST['id']);
        $dao = D('TrainCollect','train');
        if ($dao->cancelCollect($this->mid,$id)) {
            return '取消收藏成功';
        }else{
            return $dao->getError();
        }
    }

    public function course() {
        $id = intval($_REQUEST['id']);
        if ($id) {
            return $this->train->apiCourse($id,$this->mid);
        }
    }

    public function collectList() {
        $page = intval($_REQUEST['page']);
        if (!$page) {
            $page = 1;
        }
        $count = intval($_REQUEST['count']);
        if (!$count) {
            $count = 10;
        }
        $list = M('train_collect')->where('uid ='.$this->mid)->findAll();
        if ($list) {
            $ids = getSubByKey($list, 'courseId');
            $map['id'] = array('in', $ids);
            $lists = $this->train->apiCourseList($map, $count, $page, $this->mid,false);
            return $lists;
        }
        return array();
    }

}

?>
