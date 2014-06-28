DROP TABLE IF EXISTS `ts_survey`;
CREATE TABLE `ts_survey` (
`id`  int(11) NOT NULL AUTO_INCREMENT ,
`uid`  int(11) NOT NULL COMMENT '作者ID' ,
`sid`  int(11) NOT NULL,
`title`  text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '标题' ,
`explain`  text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '介绍' ,
`deadline`  int(11) NOT NULL COMMENT '截至时间' ,
`cTime`  int(11) NULL DEFAULT NULL COMMENT '创建时间',
`rTime`  int(11) NOT NULL COMMENT '访问时间' ,
`status`  tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '0草稿1完成2删除',
`vote_num`  int(11) NOT NULL DEFAULT 0 COMMENT '投票人数' ,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_survey_vote`;
CREATE TABLE `ts_survey_vote` (
  `id` int(11) NOT NULL auto_increment,
  `suid`  int(11) NOT NULL COMMENT '问卷ID' ,
  `title` text NOT NULL,
  `type`  tinyint(1) unsigned NOT NULL COMMENT '型类(0单选、1多选)' ,
  `glimit`  tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '最多可以投几项' ,
  `display_order` int(11) NOT NULL default '0' COMMENT '显示顺序' ,
  `isDel`  tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '0 1删除',
  PRIMARY KEY  (`id`),
  KEY `display_order` (`display_order`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_survey_opt`;
CREATE TABLE `ts_survey_opt` (
  `id` int(11) NOT NULL auto_increment,
  `vote_id` int(11) NOT NULL,
  `name` text NOT NULL COMMENT '选项标题',
  `num` int(11) NOT NULL default '0' COMMENT '选项被投次数',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_survey_user`;
CREATE TABLE `ts_survey_user` (
  `id` int(11) NOT NULL auto_increment,
  `survey_id` int(11) NOT NULL,
  `uid` int(11) NOT NULL default '0',
  `cTime` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_survey_user_opt`;
CREATE TABLE `ts_survey_user_opt` (
  `id` int(11) NOT NULL auto_increment,
  `suser_id` int(11) NOT NULL,
  `opt_id` int(11) NOT NULL COMMENT '用户所投选项',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;