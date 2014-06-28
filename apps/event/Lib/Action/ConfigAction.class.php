<?php

/**
 * JfAction
 * 积分商城管理
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2013-2015 陆冬云
 * @author 陆冬云 <rechner00@hotmail.com>
 * @license PHP Version 5.3
 */
class ConfigAction extends TeacherAction {

    public function _initialize() {
        //管理权限判定
        parent::_initialize();
        if ($this->rights['can_admin'] != 1) {
            $this->assign('jumpUrl', U('event/Readme/index'));
            $this->error('您没有权限管理全局配置！');
        }
    }

    public function webconfig() {
        $config = D('SchoolWeb','event')->getConfigCache($this->school['id']);
        $this->assign($config);
        $this->display();
    }

    public function orga() {
        $this->assign('list', D('SchoolOrga')->getAll($this->school['id']));
        $this->display();
    }

    public function editOrga() {
        $id = intval($_GET['id']);
        if ($id) {
            $orga = D('SchoolOrga')->where("id={$id} AND sid={$this->school['id']}")->find();
            $this->assign($orga);
        }
        $this->display();
    }

    public function doAddOrga() {
        $isnull = preg_replace("/[ ]+/si", "", t($_POST['title']));
        $type = D('SchoolOrga');
        if (empty($isnull)) {
            echo -2;
        }
        $map['sid'] = $this->school['id'];
        $map['title'] = $isnull;
        $title = $type->where($map)->getField('title');
        $cat = intval($_POST['cat'])?intval($_POST['cat']):1;
        if ($title !== null) {
            echo 0;
        } else {
            if ($type->addOrga($this->school['id'], $isnull,$cat)) {
                echo 1;
            } else {
                echo -1;
            }
        }
    }

    public function doEditOrga() {
        $_POST['id'] = intval($_POST['id']);
        $_POST['title'] = preg_replace("/[ ]+/si", "", t($_POST['title']));
        if (empty($_POST['title'])) {
            echo -2;
        }
        $type = D('SchoolOrga');
        $map['title'] = $_POST['title'];
        $map['sid'] = $this->sid;
        $map['id'] = array('neq', $_POST['id']);
        $title = $type->where($map)->getField('title');
        if ($title !== null) {
            echo 0; //分类名称重复
        } else {
            if ($type->editOrga($this->school['id'], $_POST['id'], $_POST['title'],intval($_POST['cat']))) {
                echo 1; //更新成功
            } else {
                echo -1;
            }
        }
    }

    public function doDeleteOrga() {
        $type = D('SchoolOrga');
        if ($result = $type->deleteOrga($this->school['id'], t($_POST['id']))) {
            if (!strpos($_POST['id'], ",")) {
                echo 2;            //说明只是删除一个
            } else {
                echo 1;            //删除多个
            }
        } else {
            echo $result;
        }
    }

    public function doOrgaOrder() {
        $_POST['id'] = intval($_POST['id']);
        $_POST['baseid'] = intval($_POST['baseid']);
        if ($result = D('SchoolOrga')->changeOrder($this->school['id'], $_POST['id'], $_POST['baseid'])) {
            echo 1; //更新成功
        } else {
            echo 0;
        }
    }

    public function doWebconfig() {
        if (empty($_POST)) {
            $this->error('参数错误');
        }
        //得到上传的图片
        if (!empty($_FILES['banner_logo']['name'])) {
            $logo_options['save_to_db'] = false;
            $logo_options['max_size'] = getPhotoConfig('photo_max_size');
            $logo_options['allow_exts'] = getPhotoConfig('photo_file_ext');
            $logo = X('Xattach')->upload('site_logo', $logo_options);
            if ($logo['status']) {
                $logofile = '/data/uploads/' . $logo['info'][0]['savepath'] . $logo['info'][0]['savename'];
            }elseif($logo['info'] != '没有选择上传文件'){
                $this->error($logo['info']);
            }
            $data['path'] = $logofile;
        }
        $data['title'] = t($_POST['title']);
        $data['cTime'] = time();
        $data['sid'] = $this->school['id'];
        $data['cradit_name'] = t($_POST['cradit_name']);
        $res = D('SchoolWeb','event')->editSchoolWeb($data);
        if ($res) {
            $this->assign('jumpUrl', U('event/Config/webconfig'));
            $this->success('保存成功');
        } else {
            $this->error('保存失败');
        }
    }

    public function printconfig() {
        $config = D('SchoolWeb','event')->getConfigCache($this->school['id']);
        $this->assign($config);
        $this->display();
    }

    public function doPrintconfig() {
        if (empty($_POST)) {
            $this->error('参数错误');
        }
        $data['print_title'] = t($_POST['title']);
        $data['print_content'] = t(h($_POST['content']));
        $data['print_day'] = intval($_POST['print_day']);
        $data['print_address'] = t($_POST['print_address']);
        $data['sid'] = $this->school['id'];
        $res = D('SchoolWeb','event')->editSchoolWeb($data);
        if ($res) {
            $this->assign('jumpUrl', U('event/Config/printconfig'));
            $this->success('保存成功');
        } else {
            $this->error('保存失败');
        }
    }

    public function cx() {
        $config = D('SchoolWeb','event')->getConfigCache($this->school['id']);
        $this->assign($config);
        $this->display();
    }


    public function doCx() {
        $required_field = array(
            'cxjg' => '警告次数',
            'cxjy' => '禁活动次数',
            'cxday' => '禁活动天数'
        );
        foreach ($required_field as $k => $v) {
            if (empty($_POST[$k]))
                $this->error($v . '不可为空');
        }
        $data['cxjg'] = intval($_POST['cxjg']);
        $data['cxjy'] = intval(h($_POST['cxjy']));
        $data['cxday'] = intval($_POST['cxday']);
        if($data['cxjg']<1 || $data['cxjg']>30){
            $this->error('警告次数 1-30');
        }
        if($data['cxjy']<2 || $data['cxjy']>50){
            $this->error('禁活动次数 2-50');
        }
        if($data['cxjy'] <= $data['cxjg']){
            $this->error('禁活动次数 必须大于 警告次数');
        }
        if($data['cxday']<1 || $data['cxday']>100){
            $this->error('禁活动天数 1-100');
        }
        $data['sid'] = $this->school['id'];
        $res = D('SchoolWeb','event')->editSchoolWeb($data);
        if ($res) {
            $this->assign('jumpUrl', U('event/Config/cx'));
            $this->success('保存成功');
        } else {
            $this->error('保存失败');
        }
    }
}
