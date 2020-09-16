<?php
class mtfCode{
	private $_root;
	public function __construct()
    {	
		$_root=str_replace('\\','/',dirname(__file__)).'/';
		$this->_root=$_root;
	}
	
	public function deQRCode($_f_p,$_code_http_dir_path='')
	{
		if(file_exists($_f_p)){
			
			$source=imagecreatefromstring(file_get_contents($_f_p));
			
			$_pre='qr'.uniqid();
			$_f_p=$_pre.'.jpg';
			
			imagejpeg($source, $_f_p, 70);
			$source=imagecreatefromstring(file_get_contents($_f_p));
			
			//保留颜色数目
			$_num=array(16);
			//将图片分成九等分，提高识别
			$w = 1;
			$h = 3;
			
			$_ar=array();
			$_ar[]=$_f_p;
			
			foreach($_num as $_k=>$_v){
				
				imagetruecolortopalette($source, FALSE, $_v);//禁止抖动，避免颜色接近
				$_f_p=$_pre.$_v.'.png';
				imagepng($source, $_f_p);

				list($width, $height) = getimagesize($_f_p);
				$newwidth = floor($width / $w);
				$newheight = floor($height / $h);
				
				for( $i=0 ; $i< $w; $i++ ){
					$_pw = $newwidth*$i;
					for( $j=0 ; $j< $h; $j++ ){
						$_ph = $newheight*$j;
						$thumb = ImageCreateTrueColor($newwidth, $newheight);
						imagecopyresized( $thumb, $source, 0, 0, $_pw, $_ph, $width,  $height, $width, $height);
						$_p=$_pre.$i.$j.'.jpg';
						imagejpeg( $thumb , $_p ,87);
						$_ar[]=$_p;
					}
				}
				unlink($_f_p);
			}
			
			$_root=$this->_root;
			
			foreach($_ar as $k=>$_f_p){
				/*
				$_i=pathinfo($_f_p);
				$_n=str_replace($_i['extension'],'jpg',$_i['basename']);
				$_d=dirname(__FILE__).'/'.$_n;

				copy($_f_p,$_d);
				*/
				
				$_s=exec('"'.$_root.'bin/Win32/Zbar/zbarimg.exe" -D "'.$_f_p.'" -q');
				if($_s){
					$this->_del($_ar);
					return substr($_s,8);	
				}elseif($k===0){
					/*暂停PHP扫码，效率太低，超过8秒
					ini_set('memory_limit','256M');
					include_once($_root.'QRcode/De/autoload.php');
					$qrcode = new QrReader($_f_p);
					$_s=$qrcode->text();
					if($_s){
						$this->_del($_ar);
						return $_s;
					}
					*/
				}
			}
			
			$url = "http://zxing.org/w/decode"; //不支持SSL上传
			$_f_p=$_ar[0];
			$ch = curl_init();

			if (class_exists('\CURLFile')) {// 这里用特性检测判断php版本
				curl_setopt($ch, CURLOPT_SAFE_UPLOAD, true);
				$data = array('f' => new \CURLFile($_f_p));//>=5.5
			} else {
				if (defined('CURLOPT_SAFE_UPLOAD')) {
					curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
				}
				$data = array('f' => '@' .$_f_p);//<=5.5
			}

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, 1 );
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

			/*加速：开始*/
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
			//加速POST，减少1秒延迟 Expect: 请求gzip，并解压
			curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1); //强制协议为1.1
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Expect: ","Accept-Encoding:gzip","SERVER: ".json_encode($_SERVER)));

			//开启GZIP解压，减少数据传输量
			curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
			/*加速：结束*/

			curl_setopt($ch, CURLOPT_REFERER, "https://zxing.org/w/decode.jspx");
			curl_setopt($ch, CURLOPT_TIMEOUT, 7);

			$_h = curl_exec($ch);
			
			preg_match_all('/<td><pre>(.*?)<\/pre><\/td>/',$_h,$_m);
			$this->_del($_ar);
			return $_m[1][0];
		}
	}
	
	public function enQRCode($_s)
	{
		include_once($_root.'QRcode/En/qrcode.class.php');
		// 纠错级别：L、M、Q、H
		$errorCorrectionLevel = 'H';  
		// 点的大小：1到10
		$matrixPointSize = 4;  
		//创建一个二维码文件
		QRcode::png($_s, false, $errorCorrectionLevel, $matrixPointSize, 2);
	}
	
	private function _del($_ar){
		foreach($_ar as $k=>$_f_p){
			unlink($_f_p);
		}
	}
}
?>