<?php 
class mtfFileConf{
    public $maxTime=300;//与kangle中的配置中 超时 时间对应：转码时间是这个时间的 5 倍，不超过25分钟
	//相对于root的路径
	public $root='ZONE';
	public $dir=array(
					'log'=>'log',
					'file'=>'file',
					'cache'=>'cache',
					'tmp'=>'tmp',
					'chunk'=>'chunk'
				);
	public $conf=array(
					'dir'=>'../',
					'convert'=>array(
									'image'=>array(
										'max_width'=>1290,
										'max_height'=>2500
									),
									'video'=>array(
												array('b'=>360,'w'=>480,'ext'=>'mp4','force'=>1),
												array('b'=>720,'w'=>640,'ext'=>'mp4','skip'=>1),
												array('b'=>1080,'w'=>1280,'ext'=>'mp4')
											),
									'audio'=>array(
												array('b'=>64,'ext'=>'mp3','force'=>1),
												array('b'=>128,'ext'=>'mp3')
											),
									'doc'=>array(
												array('ext'=>'pdf')
											)
									),
					'preview'=>array(
									'video'=>array('ext'=>'gif'),
									'audio'=>array('ext'=>'png'),
									'zip'=>array('ext'=>'jpg'),
									'sub'=>array('ext'=>'jpg'),
									'doc'=>array('ext'=>'jpg'),
									'bt'=>array('ext'=>'jpg'),
									'txt'=>array('ext'=>'jpg')
									),
					'domain'=>array(
									'web'=>'',
									'cdn'=>'',
									'dat'=>''
									),
					'uid'=>array(
									'admin'=>array(),
									'init'=>100000,
									'limit'=>5
								),
					'key' => array(
						'domain'=>'',
						'psd'=>''
					),
					'list'=>array(
						'max_text_length'=>280,//140*2
						'max_p_length'=>3
					),
					'cache'=>array(
						'out'=>1296000,
						'max'=>50000,
						'p'=>1
					),
					'hsv'=>array(
						'black'=>array(0,180,0,255,0,46),
						'gray'=>array(0,180,0,43,46,220),
						'white'=>array(0,180,0,30,221,255),
						'red'=>array(0,10,43,255,46,255),
						'pink'=>array(156,180,43,255,46,255),
						'orange'=>array(11,25,43,255,46,255),
						'yellow'=>array(26,34,43,255,46,255),
						'green'=>array(35,77,43,255,46,255),
						'cyan'=>array(78,99,43,255,46,255),
						'blue'=>array(100,124,43,255,46,255),
						'purple'=>array(125,155,43,255,46,255)
					),
					'tag'=>array(
						'rec'=>array('专题','萌点','服饰','作者','画师','模特','数据结构','算法','遍历','查找','排序')
					),
					'dn'=>array(
						'100131'=>'www.mantoufan.com',
						'100132'=>'yuanmeng.us',
						'100133'=>'mzhan.mantoufan.com',
						'100138'=>'ac.mantoufan.com',
						'100315'=>'v.madfan.cn',
						'115210'=>'yu.mantoufan.com',
					),
					'dy'=>array(
						'100131'=>array(
							'xiongzhang'=>'1603038209467856',
						),
						'100133'=>array(
							'xiongzhang'=>'1603038209467856'
						)
					)
				);
	public $db=array(
					'host'=>'',
					'user'=>'',
					'password'=>'',
					'database'=>'',
					'table'=>'mtffile',
					'table_msg'=>'mtfmsg',
					'install'=>"CREATE TABLE IF NOT EXISTS `mtffile` (
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
								) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
				);
	
	public $webdav=array('url'=>'','usr'=>'','psd'=>'');
}