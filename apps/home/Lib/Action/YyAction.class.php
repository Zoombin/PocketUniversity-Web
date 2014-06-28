<?php

/**
 * 银行后台操作
 *
 * @version $id$
 * @copyright 2013-2015 陆冬云
 * @author 陆冬云 <rechner00@hotmail.com>
 * @license PHP Version 5.3
 */
import('home.Action.PubackAction');

class YyAction extends PubackAction {

    public function index(){
        $this->display();
    }

    public function editYy() {
        $this->assign('type', 'add');
        $id = (int) $_REQUEST['id'];
        if ($id) {
            $this->assign('type', 'edit');
            $map['id'] = $id;
            $obj = M('Yy_product')->where($map)->find();
            if (!$obj) {
                $this->error('无法找到对象');
            }
            $this->assign($obj);
        }
        $this->display();
    }

    public function doEditYy() {
        if(intval($_REQUEST['need_attended'])<=0){
            $this->error('所需参与人数必须大于零的整数');
        }
        $id = intval($_REQUEST['id']);
        if (empty($id)) {
            $this->_insertYy();
        } else {
            $this->_updateYy($id);
        }
    }

    private function _insertYy() {
        $data = $this->_getYyData();
        $data['uid'] = $this->mid;
        $res = D('ShopProduct')->addProduct($data);
        if ($res) {
            $this->assign('jumpUrl', U('/Admin/yg'));
            $this->success('添加成功');
        } else {
            $this->error('添加失败');
        }
    }

    private function _getYyData() {
        $info = tsUploadImg();
        if ($info['status']) {
            $data['pic'] = $info['info'][0]['savepath'].$info['info'][0]['savename'];
        } elseif ($info['info'] != '没有选择上传文件') {
            $this->error($info['info']);
        }
        $data['name'] = t($_REQUEST['name']);
        $data['price'] = t($_REQUEST['price']);
        $data['content'] = t(h($_REQUEST['content']));
        $data['over_times'] = t($_REQUEST['over_times']);
        $data['need_attended'] = t($_REQUEST['need_attended']);
        $data['imgs'] = serialize($_REQUEST['imgs']);
        return $data;
    }

    private function _updateYy($id) {
        $data = $this->_getYyData();
        $data['id'] = $id;
        $res = D('ShopProduct')->updateProduct($data);
        if ($res) {
            $this->assign('jumpUrl', U('/Admin/yg'));
            $this->success('修改成功');
        } else {
            $this->error('修改失败');
        }
    }

}

?>