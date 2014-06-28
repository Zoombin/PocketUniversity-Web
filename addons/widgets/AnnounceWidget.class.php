<?php
/**
 * 校内通知Widget
 *
 */
class AnnounceWidget extends Widget {

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
                $data['title'] = '校内通知';
		$data['limit'] = empty($data['limit'])?5:$data['limit'];
                $map['isDel'] = 0;
                if(isset($data['sid'])){
                    $map['sid'] = (int)$data['sid'];
                }
		$data['list'] = M('announce')->where($map)->order('id DESC')->limit($data['limit'])->findAll();
		$content = $this->renderFile(ADDON_PATH . '/widgets/Announce.html', $data);
		return $content;
	}
}