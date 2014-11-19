
DROP TABLE IF EXISTS `#iCMS@__app`;

CREATE TABLE `#iCMS@__app` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `title` varchar(100) NOT NULL DEFAULT '',
  `table` varchar(100) NOT NULL DEFAULT '',
  `field` text NOT NULL,
  `binding` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `description` varchar(200) NOT NULL DEFAULT '',
  `position` varchar(10) NOT NULL DEFAULT '',
  `position2` varchar(10) NOT NULL DEFAULT '',
  `form` text NOT NULL,
  `show` tinyint(1) NOT NULL DEFAULT '0',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#iCMS@__article`;

CREATE TABLE `#iCMS@__article` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '栏目id',
  `scid` varchar(255) NOT NULL DEFAULT '' COMMENT '副栏目',
  `ucid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户分类',
  `pid` varchar(255) NOT NULL DEFAULT '' COMMENT '属性',
  `ordernum` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `stitle` varchar(255) NOT NULL DEFAULT '' COMMENT '短标题',
  `clink` varchar(255) NOT NULL DEFAULT '' COMMENT '自定义链接',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '外部链接',
  `source` varchar(255) NOT NULL DEFAULT '' COMMENT '出处',
  `author` varchar(255) NOT NULL DEFAULT '' COMMENT '作者',
  `editor` varchar(255) NOT NULL DEFAULT '' COMMENT '编辑',
  `userid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `haspic` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否有缩略图',
  `pic` varchar(255) NOT NULL DEFAULT '' COMMENT '缩略图',
  `mpic` varchar(255) NOT NULL DEFAULT '' COMMENT '缩略图2',
  `spic` varchar(255) NOT NULL DEFAULT '' COMMENT '缩略图3',
  `picdata` varchar(255) NOT NULL DEFAULT '' COMMENT '图片数据',
  `keywords` varchar(255) NOT NULL DEFAULT '' COMMENT '关键词',
  `tags` varchar(255) NOT NULL DEFAULT '' COMMENT '标签',
  `description` varchar(5120) NOT NULL DEFAULT '' COMMENT '摘要',
  `related` text NOT NULL COMMENT '相关',
  `metadata` text NOT NULL COMMENT '扩展',
  `pubdate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发布时间',
  `postime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '提交时间',
  `tpl` varchar(255) NOT NULL DEFAULT '' COMMENT '模板',
  `hits` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '总点击数',
  `hits_today` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '当天点击数',
  `hits_yday` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '昨天点击数',
  `hits_week` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '周点击',
  `hits_month` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '月点击',
  `favorite` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收藏数',
  `comments` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论数',
  `good` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '顶',
  `bad` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '踩',
  `creative` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '文章类型 1原创 0转载',
  `chapter` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '章节',
  `weight` smallint(6) NOT NULL DEFAULT '0' COMMENT '权重',
  `mobile` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1手机发布 0 pc',
  `postype` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '类型 0用户 1管理员',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '[[0:草稿],[1:正常],[2:回收],[3:审核],[4:不合格]]',
  PRIMARY KEY (`id`),
  KEY `id` (`status`,`id`),
  KEY `hits` (`status`,`hits`),
  KEY `pubdate` (`status`,`pubdate`),
  KEY `cid` (`status`,`cid`),
  KEY `hits_week` (`status`,`hits_week`),
  KEY `hits_month` (`status`,`hits_month`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#iCMS@__article_data`;

CREATE TABLE `#iCMS@__article_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `aid` int(10) unsigned NOT NULL DEFAULT '0',
  `subtitle` varchar(255) NOT NULL DEFAULT '',
  `body` mediumtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `aid` (`aid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#iCMS@__category`;

CREATE TABLE `#iCMS@__category` (
  `cid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rootid` int(10) unsigned NOT NULL DEFAULT '0',
  `pid` varchar(255) NOT NULL DEFAULT '',
  `appid` smallint(6) unsigned NOT NULL DEFAULT '0',
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `creator` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `subname` varchar(255) NOT NULL DEFAULT '',
  `ordernum` smallint(6) unsigned NOT NULL DEFAULT '0',
  `password` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `keywords` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `dir` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `pic` varchar(255) NOT NULL DEFAULT '',
  `mpic` varchar(255) NOT NULL DEFAULT '',
  `spic` varchar(255) NOT NULL DEFAULT '',
  `count` int(10) unsigned NOT NULL DEFAULT '0',
  `mode` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `domain` varchar(255) NOT NULL DEFAULT '',
  `htmlext` varchar(10) NOT NULL DEFAULT '',
  `categoryURI` varchar(255) NOT NULL DEFAULT '',
  `categoryRule` varchar(255) NOT NULL DEFAULT '',
  `contentRule` varchar(255) NOT NULL DEFAULT '',
  `urlRule` varchar(255) NOT NULL DEFAULT '',
  `indexTPL` varchar(255) NOT NULL DEFAULT '',
  `listTPL` varchar(255) NOT NULL DEFAULT '',
  `contentTPL` varchar(255) NOT NULL DEFAULT '',
  `metadata` mediumtext NOT NULL,
  `contentprop` mediumtext NOT NULL,
  `hasbody` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `comments` int(10) unsigned NOT NULL DEFAULT '0',
  `isexamine` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `issend` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `isucshow` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`cid`),
  KEY `dir` (`dir`),
  KEY `s_o_cid` (`status`,`ordernum`,`cid`),
  KEY `t_o_cid` (`appid`,`ordernum`,`cid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#iCMS@__category_map`;

CREATE TABLE `#iCMS@__category_map` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `node` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'category cid',
  `iid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容ID',
  `appid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'iCMS.define.php',
  PRIMARY KEY (`id`),
  KEY `idx` (`appid`,`node`,`iid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#iCMS@__comment`;

CREATE TABLE `#iCMS@__comment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `appid` int(10) unsigned NOT NULL DEFAULT '0',
  `cid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '被评论内容分类',
  `iid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '被评论内容ID',
  `suid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '被评论内容用户ID',
  `title` varchar(255) NOT NULL DEFAULT '',
  `userid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论者ID',
  `username` varchar(255) NOT NULL DEFAULT '' COMMENT '评论者',
  `content` text NOT NULL,
  `reply_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回复 评论ID',
  `reply_uid` int(11) unsigned NOT NULL DEFAULT '0',
  `reply_name` varchar(255) NOT NULL DEFAULT '',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0',
  `up` int(10) unsigned NOT NULL DEFAULT '0',
  `down` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(20) NOT NULL DEFAULT '',
  `quote` int(10) unsigned NOT NULL DEFAULT '0',
  `floor` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_iid` (`appid`,`status`,`iid`,`id`),
  KEY `idx_uid` (`status`,`userid`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#iCMS@__config`;

CREATE TABLE `#iCMS@__config` (
  `tid` smallint(6) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `value` mediumtext NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#iCMS@__favorite`;

CREATE TABLE `#iCMS@__favorite` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `nickname` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `follow` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关注数',
  `count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收藏数',
  `mode` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1 公开 0私密',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#iCMS@__favorite_data`;

CREATE TABLE `#iCMS@__favorite_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收藏者ID',
  `appid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '应用ID',
  `fid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收藏夹ID',
  `iid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容ID',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '内容URL',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '内容标题',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx` (`uid`,`fid`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#iCMS@__favorite_follow`;

CREATE TABLE `#iCMS@__favorite_follow` (
  `fid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '收藏夹ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '关注者',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '收藏夹标题',
  `uid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '关注者ID',
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#iCMS@__filedata`;

CREATE TABLE `#iCMS@__filedata` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `indexid` int(10) unsigned NOT NULL DEFAULT '0',
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `filename` varchar(255) NOT NULL DEFAULT '',
  `ofilename` varchar(255) NOT NULL DEFAULT '',
  `path` varchar(255) NOT NULL DEFAULT '',
  `intro` varchar(255) NOT NULL DEFAULT '',
  `ext` varchar(10) NOT NULL DEFAULT '',
  `size` int(10) unsigned NOT NULL DEFAULT '0',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ext` (`ext`),
  KEY `path` (`path`),
  KEY `ofilename` (`ofilename`),
  KEY `indexid` (`indexid`),
  KEY `fn_userid` (`filename`,`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#iCMS@__group`;

CREATE TABLE `#iCMS@__group` (
  `gid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `ordernum` smallint(5) unsigned NOT NULL DEFAULT '0',
  `power` mediumtext NOT NULL,
  `cpower` mediumtext NOT NULL,
  `type` enum('1','0') NOT NULL DEFAULT '0',
  PRIMARY KEY (`gid`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#iCMS@__keywords`;

CREATE TABLE `#iCMS@__keywords` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `keyword` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `times` smallint(6) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`keyword`),
  UNIQUE KEY `keyword` (`keyword`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#iCMS@__links`;

CREATE TABLE `#iCMS@__links` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `logo` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `desc` text NOT NULL,
  `ordernum` smallint(5) NOT NULL DEFAULT '0',
  UNIQUE KEY `id` (`id`),
  KEY `s_o_id` (`cid`,`ordernum`,`id`),
  KEY `ordernum` (`ordernum`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#iCMS@__members`;

CREATE TABLE `#iCMS@__members` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `username` varchar(50) NOT NULL DEFAULT '',
  `password` varchar(32) NOT NULL DEFAULT '',
  `nickname` varchar(255) NOT NULL DEFAULT '',
  `realname` varchar(255) NOT NULL DEFAULT '',
  `gender` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `info` mediumtext NOT NULL,
  `power` mediumtext NOT NULL,
  `cpower` mediumtext NOT NULL,
  `regtime` int(10) unsigned DEFAULT '0',
  `lastip` varchar(15) NOT NULL DEFAULT '',
  `lastlogintime` int(10) unsigned NOT NULL DEFAULT '0',
  `logintimes` smallint(5) unsigned NOT NULL DEFAULT '0',
  `post` int(10) unsigned NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `username` (`username`),
  KEY `groupid` (`gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#iCMS@__menu`;

CREATE TABLE `#iCMS@__menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rootid` int(10) unsigned NOT NULL DEFAULT '0',
  `ordernum` int(10) unsigned NOT NULL DEFAULT '0',
  `app` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `href` varchar(255) NOT NULL DEFAULT '',
  `icon` varchar(255) NOT NULL DEFAULT '',
  `class` varchar(255) NOT NULL DEFAULT '' COMMENT 'li',
  `a_class` varchar(255) NOT NULL DEFAULT '' COMMENT 'a',
  `target` varchar(255) NOT NULL DEFAULT '',
  `caret` varchar(255) NOT NULL DEFAULT '',
  `data-toggle` varchar(255) NOT NULL DEFAULT '',
  `data-meta` varchar(255) DEFAULT '',
  `data-target` varchar(255) DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `order_id` (`ordernum`,`id`),
  KEY `rootid` (`rootid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#iCMS@__message`;

CREATE TABLE `#iCMS@__message` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发送者ID',
  `friend` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '接收者ID',
  `send_uid` int(10) DEFAULT '0' COMMENT '发送者ID',
  `send_name` varchar(255) NOT NULL DEFAULT '' COMMENT '发送者名称',
  `receiv_uid` int(10) DEFAULT '0' COMMENT '接收者ID',
  `receiv_name` varchar(255) NOT NULL DEFAULT '' COMMENT '接收者名称',
  `content` text NOT NULL COMMENT '内容',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '信息类型',
  `sendtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发送时间',
  `readtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '读取时间',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '信息状态 参考程序注释',
  PRIMARY KEY (`id`),
  KEY `idx` (`status`,`userid`,`friend`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#iCMS@__prop`;

CREATE TABLE `#iCMS@__prop` (
  `pid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rootid` int(10) unsigned NOT NULL,
  `cid` int(10) unsigned NOT NULL DEFAULT '0',
  `field` varchar(255) NOT NULL DEFAULT '',
  `type` varchar(255) NOT NULL DEFAULT '',
  `ordernum` smallint(6) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `val` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`pid`),
  KEY `field` (`field`),
  KEY `cid` (`cid`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#iCMS@__prop_map`;

CREATE TABLE `#iCMS@__prop_map` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `node` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'prop id',
  `iid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容ID',
  `appid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'iCMS.define.php',
  PRIMARY KEY (`id`),
  KEY `idx` (`appid`,`node`,`iid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#iCMS@__push`;

CREATE TABLE `#iCMS@__push` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ordernum` smallint(5) NOT NULL DEFAULT '0',
  `cid` int(10) unsigned NOT NULL DEFAULT '0',
  `rootid` int(10) unsigned NOT NULL DEFAULT '0',
  `pid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `haspic` tinyint(1) NOT NULL DEFAULT '0',
  `editor` varchar(100) NOT NULL DEFAULT '',
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `pic` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `title2` varchar(255) NOT NULL DEFAULT '',
  `pic2` varchar(255) NOT NULL DEFAULT '',
  `url2` varchar(255) NOT NULL DEFAULT '',
  `description2` text NOT NULL,
  `title3` varchar(255) NOT NULL DEFAULT '',
  `pic3` varchar(255) NOT NULL DEFAULT '',
  `url3` varchar(255) NOT NULL DEFAULT '',
  `description3` text NOT NULL,
  `metadata` mediumtext NOT NULL,
  `addtime` int(10) unsigned NOT NULL DEFAULT '0',
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `pid_order` (`pid`,`ordernum`),
  KEY `pid_id` (`pid`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#iCMS@__search_log`;

CREATE TABLE `#iCMS@__search_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `search` varchar(200) NOT NULL DEFAULT '',
  `times` int(10) unsigned NOT NULL DEFAULT '0',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `search_times` (`search`,`times`),
  KEY `search_id` (`search`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#iCMS@__spider_post`;

CREATE TABLE `#iCMS@__spider_post` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `app` varchar(255) NOT NULL DEFAULT '',
  `post` text NOT NULL,
  `fun` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#iCMS@__spider_project`;

CREATE TABLE `#iCMS@__spider_project` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `urls` text,
  `list_url` varchar(255) DEFAULT NULL,
  `cid` int(10) unsigned DEFAULT NULL,
  `rid` int(10) unsigned DEFAULT NULL,
  `poid` int(10) unsigned DEFAULT NULL,
  `sleep` int(10) unsigned DEFAULT NULL,
  `auto` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#iCMS@__spider_rule`;

CREATE TABLE `#iCMS@__spider_rule` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `rule` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#iCMS@__spider_url`;

CREATE TABLE `#iCMS@__spider_url` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cid` int(10) unsigned NOT NULL,
  `pid` int(10) unsigned NOT NULL,
  `rid` int(10) unsigned NOT NULL,
  `indexid` int(10) NOT NULL,
  `hash` char(32) NOT NULL,
  `title` varchar(255) NOT NULL,
  `url` varchar(500) NOT NULL,
  `publish` tinyint(1) NOT NULL,
  `addtime` int(10) NOT NULL,
  `pubdate` int(10) NOT NULL,
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `hash` (`hash`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#iCMS@__tags`;

CREATE TABLE `#iCMS@__tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `cid` int(10) unsigned NOT NULL DEFAULT '0',
  `tcid` varchar(255) NOT NULL DEFAULT '',
  `pid` varchar(255) NOT NULL DEFAULT '',
  `tkey` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `seotitle` varchar(255) NOT NULL DEFAULT '',
  `subtitle` varchar(255) NOT NULL DEFAULT '',
  `keywords` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `metadata` text NOT NULL,
  `haspic` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `pic` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `related` varchar(255) NOT NULL DEFAULT '',
  `comments` int(10) unsigned NOT NULL DEFAULT '0',
  `count` int(10) unsigned NOT NULL DEFAULT '0',
  `weight` smallint(6) NOT NULL DEFAULT '0',
  `tpl` varchar(255) NOT NULL DEFAULT '',
  `ordernum` smallint(5) NOT NULL DEFAULT '0',
  `pubdate` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`,`id`),
  KEY `idx_order` (`status`,`ordernum`),
  KEY `name` (`name`),
  KEY `tkey` (`tkey`),
  KEY `idx_count` (`status`,`count`),
  KEY `pid_count` (`pid`,`count`),
  KEY `cid_count` (`cid`,`count`),
  KEY `pid_id` (`pid`,`id`),
  KEY `cid_id` (`cid`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#iCMS@__tags_map`;

CREATE TABLE `#iCMS@__tags_map` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `node` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '标签ID',
  `iid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容ID',
  `appid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '应用ID',
  PRIMARY KEY (`id`),
  KEY `tid_index` (`appid`,`node`,`iid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#iCMS@__user`;

CREATE TABLE `#iCMS@__user` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gid` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '用户组ID',
  `pid` varchar(255) NOT NULL DEFAULT '' COMMENT '属性ID',
  `username` varchar(255) NOT NULL DEFAULT '' COMMENT '用户名/email',
  `nickname` varchar(50) NOT NULL DEFAULT '' COMMENT '昵称',
  `password` char(32) NOT NULL DEFAULT '' COMMENT '密码',
  `gender` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '性别',
  `fans` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '粉丝数',
  `follow` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关注数',
  `comments` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论数',
  `article` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文章数',
  `share` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分享数',
  `credit` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '积分',
  `regip` varchar(20) NOT NULL DEFAULT '' COMMENT '注册IP',
  `regdate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '注册日期',
  `lastloginip` varchar(20) NOT NULL DEFAULT '' COMMENT '最后登陆IP',
  `lastlogintime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后登陆时间',
  `hits` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '总点击数',
  `hits_today` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '当天点击数',
  `hits_yday` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '昨天点击数',
  `hits_week` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '周点击',
  `hits_month` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '月点击',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '用户类型',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '账号状态',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `username` (`nickname`),
  KEY `email` (`username`),
  KEY `nickname` (`nickname`)
) ENGINE=MyISAM AUTO_INCREMENT=10000 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#iCMS@__user_category`;

CREATE TABLE `#iCMS@__user_category` (
  `cid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `count` int(10) unsigned NOT NULL DEFAULT '0',
  `mode` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1 公开 2私密',
  `appid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`cid`),
  KEY `uid` (`uid`,`appid`,`cid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#iCMS@__user_data`;

CREATE TABLE `#iCMS@__user_data` (
  `uid` int(11) unsigned NOT NULL,
  `realname` varchar(255) NOT NULL DEFAULT '' COMMENT '真实姓名',
  `mobile` varchar(255) NOT NULL DEFAULT '' COMMENT '联系电话',
  `enterprise` varchar(255) NOT NULL DEFAULT '' COMMENT '现有的地址',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '街道地址',
  `weibo` varchar(255) NOT NULL DEFAULT '' COMMENT '个人博客',
  `province` varchar(255) NOT NULL DEFAULT '' COMMENT '省份',
  `city` varchar(255) NOT NULL DEFAULT '' COMMENT '城市',
  `year` varchar(255) NOT NULL DEFAULT '' COMMENT '生日-年',
  `month` varchar(255) NOT NULL DEFAULT '' COMMENT '生日-月',
  `day` varchar(255) NOT NULL DEFAULT '' COMMENT '生日-日',
  `constellation` varchar(255) NOT NULL DEFAULT '' COMMENT '星座',
  `profession` varchar(255) NOT NULL DEFAULT '' COMMENT '职业',
  `isSeeFigure` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '身材信息',
  `height` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '身高',
  `weight` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '体重',
  `bwhB` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '胸围',
  `bwhW` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '腰围',
  `bwhH` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '臀围',
  `pskin` varchar(255) NOT NULL DEFAULT '' COMMENT '肤质',
  `phair` varchar(255) NOT NULL DEFAULT '' COMMENT '发质',
  `shoesize` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '鞋码尺寸',
  `personstyle` varchar(255) NOT NULL DEFAULT '' COMMENT '个人标签',
  `slogan` varchar(512) NOT NULL DEFAULT '' COMMENT '自我介绍',
  `unickEdit` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '昵称修改次数',
  `coverpic` varchar(255) NOT NULL DEFAULT '' COMMENT '自定义封面',
  `tb_nick` varchar(255) NOT NULL DEFAULT '' COMMENT '淘宝用户名',
  `tb_buyer_credit` varchar(255) NOT NULL DEFAULT '' COMMENT '买家信用',
  `tb_seller_credit` varchar(255) NOT NULL DEFAULT '' COMMENT '卖家信用',
  `tb_type` varchar(255) NOT NULL DEFAULT '' COMMENT '淘宝用户类型',
  `is_golden_seller` varchar(255) NOT NULL DEFAULT '' COMMENT '是否金牌卖家',
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#iCMS@__user_follow`;

CREATE TABLE `#iCMS@__user_follow` (
  `uid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '关注者ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '关注者',
  `fuid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '被关注者ID',
  `fname` varchar(255) NOT NULL DEFAULT '' COMMENT '被关注者',
  KEY `uid` (`uid`,`fuid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#iCMS@__user_openid`;

CREATE TABLE `#iCMS@__user_openid` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `openid` varchar(255) NOT NULL,
  `platform` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1:wx,2:qq,3:wb,4:tb',
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#iCMS@__user_report`;

CREATE TABLE `#iCMS@__user_report` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `appid` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '应用ID',
  `userid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '举报者',
  `iid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容ID',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '被举报者',
  `reason` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `content` varchar(255) NOT NULL DEFAULT '',
  `ip` varchar(20) NOT NULL DEFAULT '',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `#iCMS@__weixin_api_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ToUserName` varchar(255) NOT NULL DEFAULT '',
  `FromUserName` varchar(255) NOT NULL DEFAULT '',
  `CreateTime` int(11) NOT NULL DEFAULT '0',
  `content` text NOT NULL,
  `dayline` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `#iCMS@__sph_counter` (
  `counter_id` int(11) NOT NULL,
  `max_doc_id` int(11) NOT NULL,
  PRIMARY KEY (`counter_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

insert  into `#iCMS@__config`(`tid`,`name`,`value`) values (0,'site','a:5:{s:4:\"name\";s:4:\"iCMS\";s:8:\"seotitle\";s:39:\"高效简洁的开源内容管理系统\";s:8:\"keywords\";s:28:\"iCMS,idreamsoft,艾梦软件\";s:11:\"description\";s:132:\"iCMS 是一套采用 PHP 和 MySQL 构建的高效简洁的内容管理系统,为您的网站提供一个完美的开源解决方案\";s:3:\"icp\";s:0:\"\";}'),(0,'router','a:12:{s:3:\"URL\";s:26:\"http://demo.idreamsoft.com\";s:3:\"DIR\";s:1:\"/\";i:404;s:42:\"http://demo.idreamsoft.com/public/404.html\";s:10:\"public_url\";s:33:\"http://demo.idreamsoft.com/public\";s:8:\"user_url\";s:33:\"http://demo.idreamsoft.com/usercp\";s:8:\"html_dir\";s:5:\"html/\";s:8:\"html_ext\";s:5:\".html\";s:5:\"speed\";s:2:\"50\";s:7:\"rewrite\";s:1:\"0\";s:7:\"tag_url\";s:26:\"http://demo.idreamsoft.com\";s:8:\"tag_rule\";s:5:\"{PHP}\";s:7:\"tag_dir\";s:1:\"/\";}'),(0,'cache','a:5:{s:6:\"enable\";s:1:\"1\";s:6:\"engine\";s:4:\"file\";s:4:\"host\";s:5:\"cache\";s:4:\"time\";s:3:\"300\";s:8:\"compress\";s:1:\"1\";}'),(0,'FS','a:4:{s:3:\"url\";s:31:\"http://demo.idreamsoft.com/res/\";s:3:\"dir\";s:3:\"res\";s:10:\"dir_format\";s:7:\"Y/m-d/H\";s:9:\"allow_ext\";s:24:\"gif,jpg,rar,swf,jpeg,png\";}'),(0,'thumb','a:3:{s:6:\"enable\";s:1:\"1\";s:5:\"width\";s:3:\"140\";s:6:\"height\";s:3:\"140\";}'),(0,'watermark','a:12:{s:6:\"enable\";s:1:\"1\";s:5:\"width\";s:3:\"140\";s:6:\"height\";s:3:\"140\";s:3:\"pos\";s:1:\"0\";s:1:\"x\";s:1:\"0\";s:1:\"y\";s:1:\"0\";s:3:\"img\";s:13:\"watermark.png\";s:4:\"text\";s:4:\"iCMS\";s:8:\"fontsize\";s:2:\"12\";s:5:\"color\";s:7:\"#000000\";s:11:\"transparent\";s:2:\"80\";s:5:\"thumb\";s:1:\"1\";}'),(0,'user','a:6:{s:8:\"register\";s:1:\"1\";s:10:\"regseccode\";s:1:\"1\";s:5:\"login\";s:1:\"1\";s:12:\"loginseccode\";s:1:\"1\";s:9:\"agreement\";s:0:\"\";s:8:\"coverpic\";s:16:\"/ui/coverpic.jpg\";}'),(0,'publish','a:9:{s:10:\"autoformat\";s:1:\"0\";s:6:\"remote\";s:1:\"0\";s:7:\"autopic\";s:1:\"1\";s:8:\"autodesc\";s:1:\"1\";s:7:\"descLen\";s:3:\"100\";s:8:\"autoPage\";s:1:\"0\";s:11:\"AutoPageLen\";s:4:\"1000\";s:10:\"repeatitle\";s:1:\"0\";s:7:\"showpic\";s:1:\"1\";}'),(0,'comment','a:3:{s:6:\"enable\";s:1:\"1\";s:7:\"examine\";s:1:\"1\";s:7:\"seccode\";s:1:\"1\";}'),(0,'debug','a:2:{s:3:\"php\";s:1:\"0\";s:3:\"tpl\";s:1:\"1\";}'),(0,'time','a:3:{s:4:\"zone\";s:13:\"Asia/Shanghai\";s:6:\"cvtime\";s:1:\"0\";s:10:\"dateformat\";s:11:\"Y-m-d H:i:s\";}'),(0,'apps','a:10:{i:0;s:5:\"index\";i:1;s:7:\"article\";i:2;s:3:\"tag\";i:3;s:6:\"search\";i:4;s:6:\"usercp\";i:5;s:8:\"category\";i:6;s:7:\"comment\";i:7;s:8:\"favorite\";i:8;s:6:\"public\";i:9;s:4:\"user\";}'),(0,'other','a:4:{s:8:\"py_split\";s:0:\"\";s:13:\"keyword_limit\";s:2:\"-1\";s:14:\"sidebar_enable\";s:1:\"1\";s:7:\"sidebar\";s:1:\"1\";}'),(0,'system','a:1:{s:5:\"patch\";s:1:\"2\";}'),(1,'defaults','a:2:{s:6:\"source\";a:1:{i:0;s:3:\"asd\";}s:6:\"author\";a:1:{i:0;s:3:\"dfg\";}}'),(1,'word.filter','a:1:{i:0;a:1:{i:0;s:0:\"\";}}'),(1,'word.disable','a:1:{i:0;s:0:\"\";}'),(0,'sphinx','a:2:{s:4:\"host\";s:14:\"127.0.0.1:9312\";s:5:\"index\";s:31:\"iCMS_article iCMS_article_delta\";}'),(0,'open','a:4:{s:2:\"WX\";a:2:{s:5:\"appid\";s:0:\"\";s:6:\"appkey\";s:0:\"\";}s:2:\"QQ\";a:2:{s:5:\"appid\";s:0:\"\";s:6:\"appkey\";s:0:\"\";}s:2:\"WB\";a:2:{s:5:\"appid\";s:0:\"\";s:6:\"appkey\";s:0:\"\";}s:2:\"TB\";a:2:{s:5:\"appid\";s:0:\"\";s:6:\"appkey\";s:0:\"\";}}'),(0,'template','a:6:{s:10:\"index_mode\";s:1:\"1\";s:5:\"index\";s:16:\"{iTPL}/index.htm\";s:10:\"index_name\";s:5:\"index\";s:2:\"pc\";a:1:{s:3:\"tpl\";s:7:\"default\";}s:6:\"mobile\";a:3:{s:5:\"agent\";s:125:\"WAP,Smartphone,Mobile,UCWEB,Opera Mini,Windows CE,Symbian,SAMSUNG,iPhone,Android,BlackBerry,HTC,Mini,LG,SonyEricsson,J2ME,MOT\";s:6:\"domain\";s:26:\"http://demo.idreamsoft.com\";s:3:\"tpl\";s:6:\"mobile\";}s:6:\"device\";a:1:{i:0;a:4:{s:4:\"name\";s:6:\"weixin\";s:2:\"ua\";s:14:\"MicroMessenger\";s:6:\"domain\";s:26:\"http://demo.idreamsoft.com\";s:3:\"tpl\";s:6:\"weixin\";}}}'),(0,'api', 'a:1:{i:0;s:0:\"\";}'),(0,'article','a:3:{s:8:\"pic_next\";s:1:\"0\";s:11:\"pageno_incr\";s:0:\"\";s:9:\"prev_next\";s:1:\"0\";}');

insert  into `#iCMS@__app`(`id`,`name`,`title`,`table`,`field`,`binding`,`description`,`position`,`position2`,`form`,`show`,`addtime`) values (1,'article','文章','[[\"article\",\"id\"],[\"article_data\",\"aid\"]]','',0,'','','','',0,0),(2,'category','分类','[[\"category\",\"cid\"],[\"category_map\",\"node\"]]','',0,'','','','',0,0),(3,'tag','标签','[[\"tags\",\"id\"],[\"tags_map\",\"tid\"]]','',0,'','','','',0,0),(4,'push','推送','[[\"push\",\"id\"]]','',0,'','','','',0,0),(5,'comment','评论','[[\"comment\",\"id\"]]','',0,'','','','',0,0),(6,'prop','属性','[[\"prop\",\"pid\"],[\"prop_map\",\"node\"]]','',0,'','','','',0,0),(7,'message','私信','[[\"message\",\"id\"]]','',0,'','','','',0,0),(8,'favorite','收藏','[[\"favorite\",\"id\"],[\"favorite_data\",\"fid\"]]','',0,'','','','',0,0),(9,'user','用户','[[\"user\",\"uid\"],[\"user_data\",\"uid\"]]','',0,'','','','',0,0);

insert  into `#iCMS@__menu`(`id`,`rootid`,`ordernum`,`app`,`name`,`title`,`href`,`icon`,`class`,`a_class`,`target`,`caret`,`data-toggle`,`data-meta`,`data-target`) values (1,0,0,'home','管理','','__SELF__','fa fa-home','','','','','','',''),(2,0,1,'article','文章','','#','fa fa-pencil-square-o','dropdown','dropdown-toggle','','<b class=\"caret\"></b>','dropdown','',''),(3,8,6,'spider','采集管理','','#','fa fa-magnet','','','','','','',''),(4,0,2,'separator','','','','','divider-vertical','','','','','',''),(5,6,0,'push','推送管理','','#','fa fa-thumb-tack','dropdown','dropdown-toggle','','','dropdown','',''),(6,0,4,'assist','辅助','','#','fa fa-cogs','dropdown','dropdown-toggle','','<b class=\"caret\"></b>','dropdown','',''),(7,0,3,'user','用户','','#','fa fa-users','dropdown','dropdown-toggle','','<b class=\"caret\"></b>','dropdown','',''),(8,0,5,'tools','工具','','#','fa fa-gavel','dropdown','dropdown-toggle','','<b class=\"caret\"></b>','dropdown','',''),(9,0,6,'system','系统','','#','fa fa-cog','dropdown','dropdown-toggle','','<b class=\"caret\"></b>','dropdown','',''),(10,2,0,'category','添加栏目','','category&do=add','fa fa-edit','','','','','','',''),(11,2,1,'category','栏目管理','','category','fa fa-list-alt','','','','','','',''),(12,2,2,'separator','','','','','divider','','','','','',''),(13,2,3,'article','添加文章','','article&do=add','fa fa-edit','','','','','','',''),(14,2,4,'article','文章管理','','article','fa fa-list-alt','','','','','','',''),(15,2,5,'article','草稿箱','','article&do=inbox','fa fa-inbox','','','','','','',''),(16,2,6,'article','回收站','','article&do=trash','fa fa-trash-o','','','','','','',''),(17,2,11,'separator','','','','','divider','','','','','',''),(18,2,12,'comment','文章评论管理','','comment&do=article','fa fa-comments','','','','','','',''),(19,1,0,'home','管理首页','','__SELF__','fa fa-home','active','','','','','',''),(20,3,0,'spider','采集列表','','spider&do=manage','fa fa-list-alt','','','','','','',''),(21,3,1,'spider','未发文章','','spider&do=inbox','fa fa-inbox','','','','','','',''),(22,3,2,'separator','','','','','divider','','','','','',''),(23,3,4,'spider','添加方案','','spider&do=addproject','fa fa-edit','','','','','','',''),(24,3,3,'spider','采集方案','','spider&do=project','fa fa-magnet','','','','','','',''),(25,3,5,'separator','','','','','divider','','','','','',''),(26,3,7,'spider','添加规则','','spider&do=addrule','fa fa-edit','','','','','','',''),(27,3,6,'spider','采集规则','','spider&do=rule','fa fa-magnet','','','','','','',''),(28,3,8,'separator','','','','','divider','','','','','',''),(29,3,10,'spider','添加发布模块','','spider&do=addpost','fa fa-edit','','','','','','',''),(30,3,9,'spider','发布模块','','spider&do=post','fa fa-magnet','','','','','','',''),(31,5,1,'pushcategory','推送块管理','','pushcategory','fa fa-sitemap','','','','','','',''),(32,5,2,'pushcategory','添加推送块','','pushcategory&do=add','fa fa-edit','','','','','','',''),(33,5,3,'separator','','','','','divider','','','','','',''),(34,5,4,'push','推送管理','','push','fa fa-thumb-tack','','','','','','',''),(35,5,5,'push','添加推送','','push&do=add','fa fa-edit','','','','','','',''),(36,6,7,'tagcategory','标签分类管理','','tagcategory','fa fa-sitemap','','','','','','',''),(37,6,8,'tagcategory','添加标签分类','','tagcategory&do=add','fa fa-edit','','','','','','',''),(38,6,9,'tags','标签管理','','tags','fa fa-tags','','','','','','',''),(39,6,10,'tags','添加标签','','tags&do=add','fa fa-edit','','','','','','',''),(40,6,11,'separator','','','','','divider','','','','','',''),(41,6,14,'keywords','内链管理','','keywords','fa fa-paperclip','','','','','','',''),(42,6,15,'keywords','添加内链','','keywords&do=add','fa fa-edit','','','','','','',''),(43,6,16,'separator','','','','','divider','','','','','',''),(44,6,17,'prop','属性管理','','prop','fa fa-puzzle-piece','','','','','','',''),(45,6,18,'prop','添加属性','','prop&do=add','fa fa-edit','','','','','','',''),(46,6,19,'separator','','','','','divider','','','','','',''),(47,6,20,'filter','关键词过滤','','filter','fa fa-filter','','','','','','',''),(48,6,21,'search','搜索统计','','search','fa fa-search','','','','','','',''),(49,7,0,'user','会员管理','','user','fa fa-list-alt','','','','','','',''),(50,7,1,'user','添加会员','','user&do=add','fa fa-user','','','','','','',''),(51,7,2,'separator','','','','','divider','','','','','',''),(52,7,3,'account','管理员列表','','account','fa fa-list-alt','','','','','','',''),(53,7,4,'account','添加管理员','','account&do=add','fa fa-user','','','','','','',''),(54,7,5,'separator','','','','','divider','','','','','',''),(55,7,6,'groups','角色管理','','groups','fa fa-list-alt','','','','','','',''),(56,7,7,'groups','添加角色','','groups&do=add','fa fa-group','','','','','','',''),(57,8,0,'links','友情链接管理','','links','fa fa-list-alt','','','','','','',''),(58,8,1,'links','添加友情链接','','links&do=add','fa fa-edit','','','','','','',''),(59,8,5,'separator','','','','','divider','','','','','',''),(60,8,3,'files','文件管理','','files','fa fa-folder','','','','','','',''),(61,8,4,'files','上传文件','上传文件','files&do=multi&from=modal','fa fa-upload','','','','','modal','{\"width\":\"85%\",\"height\":\"640px\"}','#iCMS-MODAL'),(62,8,7,'separator','','','','','divider','','','','','',''),(63,8,8,'database','数据库管理','','#','fa fa-database','','','','','','',''),(64,63,0,'database','数据库备份','','database&do=backup','fa fa-cloud-download','','','','','','',''),(65,63,1,'database','备份管理','','database&do=recover','fa fa-upload','','','','','','',''),(66,63,2,'database','修复优化','','database&do=repair','fa fa-gavel','','','','','','',''),(67,63,9,'database','数据替换','','database&do=replace','fa fa-retweet','','','','','','',''),(68,8,10,'separator','','','','','divider','','','','','',''),(69,8,11,'html','生成静态','','#','fa fa-file','','','','','','',''),(70,69,0,'html','首页静态化','','html&do=index','fa fa-refresh','','','','','','',''),(71,69,1,'html','栏目静态化','','html&do=category','fa fa-refresh','','','','','','',''),(72,69,2,'html','文章静态化','','html&do=article','fa fa-refresh','','','','','','',''),(73,69,3,'separator','','','','','divider','','','','','',''),(74,69,4,'html','全站生成静态','','html&do=all','fa fa-refresh','','','','','','',''),(75,69,5,'separator','','','','','divider','','','','','',''),(76,69,6,'setting','静态设置','','setting&tab=url','fa fa-cog','','','','','','',''),(77,69,7,'','静态设置帮助','','http://www.idreamsoft.com/help/v6/html','fa fa-flag','','','_blank','','','',''),(78,9,0,'setting','系统设置','','setting','fa fa-cog','','','','','','',''),(79,78,0,'setting','网站设置','','setting&tab=base','fa fa-cog','','','','','','',''),(80,78,1,'setting','URL设置','','setting&tab=url','fa fa-cog','','','','','','',''),(81,78,2,'setting','标签设置','','setting&tab=tag','fa fa-cog','','','','','','',''),(82,78,3,'setting','缓存设置','','setting&tab=cache','fa fa-cog','','','','','','',''),(83,78,4,'setting','附件设置','','setting&tab=file','fa fa-cog','','','','','','',''),(84,78,5,'setting','缩略图设置','','setting&tab=thumb','fa fa-cog','','','','','','',''),(85,78,6,'setting','水印设置','','setting&tab=watermark','fa fa-cog','','','','','','',''),(86,78,7,'setting','用户设置','','setting&tab=user','fa fa-cog','','','','','','',''),(87,78,8,'setting','发布设置','','setting&tab=publish','fa fa-cog','','','','','','',''),(88,78,9,'setting','评论设置','','setting&tab=comment','fa fa-cog','','','','','','',''),(89,78,10,'setting','其它设置','','setting&tab=other','fa fa-cog','','','','','','',''),(90,78,11,'setting','更新设置','','setting&tab=patch','fa fa-cog','','','','','','',''),(91,78,12,'setting','高级设置','','setting&tab=grade','fa fa-cog','','','','','','',''),(92,9,1,'separator','','','','','divider','','','','','',''),(93,9,7,'cache','清理缓存','','#','fa fa-refresh','','','','','','',''),(94,93,22,'cache','更新系统设置缓存','','cache&acp=setting','fa fa-refresh','','','iPHP_FRAME','','','',''),(95,93,23,'separator','','','','','divider','','','','','',''),(96,93,24,'cache','更新所有分类缓存','','cache&do=allcategory','fa fa-refresh','','','iPHP_FRAME','','','',''),(97,93,25,'cache','更新文章栏目缓存','','cache&do=category','fa fa-refresh','','','iPHP_FRAME','','','',''),(98,93,26,'cache','更新推送版块缓存','','cache&do=pushcategory','fa fa-refresh','','','iPHP_FRAME','','','',''),(99,93,27,'cache','更新标签分类缓存','','cache&do=tagcategory','fa fa-refresh','','','iPHP_FRAME','','','',''),(100,93,28,'separator','','','','','divider','','','','','',''),(101,93,29,'cache','更新属性缓存','','cache&acp=prop','fa fa-refresh','','','iPHP_FRAME','','','',''),(102,93,30,'cache','更新内链缓存','','cache&acp=keywords','fa fa-refresh','','','iPHP_FRAME','','','',''),(103,93,31,'cache','更新过滤缓存','','cache&acp=filter','fa fa-refresh','','','iPHP_FRAME','','','',''),(104,93,32,'separator','','','','','divider','','','','','',''),(105,93,33,'cache','重计栏目文章数','','cache&do=artCount','fa fa-refresh','','','iPHP_FRAME','','','',''),(106,93,34,'separator','','','','','divider','','','','','',''),(107,93,35,'cache','更新后台菜单缓存','','cache&do=menu','fa fa-refresh','','','iPHP_FRAME','','','',''),(108,93,37,'cache','清除模板缓存','','cache&do=tpl','fa fa-refresh','','','iPHP_FRAME','','','',''),(109,9,6,'separator','','','','','divider','','','','','',''),(110,9,5,'template','模板管理','','template','fa fa-desktop','','','','','','',''),(111,9,8,'separator','','','','','divider','','','','','',''),(112,9,9,'patch','检查更新','','patch&do=check&force=1','fa fa-repeat','','','iPHP_FRAME','','','',''),(113,9,10,'','官方网站','','http://www.idreamsoft.com','fa fa-star','','','_blank','','','',''),(114,9,11,'','帮助文档','','http://www.idreamsoft.com/help/','fa fa-question-circle','','','_blank','','','',''),(115,8,2,'separator','','','','','divider','','','','','',''),(117,93,36,'separator','','','','','divider','','','','','',''),(119,9,2,'menu','后台菜单管理','','menu','fa fa-desktop','','','','','','',''),(120,9,3,'menu','添加菜单','','menu&do=add','fa fa-pencil-square-o','','','','','','',''),(121,9,4,'separator','','','','','divider','','','','','',''),(122,6,6,'separator','','','','','divider','','','','','',''),(123,78,0,'setting','模板设置','','setting&tab=tpl','fa fa-cog','','','','','','',''),(124,6,13,'separator','','','','','divider','','','','','',''),(125,6,12,'comment','评论管理','','comment','fa fa-comments','','','','','','',''),(126,2,9,'article','审核文章','审核用户文章','article&do=examine','fa fa-minus-circle','','tip','','','','',''),(127,2,7,'separator','','','','','divider','','','','','',''),(128,2,8,'article','用户文章管理','查看用户文章','article&do=user','fa fa-check-circle','','tip','','','','',''),(129,2,10,'article','垃圾文章','被拒绝的用户文章','article&do=off','fa fa-times-circle','','tip','','','','','');

insert  into `#iCMS@__group`(`gid`,`name`,`ordernum`,`power`,`cpower`,`type`) values (1,'超级管理员',1,'[\"ADMINCP\",\"ARTICLE.VIEW\",\"ARTICLE.EDIT\",\"ARTICLE.DELETE\",\"FILE.UPLOAD\",\"FILE.MKDIR\",\"FILE.MANAGE\",\"FILE.BROWSE\",\"FILE.EDIT\",\"FILE.DELETE\",\"1\",\"19\",\"2\",\"10\",\"11\",\"12\",\"13\",\"14\",\"15\",\"16\",\"127\",\"128\",\"126\",\"129\",\"17\",\"18\",\"4\",\"7\",\"49\",\"50\",\"51\",\"52\",\"53\",\"54\",\"55\",\"56\",\"6\",\"5\",\"31\",\"32\",\"33\",\"34\",\"35\",\"122\",\"36\",\"37\",\"38\",\"39\",\"40\",\"125\",\"124\",\"41\",\"42\",\"43\",\"44\",\"45\",\"46\",\"47\",\"48\",\"8\",\"57\",\"58\",\"115\",\"60\",\"61\",\"59\",\"3\",\"20\",\"21\",\"22\",\"24\",\"23\",\"25\",\"27\",\"26\",\"28\",\"30\",\"29\",\"62\",\"63\",\"64\",\"65\",\"66\",\"67\",\"68\",\"69\",\"70\",\"71\",\"72\",\"73\",\"74\",\"75\",\"76\",\"77\",\"9\",\"78\",\"79\",\"123\",\"80\",\"81\",\"82\",\"83\",\"84\",\"85\",\"86\",\"87\",\"88\",\"89\",\"90\",\"91\",\"92\",\"119\",\"120\",\"121\",\"110\",\"109\",\"93\",\"94\",\"95\",\"96\",\"97\",\"98\",\"99\",\"100\",\"101\",\"102\",\"103\",\"104\",\"105\",\"106\",\"107\",\"117\",\"108\",\"111\",\"112\",\"113\",\"114\"]','[\"0:a\"]','1'),(2,'编辑',2,'','','1'),(3,'会员',1,'','','0');

insert  into `#iCMS@__members`(`uid`,`gid`,`username`,`password`,`nickname`,`realname`,`gender`,`info`,`power`,`cpower`,`regtime`,`lastip`,`lastlogintime`,`logintimes`,`post`,`type`,`status`) values (1,1,'admin','b316df1d65ee42ff51a5393df1f86105','iCMS.V6','',0,'','','',0,'127.0.0.1',1414207894,139,0,1,1);

insert  into `#iCMS@__prop`(`pid`,`rootid`,`cid`,`field`,`type`,`ordernum`,`name`,`val`) values (1,0,0,'pid','article',0,'头条','1'),(2,0,0,'pid','article',0,'首页推荐','2'),(3,0,0,'pid','category',0,'推荐栏目','1'),(4,0,0,'pid','tags',0,'热门标签','1'),(5,0,0,'pid','user',0,'推荐用户','1');

insert  into `#iCMS@__spider_post`(`id`,`name`,`app`,`post`,`fun`) values (1,'直接发布','article','status=1\r\npostype=1\r\nremote=true\r\nautopic=true','do_save'),(2,'采集到草稿','article','status=0\r\npostype=1\r\nremote=true\r\nautopic=true','do_save');

insert  into `#iCMS@__spider_project`(`id`,`name`,`urls`,`list_url`,`cid`,`rid`,`poid`,`sleep`,`auto`) values (1,'科学探索_腾讯科技频道','http://tech.qq.com/science.htm','',1,1,1,30,0);

insert  into `#iCMS@__spider_rule`(`id`,`name`,`rule`) values (1,'采集规则示例 (科学探索_腾讯科技频道)','a:19:{s:10:\"user_agent\";s:83:\"Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)\";s:6:\"cookie\";s:0:\"\";s:7:\"charset\";s:3:\"gbk\";s:4:\"sort\";s:1:\"1\";s:9:\"list_urls\";s:30:\"http://tech.qq.com/science.htm\";s:14:\"list_area_rule\";s:65:\"<div id=\\\"listZone\\\"><%content%><script type=\\\"text/javascript\\\">\";s:16:\"list_area_format\";s:0:\"\";s:13:\"list_url_rule\";s:99:\"<h3 class=\\\"f18 l26\\\"><a target=\\\"_blank\\\" href=\\\"<%url%>\\\" title=\\\"<%var_UL%>\\\"><%title%></a></h3>\";s:8:\"list_url\";s:7:\"<%url%>\";s:4:\"data\";a:3:{i:0;a:6:{s:4:\"name\";s:5:\"title\";s:4:\"rule\";s:20:\"<h1><%content%></h1>\";s:10:\"cleanbefor\";s:0:\"\";s:10:\"cleanafter\";s:0:\"\";s:4:\"trim\";s:1:\"1\";s:5:\"empty\";s:1:\"1\";}i:1;a:8:{s:4:\"name\";s:4:\"body\";s:4:\"rule\";s:70:\"<div id=\\\"Cnt-Main-Article-QQ\\\" bossZone=\\\"content\\\"><%content%></div>\";s:10:\"cleanbefor\";s:0:\"\";s:10:\"cleanafter\";s:0:\"\";s:5:\"multi\";s:1:\"1\";s:6:\"format\";s:1:\"1\";s:4:\"trim\";s:1:\"1\";s:5:\"empty\";s:1:\"1\";}i:2;a:4:{s:4:\"name\";s:11:\"description\";s:4:\"rule\";s:41:\"<p class=\\\"Introduction\\\"><%content%></p>\";s:10:\"cleanbefor\";s:25:\"[<strong>摘要</strong>]\";s:10:\"cleanafter\";s:0:\"\";}}s:14:\"page_area_rule\";s:0:\"\";s:13:\"page_url_rule\";s:0:\"\";s:14:\"page_url_parse\";s:7:\"<%url%>\";s:13:\"page_no_start\";s:1:\"1\";s:11:\"page_no_end\";s:1:\"5\";s:12:\"page_no_step\";s:1:\"1\";s:14:\"page_url_right\";s:31:\"<div id=\\\"Cnt-Main-Article-QQ\\\"\";s:14:\"page_url_error\";s:0:\"\";s:8:\"page_url\";s:20:\"<%url%>_<%step%>.htm\";}');
