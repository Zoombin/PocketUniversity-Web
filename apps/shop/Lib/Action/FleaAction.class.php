<?php

class FleaAction extends Action {

    public function _initialize() {
        $this->setTitle('跳骚市场');
    }

    public function index() {
        $list = M('donate')->where('isDel =0')->findPage(8);
        $this->assign($list);
        $this->display();
    }

}

?>
