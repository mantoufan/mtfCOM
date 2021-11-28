<?php
class Lrc{
	//处理LRC
	public function get($lrc){   
		if( $lrc ){   
			// 远程获取歌词内容，谷歌翻译会自动加<font>，此处过滤掉
			$content = str_replace('</font>','',str_replace('<font>','',$lrc));    
			// 按"回车换行"将歌词切割成数组   
			$array = explode("\n", $content);  
			$lrc = array();   
			foreach($array as $val){   
				// 清除掉"回车不换行"符号   
				$val = preg_replace('/\r/', '', $val);        
				// 正则匹配歌词时间   
				$temp = preg_match_all('/\[\d{1,4}\:\d{1,2}\.\d{1,4}\]/', $val, $matches);   
				if( !empty($matches[0]) ){   
					$data_plus = "";   
					$time_array = array();     
					// 将可能匹配的多个时间挑选出来，例如：[00:21]、[03:40]   
					foreach($matches[0] as $V){   
						$data_plus .= $V;   
						$V = str_replace("[", "", $V);   
						$V = str_replace("]", "", $V);   
						$date_array = explode(":", $V); 
						// 将例如：00:21、03:40 转换成秒   
						$time_array[] = $date_array[0]*60 + $date_array[1] ;   
					}
					// 将上面的得到的时间，例如：[00:21][03:40]，替换成空，得到歌词   
					$data_plus = str_replace($data_plus, "", $val);     
					// 将时间和歌词组合到数组中   
					foreach($time_array as $t){   
						$lrc[intval( $t*10)] = $data_plus;   
					}   
				}   
			}   
			   
			// 按时间顺序来排序数组   
			krsort($lrc);
			$pre=0;   
			foreach ($lrc as $k=>$v){
				$d=$pre-$k;
				$d=($d>0?$d:10);
				$pre=$k;
				$newLRC[$k]["lrc".($d*100)]=$v;
			}
			// 输出 json格式   
			return array('json'=>json_encode($newLRC),'ar'=>$lrc);   
		}   
		return false;   
	}
}
?>