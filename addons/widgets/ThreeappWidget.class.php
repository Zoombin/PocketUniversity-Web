<?php

/**
 * 首页3个应用Widget
 *
 */
class ThreeappWidget extends Widget {

    private $allowApps;

    public function __construct() {
        $this->allowApps = array('课件','APP','消费');
    }

    public function render($data) {
        if(!empty($data['installApp'])){
            foreach($data['installApp'] as $value){
                if(in_array($value['app_alias'], $this->allowApps)){
                    $apps[] = $value['app_alias'];
                }
            }
        }else{
            $apps = $this->allowApps;
        }
        $result['list'] = array();
        foreach ($apps as $value) {
            $map = array();
            $data = array();
            $data['title'] = $value;
            switch ($value) {
                case 'APP':
                    $data['more'] = U('appstore/Index/index');
                    $map['is_active'] = 1;
                    $data['list'] = M('appstore_app')->field('id,title,icon as path,readCount,commentCount')->where($map)->limit(6)->order('id DESC')->findAll();
                    foreach ($data['list'] as &$vo){
                        $vo['url'] = U('appstore/Index/app', array('id'=>$vo['id']));
                        if (strlen($vo['path']) > 0) {
                            $vo['path'] = SITE_URL . '/data/uploads/' . $vo['path'];
                        } else {
                            $vo['path'] = SITE_URL . '/apps/appstore/Appinfo/ico_app_large.png';
                        }
                    }
                    break;
                case '课件':
                    $data['more'] = U('document/Index/index');
                    $map['status'] = 1;
                    $map['isDel'] = 0;
                    $data['list'] = M('wenku')->field('id,name as title,extension,userId,commentCount,downloadCount')->where($map)->limit(6)->order('id DESC')->findAll();
                    foreach ($data['list'] as &$vo){
                        $vo['url'] = U('document/Index/doc', array('id'=>$vo['id'], 'uid'=>$vo['userId']));
                        $vo['path'] = '';
                    }
                    break;
                case '消费':
                    $data['more'] = U('coupon/Index/index');
                    $map['isDel'] = 0;
                    $data['list'] = M('coupon')->field('id,description as title,path,readCount')->where($map)->limit(6)->order('id DESC')->findAll();
                    foreach ($data['list'] as &$vo){
                        $vo['url'] = U('coupon/Index/details', array('id'=>$vo['id']));
                        if (strlen($vo['path']) > 0) {
                            $vo['path'] = tsMakeThumbUp('coupon/'.$vo['path'],$width=16,$height=16,$t='f');
                        } else {
                            $vo['path'] = '';
                        }
                    }
                    break;

                default:
                    break;
            }
            $result['list'][] = $data;
        }
        $content = $this->renderFile(ADDON_PATH . '/widgets/Threeapp1305.html', $result);
        return $content;
    }

}