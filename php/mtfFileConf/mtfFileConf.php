<?php

class mtfFileConf {
  public $maxTime = 900; // 与 kangle 中的配置中 超时 时间对应：转码时间是这个时间的 5 倍，不超过10分钟
  public $dir = array('log' => 'log', 'file' => 'file', 'cache' => 'cache', 'tmp' => 'tmp', 'chunk' => 'chunk', 'oss' => 'oss/mtf');
  public $conf = array(
    'convert' => array(
      'image' => array(
        'widths' => array(50, 300, 450, 600, 1280), // csw = 600 张图片 头像已存到数据库
        'heights' => array(50, 300, 450, 600, 150),
      ),
      'video' => array(
        array('b' => 360, 'w' => 480, 'ext' => 'mp4', 'force' => 1),
        array('b' => 1080, 'w' => 1280, 'ext' => 'mp4'),
      ),
      'audio' => array(
        array('b' => 64, 'ext' => 'mp3', 'force' => 1),
        array('b' => 128, 'ext' => 'mp3'),
      ),
      'doc' => array(array('ext' => 'pdf')),
    ),
    'preview' => array(
      'video' => array('ext' => 'gif'),
      'audio' => array('ext' => 'png'),
      'zip' => array('ext' => 'jpg'),
      'sub' => array('ext' => 'jpg'),
      'doc' => array('ext' => 'jpg'),
      'bt' => array('ext' => 'jpg'),
      'txt' => array('ext' => 'jpg'),
      'rom' => array('ext' => 'jpg'),
    ),
    'domain' => array('web' => '', 'cdn' => '', 'dat' => ''),
    'uid' => array('admin' => array(), 'init' => 100000, 'limit' => 5),
    'key' => array('domain' => '', 'psd' => ''),
    'list' => array('max_text_length' => 280, 'max_p_length' => 3),
    'cache' => array('out' => 1296000, 'max' => 50000, 'p' => 1),
    'hsv' => array(
      'black' => array(0, 180, 0, 255, 0, 46),
      'gray' => array(0, 180, 0, 43, 46, 220),
      'white' => array(0, 180, 0, 30, 221, 255),
      'red' => array(0, 10, 43, 255, 46, 255),
      'pink' => array(156, 180, 43, 255, 46, 255),
      'orange' => array(11, 25, 43, 255, 46, 255),
      'yellow' => array(26, 34, 43, 255, 46, 255),
      'green' => array(35, 77, 43, 255, 46, 255),
      'cyan' => array(78, 99, 43, 255, 46, 255),
      'blue' => array(100, 124, 43, 255, 46, 255),
      'purple' => array(125, 155, 43, 255, 46, 255),
    ),
    'tag' => array(
      'rec' => array('专题', '萌点', '服饰', '作者', '画师', '模特', '数据结构', '算法', '遍历', '查找', '排序'),
      'filter' => array('作者', '摄影师', '模特', '画师'),
    ),
    'dn' => array(
      '100131' => 'www.mfan8.com',
      '100132' => 'www.yuanmenghuashi.com',
      '100133' => 'mzhan.kv.cm',
      '100138' => 'ac.kv.cm',
      '115210' => 'www.shon8.com',
    ),
    'oss' => array(
      'ext' => array('mp4', 'mp3', 'nes', 'zip', '7z', 'rar', 'apk', 'ipa'),
    ),
    'languages' => array('en', 'zh-CN', 'zh-TW', 'jp'),
    'yzhanTranslator' => array(
      'openAIApiKey' => 'sk-yxRA2YtWvAeIr1WSF820239e6f7f4c0a8f6d61Ec2b28Cb8e',
      'openAIApiUrl' => 'https://api.gueai.com',
      'timeout' => 120,
      'maxAge' => 86400 * 365 * 30,
    ),
  );
  public $db = array(
    'host' => '',
    'user' => '',
    'password' => '',
    'database' => '',
    'table' => 'mtffile',
    'table_msg' => 'mtfmsg',
    'install' => "CREATE TABLE IF NOT EXISTS `mtffile` (
							`i` bigint(18) NOT NULL COMMENT '序号',
							`e` varchar(10) NOT NULL COMMENT '扩展名',
							`a` varchar(5000) NOT NULL DEFAULT '' COMMENT '属性',
							`k` varchar(10000) NOT NULL DEFAULT '' COMMENT '标签',
							`h` varchar(64) NOT NULL DEFAULT '' COMMENT 'Hash',
							`hm` varchar(64) DEFAULT '' COMMENT 'HashMD5',
							`t` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',
							`t0` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '最后修改时间',
							`r` text COMMENT '关系',
							`o` varchar(18) NOT NULL DEFAULT '' COMMENT '所有者',
							`q` text COMMENT '引用',
							`z` int(12) NOT NULL DEFAULT '0' COMMENT '♥',
							`z0` int(11) DEFAULT NULL COMMENT '可用♥',
							`ti` bigint(18) DEFAULT NULL COMMENT '发送到（序号）',
							`p` varchar(18) DEFAULT '' COMMENT '初始父级',
							`m` varchar(1) DEFAULT '' COMMENT '模式',
							`d` varchar(8) DEFAULT '' COMMENT '位置',
							`tt` varchar(12) DEFAULT '' COMMENT '时间',
							`v` varchar(150) DEFAULT '' COMMENT '变量',
							`fid` varchar(32) DEFAULT '' COMMENT '指纹',
							`ip` varchar(15) DEFAULT '' COMMENT 'IP',
							`ch` varchar(3) DEFAULT '' COMMENT 'hsv-h',
							`cs` varchar(3) DEFAULT '' COMMENT 'hsv-s',
							`cv` varchar(3) DEFAULT '' COMMENT 'hsv-v',
							`l` tinyint(1) DEFAULT '0' COMMENT '锁定（1 永久锁定 2 需实名  0 未锁定）',
							UNIQUE KEY `i` (`i`)
						) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='文件';
						CREATE TABLE IF NOT EXISTS `mtfmsg` (
							`i` bigint(18) NOT NULL AUTO_INCREMENT,
							`g` tinyint(3) NOT NULL DEFAULT '0' COMMENT '0-点♥ 1-关注/粉丝 2-消息',
							`f` varchar(18) NOT NULL DEFAULT '',
							`to` varchar(18) NOT NULL DEFAULT '',
							`v` varchar(250) NOT NULL DEFAULT '',
							`fid` varchar(32) NOT NULL DEFAULT '' COMMENT '指纹',
							`ip` varchar(15) NOT NULL COMMENT 'IP',
							`t` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
							PRIMARY KEY (`i`)
						) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;",
  );
}