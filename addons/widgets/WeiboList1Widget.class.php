<?php

class WeiboList1Widget extends Widget {

    public function render($data) {
        $data['type'] = $data['type'] ? $data['type'] : 'normal';
        $data['num'] = $data['num'] ? $data['num'] : 3;
        $data['insert'] = 1 == $data['insert'] ? $data['insert'] : 0;
        $template = "WeiboList1.html";
        $content = $this->renderFile(ADDON_PATH . '/widgets/' . $template, $data);
        return $content;
    }

}