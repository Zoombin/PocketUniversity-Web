<?php
class AdminCreditAction extends TeacherAction {


    public function _initialize() {
        parent::_initialize();
        if(!$this->rights['can_credit'])
            $this->error('您没有权限');
    }

    public function index() {
        if(!$this->rights['can_admin'])
            $this->error('您没有权限');
        $list = D('EcType')->getEcType($this->sid);
        $this->assign('totalRows', count($list));
        $this->assign('data', $list);
        $this->display();
    }

    public function editEcType() {
        
        if(!$this->rights['can_admin'])
            $this->error('您没有权限');
        $this->assign('type', 'add');
        $id = (int) $_REQUEST['id'];
        if ($id) {
            $this->assign('type', 'edit');
            $map['id'] = $id;
            $obj = M('ec_type')->where($map)->find();
            if (!$obj) {
                $this->error('无法找到对象');
            }
            $this->assign($obj);
        }
        $this->display();
    }

}

?>