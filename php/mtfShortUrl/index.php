<?php
/******* 短网址系统 ******
 * Author：小宇
 * Date: 2020-05-21 00:38
 ************************/
    $c = array(
        'db' => array(
            'host' => '127.0.0.1', // 主机
            'usr' => 'k_os120_com', // 数据库用户名
            'psd' => '1991madfan925', // 数据库密码
            'dbname' => 'k_os120_com' // 数据库名称
        )
    );
    $m = mysqli_connect($c['db']['host'], $c['db']['usr'], $c['db']['psd'], $c['db']['dbname']);
    // 检查连接
    if (!$m) {
        die('连接错误: ' . mysqli_connect_error());
    }
    // 判断是否安装，如果未安装则安装
    if (file_exists('.htaccess')) {
        $q = reset(explode('?',end(explode('mtfq=',@$_SERVER['QUERY_STRING']))));
        $a = explode('/', $q);
        if ($a[0] === 'api'){
            if ($a[1] === 'short') {
                $g = array(
                    'u' => ''
                );
                if (!empty($_SERVER[REQUEST_URI])) {
                    $p = parse_url($_SERVER[REQUEST_URI]);
                    parse_str($p['query'], $g);
                }
                $url = trim($g['u']);
                if (!$url) {
                    die('缺少参数网址：u');
                }
                $url = urldecode($url);
                $md5 = md5($url);
                
                $r = mysqli_query($m, "SELECT code FROM `mtf_shorturl` WHERE md5 = '" . $md5 ."'");
                if ($r && $r->num_rows > 0) {
                    $row = mysqli_fetch_array($r, MYSQLI_ASSOC);
                    $code = $row['code'];
                } else {
                    $maxId = 1;
                    $r = mysqli_query($m, 'SELECT MAX(id) as max_id FROM `mtf_shorturl`');
                    if ($r) {
                        $row = mysqli_fetch_array($r, MYSQLI_ASSOC);
                        $maxId = $row['max_id'] + 1;
                    }
                    $code = base_convert($maxId, 10, 36);
                    $r = mysqli_query($m, "INSERT INTO `mtf_shorturl` (`id`, `code`, `md5`, `url`, `hits`, `add_time`,`upd_time`) VALUES ('" . $maxId . "', '" . $code . "', '" . $md5 . "', '" . $url . "', '0', CURRENT_TIMESTAMP , CURRENT_TIMESTAMP);");
                    if (!$r) {
                        logger('新增网址失败：'."INSERT INTO `mtf_shorturl` (`id`, `code`, `md5`, `url`, `hits`, `add_time`,`upd_time`) VALUES ('" . $maxId . "', '" . $code . "', '" . $md5 . "', '" . $url . "', '0', CURRENT_TIMESTAMP , CURRENT_TIMESTAMP);");
                        die('新增网址失败，请重试');
                    }
                }
                die('http' . (isSsl() ? 's' : '') . '://' . $_SERVER['SERVER_NAME'] . ($_SERVER[PHP_SELF] ? substr($_SERVER[PHP_SELF], 0, strrpos($_SERVER[PHP_SELF], '/')) : '') . '/' . $code);
            }
        } else if ($a[0]) {
            $code = $a[0];
            $r = mysqli_query($m, "SELECT url FROM `mtf_shorturl` WHERE code = '" . $code ."'");
            if ($r && $r->num_rows > 0) {
                $row = mysqli_fetch_array($r, MYSQLI_ASSOC);
                $url = $row['url'];
                // 浏览次数 + 1，不关心结果
                mysqli_query($m, "UPDATE `mtf_shorturl` SET `hits` = `hits` + 1 WHERE code = '" . $code ."'");
                // 302跳转到指定网址
                header('Location:' . $url);
            } else {
                header('HTTP/1.1 404 Not Found');
                header("status: 404 Not Found");
                die('404');
            }
        } else {
            die('http' . (isSsl() ? 's' : '') . '://' . $_SERVER['SERVER_NAME'] . ($_SERVER[REQUEST_URI] ? $_SERVER[REQUEST_URI] : '/') . 'api/short?u={urlEncode后的网址}');
        }
    } else {
        // 删除原来的数据库
        mysqli_query($m, "DROP TABLE IF EXISTS `mtf_shorturl`;");
        // 创建数据库
        $r = mysqli_query($m, "CREATE TABLE IF NOT EXISTS `mtf_shorturl` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '序号',
            `code` varchar(20) DEFAULT '' COMMENT '编码',
            `md5` char(32) DEFAULT '' COMMENT 'MD5',
            `url` varchar(2048) DEFAULT '' COMMENT '地址',
            `hits` int(11) DEFAULT '0' COMMENT '浏览次数',
            `add_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
            `upd_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
            PRIMARY KEY (`id`),
            UNIQUE KEY `id` (`id`),
            UNIQUE KEY `code` (`code`),
            UNIQUE KEY `md5` (`md5`),
            KEY `id_2` (`id`),
            KEY `code_2` (`code`),
            KEY `md5_2` (`md5`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COMMENT='短网址' AUTO_INCREMENT=1 ;");
        if (!$r) {
            die('数据库创建失败！');
        }
        // 生成伪静态规则.htaccess
        file_put_contents('.htaccess', 'RewriteEngine On
        RewriteRule \.(js|css|svg|gif|png|jpg|jpeg|swf|ico|html|txt|webp)$ - [L]
        RewriteRule ^(.*)$ index.php?mtfq=$1');

        die('安装成功！');
    }

    // 日志
    function logger($logText) {
        $logSize = 100000; // 日志最大10M
        $logFile = 'log.txt';
        
        if (file_exists($logFile) && filesize($logFile) > $logSize) { // 如果日志超过大小，自动删除之前的一半记录
            $c = file_get_contents($logFile);
            $a = explode("\n", $c);
            $l = count($a);
            $a = array_slice($a, floor($l/2)); // 删除一半记录
            file_put_contents($logFile, implode("\n", $a));
        }
        file_put_contents($logFile, date('H:i:s') . ' ' . $logText . "\n", FILE_APPEND);
    }

    // 是否ssl
    function isSsl() {
        if ($_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            $_SERVER['REQUEST_SCHEME'] = 'https';
            $_SERVER['HTTPS'] = 'on';
        }
        if ( isset( $_SERVER['HTTPS'] ) ) {
            if ( 'on' == strtolower( $_SERVER['HTTPS'] ) ) {
                return true;
            }
     
            if ( '1' == $_SERVER['HTTPS'] ) {
                return true;
            }
        } elseif ( isset($_SERVER['SERVER_PORT'] ) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
            return true;
        }
        return true;
    }
?>