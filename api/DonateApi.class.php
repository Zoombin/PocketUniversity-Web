<?php

class DonateApi extends Api {

    private $donate;

    public function __construct() {
        parent::__construct();
        $this->donate = D('DonateProduct', 'shop');
    }

    public function donateList() {
        $map['isDel'] = 0;
        $map['status'] = 2;
        $map['buyer'] = 0;
        $provinceId = intval($_REQUEST['provinceId']);
        if ($provinceId) {
            $map['provinceId'] = $provinceId;
        } else {
            $sid = M('user')->getField('sid', 'uid=' . $this->mid);
            $locid = M('school')->getField('cityId', 'id=' . $sid);
            $map['provinceId'] = M('citys')->getField('pid', 'id=' . $locid);
        }
        $cityId = intval($_REQUEST['cityId']);
        if ($cityId) {
            $map['cityId'] = $cityId;
        }
        $sid = intval($_REQUEST['sid']);
        if ($sid) {
            $map['sid'] = $sid;
        }
        $sid1 = intval($_REQUEST['sid1']);
        if ($sid1) {
            $map['sid1'] = $sid1;
        }
        $catId = intval($_REQUEST['catId']);
        if ($catId) {
            $map['catId'] = $catId;
        }
        $price = intval($_REQUEST['price']);
        if ($price) {
            $map['price'] = $price;
        }
        $page = intval($_REQUEST['page']);
        if (!$page) {
            $page = 1;
        }
        $count = intval($_REQUEST['count']);
        if (!$count) {
            $count = 10;
        }
        $list = $this->donate->apiDonateList($map, $count, $page);
        if ($list) {
            return $list;
        } else {
            return array();
        }
    }

    public function donate() {
        $id = intval($_REQUEST['id']);
        if ($id) {
            return $this->donate->apiDonate($id);
        }
    }

    public function catList() {
        return M('donate_cat')->findAll();
    }

    public function defaultProvince() {
        $sid = M('user')->getField('sid', 'uid=' . $this->mid);
        $locid = M('school')->getField('cityId', 'id=' . $sid);
        $provincId = M('citys')->getField('pid', 'id=' . $locid);
        return $provincId;
    }

    public function citySchool() {
        $type = $_REQUEST['type'];
        $id = $_REQUEST['id'];
        if ($type == 'province') {
            $list = M('province')->findALl();
        } else if ($type == 'city') {
            $list = M('citys')->where('pid = ' . $id)->field('id,city as title')->findALl();
        } else if ($type == 'sid') {
            $list = M('school')->where('cityId =' . $id)->field('id,title')->findALl();
        } else if ($type == 'sid1') {
            $map['pid'] = $id;
            $list = M('school')->where($map)->field('id,title')->findALl();
        }

        return $list;
    }

    public function payment() {
        $id = intval($_REQUEST['id']);
        $res = D('DonateProduct', 'shop')->buy($this->mid, $id);
        return $res;
    }

    public function insertDonate() {
        $res['status'] = 0;
        //参数合法性检查
        $required_field = array(
            'title' => '物品名称',
            'price' => '物品价格',
            'content' => '物品详情',
            'catId' => '物品分类',
            'contact' => '联系人',
            'mobile' => '联系电话',
        );
        foreach ($required_field as $k => $v) {
            if (empty($_REQUEST[$k]))
                $res['msg'] = $v . '不可为空';
            return $res;
        }
        if (mb_strlen($_REQUEST['title'], 'utf8') > 30) {
            $res['msg'] = '标题必须在30个字内';
            return $res;
        }
        $info = tsUploadImg();
        if ($info['status']) {
            $data['pic'] = $info['info'][0]['savepath'] . $info['info'][0]['savename'];
        } elseif ($info['info'] != '没有选择上传文件') {
            $res['msg'] = $info['info'];
            return $res;
        }
        if (intval($_REQUEST['price']) == 1) {
            $data['price'] = 1;
        } else if (intval($_REQUEST['price']) == 3) {
            $data['price'] = 3;
        } else if (intval($_REQUEST['price']) == 5) {
            $data['price'] = 5;
        }
        $data['title'] = t($_REQUEST['title']);
        $data['content'] = t(h($_REQUEST['content']));
        $data['catId'] = intval($_REQUEST['catId']);
        $data['contact'] = t($_REQUEST['contact']);
        $data['mobile'] = t($_REQUEST['mobile']);
        $school = M('user')->where('uid' . $this->mid)->field('sid,sid1')->find();
        $data['cityId'] = M('school')->getField('cityId', 'id=' . $school['sid']);
        $data['provinceId'] = M('citys')->getField('pid', 'id=' . $data['cityId']);
        $data['sid'] = $school['sid'];
        $data['sid1'] = $school['sid1'];
        $data['imgs'] = serialize($_REQUEST['imgs']);
        $data['uid'] = $this->mid;
        $result = D('DonateProduct' . 'shop')->addProduct($data);
        if ($result) {
            $res['status'] = 1;
            $res['msg'] = '添加成功';
            return $res;
        } else {
            $res['msg'] = '添加失败';
            return $res;
        }
    }

    public function myDonateBuyer() {
        $map['isDel'] = 0;
        $map['buyer'] = $this->mid;
        $page = intval($_REQUEST['page']);
        if (!$page) {
            $page = 1;
        }
        $count = intval($_REQUEST['count']);
        if (!$count) {
            $count = 10;
        }
        $offset = ($page - 1) * $count;
        $list = $this->donate->where($map)->field('id,title,pic,price')->order('id DESC')->limit("$offset,$count")->findAll();
        foreach ($list as $k => $v) {
            $list[$k]['pic'] = tsMakeThumbUp($v['pic'], 60, 60, 'f');
        }
        return $list ? $list : array() ;
    }
    
        public function myDonate() {
        $map['isDel'] = 0;
        $map['uid'] = $this->mid;
        $page = intval($_REQUEST['page']);
        if (!$page) {
            $page = 1;
        }
        $count = intval($_REQUEST['count']);
        if (!$count) {
            $count = 10;
        }
        $offset = ($page - 1) * $count;
        $list = $this->donate->where($map)->field('id,title,pic,price')->order('id DESC')->limit("$offset,$count")->findAll();
        foreach ($list as $k => $v) {
            $list[$k]['pic'] = tsMakeThumbUp($v['pic'], 60, 60, 'f');
        }
        return $list ? $list : array() ;
    }

}

?>
