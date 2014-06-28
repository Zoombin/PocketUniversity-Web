<?php
/*
 * 游客访问的黑/白名单，不需要开放的，可以注释掉
 */
return array(
	"access"	=>	array(
		'home/Widget/renderWidget' 	=> true, // 渲染插件
		'home/User/countNew'		=> true, // 获取最新微博
		'home/Public/*'				=> true, // 登录注册
		'home/Space/*'      		=> true, // 个人空间
		'phptest/*/*'				=> true, // 测试应用
		'api/*/*'					=> true, // Api接口
		'wap/*/*'					=> true, // Wap版
        'w3g/*/*'					=> true, // 3G版
		'admin/*/*'					=> true, // 管理后台的权限由它自己控制
		'home/Square/*'				=> true, // 微博广场的权限由管理后台控制
		'home/User/topics'			=> true, // 话题列表
		'home/Widget/addonsRequest' => true, // 便于匿名下的钩子异步
		'home/Widget/weiboShow'		=> true, // 微博秀
		'home/Widget/share'			=> true, // 站外分享

		'blog/Index/news'			=> true, // 最新博客
		'blog/Index/show'			=> true, // 博客内容
		'blog/Index/personal'		=> true, // 个人博客

		'photo/Index/photo'			=> true, // 照片展示
		'photo/Index/album'			=> true, // 相册展示
		'photo/Index/photos'		=> true, // 所有照片

		'group/Index/index'		=> true, // 群组首页
		'group/Index/newIndex'		=> true, // 群组首页
		'group/Index/search'		=> true, // 分类列表
		'group/Group/index'			=> true, // 单群首页

		'document/Index/index'		=> true, // 文库首页
		'appstore/Index/index'		=> true, // 应用首页
		'event/Index/index'		=> true, // 活动首页
		'coupon/Index/index'		=> true, // 消费首页
		'newcomer/Index/*'		=> true, // 迎新首页
                'show/*/*'                        =>true, //独show新动力
                'hold/*/*'                        =>true, //hold住我的麦克风
                'lib/*/*'                        =>true, //图书馆
                'event/Front/*'                        =>true, //活动频道页
                'home/Comment/getComment'       =>true,
                'weibo/Operate/syncWeibo'       =>true, //自动更新绑定微博
                'event/School/*'       =>true, //
                'event/Lesson/board'       =>true, //
                'event/Lesson/boardActive'       =>true, //
                'event/LessonMember/*'       =>true, //
                'event/LessonActiveMember/*'       =>true, //
                'event/CourseAdmin/*'       =>true, //
                'event/GroupIndex/*'       =>true, //
                'event/GroupTopic/*'       =>true, //
                'event/GroupMember/*'       =>true, //
                'event/GroupDir/*'       =>true, //
                'event/GroupEvent/*'       =>true, //
                'event/GroupRule/*'       =>true, //
                'event/Announce/*'       =>true, //
                'event/Sjsq/*'       =>true, //
                'travel/*/*'       =>true, //
                'announce/Index/notice'       =>true, //
                'announce/Index/noticeDetail'       =>true, //
                'event/Index/sj'		=> true, // 十佳首页
                'shop/Index/index'		=> true, //
                'home/Puback/*'		=> true, // 管理后台的权限由它自己控制
                'home/Ad/*'		=> true, // 管理后台的权限由它自己控制
                'shop/Admin/*'		=> true, // 管理后台的权限由它自己控制
                'travel/Admin/*'		=> true, // 管理后台的权限由它自己控制
                'announce/Admin/*'		=> true, // 管理后台的权限由它自己控制
                'appstore/Admin/*'		=> true, // 管理后台的权限由它自己控制
                'document/Admin/*'		=> true, // 管理后台的权限由它自己控制
                'coupon/Admin/*'		=> true, // 管理后台的权限由它自己控制
                'weibo/Admin/*'		=> true, // 管理后台的权限由它自己控制
                'home/Ditu/*'		=> true, // 管理后台的权限由它自己控制
	)
);