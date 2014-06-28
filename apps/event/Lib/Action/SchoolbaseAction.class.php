<?php

/**
 * SchoolbaseAction
 * 校方活动前台抽象类
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2013-2015 陆冬云
 * @author 陆冬云 <rechner00@hotmail.com>
 * @license PHP Version 5.3
 */
class SchoolbaseAction extends Action {

    protected $appName;
    protected $event;
    protected $school;
    protected $sid;
    protected $smid = 0;
    protected $rights;
    //菁英人才外部版
    protected $jyrcOut = false;

    /**
     * __initialize
     * 初始化
     * @access public
     * @return void
     */
    public function _initialize() {
        // 提示消息不显示头部
        $this->assign('isAdmin', '1');
        $domain = parse_url($_SERVER['SERVER_NAME']);
        $map['domain'] = substr($domain['path'], 0, strpos($domain['path'], '.'));
        $map['eTime'] = array('gt', 0);
//        var_dump($map);die;
        $school = M('school')->where($map)->find();
        $hostNeedle = get_host_needle();
        if (!$school) {
            $this->assign('jumpUrl', 'http://'.$hostNeedle);
            $this->error('此学校尚未开通校方活动！');
        }
        $this->school = $school;
        $this->sid = $school['id'];
        $this->assign('school', $school);
        //站点信息
        $config = D('SchoolWeb','event')->getConfigCache($this->sid);
        $this->assign('webconfig', $config);
        //用户信息
        if($this->mid){
            $groups = M('user_group_link')->where('uid='.$this->mid)->field('user_group_id')->findAll();
            $gids = getSubByKey($groups, 'user_group_id');
            $this->rights['allAdmin'] = in_array(C('SADMIN'), $gids);
            //$this->rights['allLook'] = in_array(C('SLOOK'), $gids);
        }
        if ($this->user['sid'] == $this->sid || $this->rights['allAdmin']) {
            $this->smid = $this->mid;
            //是否可以进入后台
            if ($this->user['can_event'] ||$this->user['can_event2'] ||$this->user['can_gift']
                    ||$this->user['can_print']||$this->user['can_group']||$this->user['can_admin']||$this->user['can_announce']
                    ||$this->user['can_prov_event']||$this->user['can_prov_news']||$this->user['can_prov_work']
                    ||$this->user['event_level'] != 20||$this->rights['allAdmin']) {
                $this->assign('open_admin', 1);
            }
            if($this->rights['allAdmin'] || $this->user['can_admin']){
                $this->rights['can_admin'] = 1;
            }
        }
        $this->assign('smid', $this->smid);
        //应用名称
        $this->appName = '活动';
        //设置活动的数据处理层
        $this->event = D('Event');
        // 活动分类
        $cate = D('EventType')->getType();
        $this->assign('category', $cate);
        //幻灯
        $this->assign('slide', $this->event->getSlide($this->sid));
        //菁英人才 并且 未登录前 或 登录用户不是菁英
        if($this->sid == 505 && !$this->smid){
            $this->jyrcOut = true;
        }
        $this->assign('jyrcOut', $this->jyrcOut);
        $this->assign('eventPage', 'event');
    }
}
