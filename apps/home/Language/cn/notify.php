<?php
return  array(
	/**
	'home_addComment'   => array(
		'title' => '{actor} 在您的'.$app_alias.' <a href="'.$url.'" target="_blank">'.$title.'</a> 发表了评论',
		'body'  => $content.' <div class="right alR mr10"><a href="javascript:void(0);return false;" onclick="">回复</a></div>',
	),
	'home_replyComment'	=> array(
		'title'	=> '{actor}: ' . $content,
		'body'	=> '回复我在'.$app_alias.' <a href="'.$url.'" target="_blank">'.$title.'</a> 的评论: '.$my_content.' <div class="right alR mr10"><a href="javascript:void(0);return false;" onclick="">回复</a></div>',
	),
	**/

	'home_addComment'   => array(
		'title' => '{actor} 在您的'.$app_alias.' <a href="'.$url.'" target="_blank">'.$title.'</a> 发表了评论',
		'body'  => $content,
	),
	'home_replyComment'	=> array(
		'title'	=> '{actor}: ' . $content,
		'body'	=> '回复我在'.$app_alias.' <a href="'.$url.'" target="_blank">'.$title.'</a> 的评论: '.$my_content,
	),
	   'home_delaudit' => array(
        'title' => '您添加的爱心物品 "' . $title . '" 被驳回',
        'other' => '原因,' . $reason,
    ),
        'home_donate_audit' => array(
        'title' => '您添加的爱心物品"' . $title . '"通过了审核',
        'other' => '<a href="' . U('shop/Donate/detail', array('id' => $donateId)) . '" target="_blank">去看看</a>',
    ),
);