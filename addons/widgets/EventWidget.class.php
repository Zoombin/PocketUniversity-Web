<?php
/**
 * 校内通知Widget
 *
 */
class EventWidget extends Widget {

	/**
	 * 热门话题Widget
	 *
	 * $data接受的参数:
	 * arrary(
	 *  'limit'(可选)        => $limit,
	 *  'sid'(可选)        => $sid, // 学校id
	 * )
	 *
	 * @see Widget::render()
	 */
	public function render($data) {
            global $ts;
            $map['show_in_xyh'] = 1;
            if($ts['user']['sid']){
                $eventSchool = M('event_school')->where('sid='.$ts['user']['sid'])->findAll();
                $eids = getSubByKey($eventSchool, 'eventId');
                if($eids){
                    $map['id'] = array('in', $eids);
                }
                //口袋大学显示
                if($ts['user']['sid']==473){
                    unset($map['show_in_xyh']);
                }
            }
                $data['title'] = '活动';
		$data['limit'] = empty($data['limit'])?3:$data['limit'];
		$data['mid'] = empty($data['mid'])?0:$data['mid'];
                $map['isDel'] = 0;
                $map['status'] = 1;
                $map['deadline'] = array('gt',time());
                $daoEvent = D('Event','event');
		$list = $daoEvent->getHomeList($map,$data['mid'],$data['limit']);
                //显示羽毛球
//                if(time()<strtotime('2013-10-31')){
//                    if($data['mid']==0){
//                        $ymq = M('event')->where('id=2170')->find();
//                        $ymq['cover'] = tsGetCover($ymq['coverId']);
//                        $ymq['canJoin'] = true;
//                        if(count($list)==$data['limit']){
//                            $del = $data['limit']-1;
//                            unset($list[$del]);
//                        }
//                        $newlist[] = $ymq;
//                        foreach($list as $v){
//                            $newlist[] = $v;
//                        }
//                        $list = $newlist;
//                    }
//                }
                //如果不够，再增加已完成的活动
                $cnt = count($list);
                if($cnt < $data['limit']){
                    unset($map['deadline']);
                    $ids = getSubByKey($list, 'id');
                    if($ids){
                        $map['id'] = array('not in', $ids);
                    }
                    $add = $daoEvent->getHomeList($map,$data['mid'],$data['limit']-$cnt);
                    foreach ($add as $v) {
                        $list[] = $v;
                    }
                }

		$data['list'] = $list;
		$content = $this->renderFile(ADDON_PATH . '/widgets/Event1305.html', $data);
		return $content;
	}
}