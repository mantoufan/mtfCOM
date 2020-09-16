<?php
class mtfSub{
	private function _covert_sign($s){
		$ar=array('＼'=>'\\','｛'=>'{','｝'=>'}');
		return strtr($s,$ar);
	}
	public function isLrc($s){
		return preg_match("/\[\d{2}\:\d{2}\.\d{2}\]/", $s, $matches)>0?true:false;
	}
	public function isASS($s){
		return stristr(strtolower(substr(trim($s),0,20)),'[script info]')?true:false;
	}
	public function isVtt($s){
		return strtoupper(substr(trim($s),0,6))== 'WEBVTT'?true:false;
	}
	public function isSrt($s){
		return substr(trim($s),0,2)== "1\n"?true:false;
	}
	public function isXml($s){
		return substr(trim($s),0,5)== "<?xml"?true:false;
	}
	public function type($s){
		return $this->isVtt($s)?'vtt':($this->isASS($s)?'ass':($this->isLrc($s)?'lrc':'srt'));
	}
	
	private function _duration($seconds_count)
	{
		$delimiter  = ':';
		$seconds = $seconds_count*10 % 600/10;
		$a=explode('.',$seconds);
		$minutes = floor($seconds_count/60);
		$hours   = floor($seconds_count/3600);
		
		$seconds = str_pad($a[0], 2, "0", STR_PAD_LEFT).'.';
		@$seconds.= str_pad($a[1], 3, "0", STR_PAD_RIGHT);
		$minutes = str_pad($minutes, 2, "0", STR_PAD_LEFT).$delimiter;

		$hours = str_pad($hours, 2, "0", STR_PAD_LEFT).$delimiter;
		return "$hours$minutes$seconds";
	}
	private function _t($t){
		return $this->_duration($t/10);
	}
	private function _time2seconds($t){
		$ar=explode(':',$t);
		return $ar[0]*3600+$ar[1]*60+$ar[2];
	}
	private function _timeSrt2Lrc($t){
		$a=explode(':',$t);
		$h=$a[0];
		$m=$a[1];
		$s=$a[2];
		$a=explode(',',$s);
		$s=$a[0];
		$ms=substr($a[1],0,2);
		return ($h*60+$m).':'.$s.'.'.$ms;
			
	}
	
	
	public function Ass2Srt($s){
		 return $this->Vtt2Srt($this->Ass2Vtt($s));
	}
	public function Ass2Vtt($s){
		require_once('lib/AssFile.php');
		require_once('lib/WebVTT.php');
		$AssFile=\LibPHPAss\AssFile::loadFromString($s);
		$WebVTT=new \LibPHPAss\WebVTT($AssFile->getEvents());
		return $WebVTT->toString();
	}
	
	public function Lrc2Ass($s){
		return $this->Vtt2Ass($this->Lrc2Vtt($s),'lrc');
	}
	public function Lrc2Srt($s){
		return $this->Vtt2Srt($this->Lrc2Vtt($s),'lrc');
	}
	public function Lrc2Txt($lrc){
		return preg_replace('/\[.*?\]/','',$lrc);
	}
	public function Lrc2Vtt($s){
		require_once('lib/Lrc.php');
		$Lrc=new Lrc();
		$ar=$Lrc->get($s);
		$ar=$ar['ar'];
		
		ksort($ar);
		$k=1;$j=1;$l='';$n='';$len=count($ar);$a=array('WEBVTT'."\n\n");
		foreach ($ar as $t=>$v){
			if($k==1){
				//$a[]=$k."\n".$this->_t($t).' --> ';
				$a[]=$this->_t($t).' --> ';
				$l=$v;
			}else{
				if($l){
					$a[]=@$n.$this->_t($t)."\n".str_replace('|',"\n",$l);
				}else{
					//$k--;	
				}
					//$a[]=$this->_t($t)."\n".$l.($j!=$len?"\n\n".$k."\n".$this->_t($t).' --> ':'');
					$n=($j!=$len?"\n\n".$this->_t($t).' --> ':'');
				
				$l=$v;	
			}
			$k++;
			$j++;
		}
		return implode('',$a);
	}
	
	public function Srt2Ass($s){
		return $this->Vtt2Ass($this->Srt2Vtt($s),'srt');
	}
	public function Srt2Lrc($sub){
		@define('SRT_STATE_SUBNUMBER', 0);
		@define('SRT_STATE_TIME',      1);
		@define('SRT_STATE_TEXT',      2);
		@define('SRT_STATE_BLANK',     3);
		$ar=explode("\n",$sub);
		unset($sub);
		$subs=array();
		$state=SRT_STATE_SUBNUMBER;
		$subNum=0;
		$subText='';
		$subTime='';
		$t=count($ar)-1;
		$time='';
		foreach($ar as $k=>$line) {
			switch($state) {
				case SRT_STATE_SUBNUMBER:
					$state=SRT_STATE_TIME;
					break;
				case SRT_STATE_TIME:
					$subTime= @trim($line);
					$ar=explode(' --> ',$subTime);
					$t0='['.$this->_timeSrt2Lrc($ar[0]).']';
					$t1='['.$this->_timeSrt2Lrc($ar[1]).']';
					
					if($time){
						if($t0!==$time){
							$subs[]=$time;
							$subs[]="\n";
						}
						$subs[]=$t0;
					}else{
						$subs[]=$t0;
					}
					$time=$t1;
					
					$state=SRT_STATE_TEXT;
					break;
				case SRT_STATE_TEXT:
					$subs[]=trim($line);
					if (trim($line)=='') {
						$state=SRT_STATE_SUBNUMBER;
						$subs[]="\n";
					}
					break;
			}
		}
		unset($ar);
		return implode('',$subs);
	}
	public function Srt2Txt($sub){
		@define('SRT_STATE_SUBNUMBER', 0);
		@define('SRT_STATE_TIME',      1);
		@define('SRT_STATE_TEXT',      2);
		@define('SRT_STATE_BLANK',     3);
		/*
		$ar=explode("\n\n\n",$sub);
		$l=count($ar);
		if($l>3){
			for($j=0; $j<$l; $j+=2){ 
				if($ar[$j]=='简'){
					$sub=$ar[$j+1];	
				}
			}
		}
		unset($ar);
		*/
		$ar=explode("\n",$this->_covert_sign($sub));
		unset($sub);
		$subs    = array();
		$state   = SRT_STATE_SUBNUMBER;
		$subNum  = 0;
		$subText = '';
		$subTime = '';
		$t=count($ar)-1;
		//print_r($ar);
		foreach($ar as $k=>$line) {
			switch($state) {
				case SRT_STATE_SUBNUMBER:
					//$subNum = @trim($line);
					$state  = SRT_STATE_TIME;
					break;
		
				case SRT_STATE_TIME:
					//$subTime = @trim($line);
					$state   = SRT_STATE_TEXT;
					break;
		
				case SRT_STATE_TEXT:
					if (trim($line) == '') {
						/*$sub = new stdClass;
						$sub->number = $subNum;
						list($sub->startTime, $sub->stopTime) = explode(' --> ', $subTime);
						$sub->text   = $subText;*/
						$sub = $subText;
						$subText     = '';
						$state       = SRT_STATE_SUBNUMBER;
						$subs[]      = $sub;
					} else {
							$len = strlen($line)-1;
							$subText .= preg_replace('/\{\\\(.*?)\}/i', '',$line.(preg_match('/[^0-9a-zA-Z一-龥]/u',substr($line,-3))||preg_match('/[^0-9a-zA-Z一-龥]/u',substr($line,-1))?'<br>　　':($t==$k?'。':'，')));//去除ASS动画效果标记
							if($t==$k){$subs[]=$subText.'<br>';}
					}
					break;
			}
		}
		unset($ar);
		return '　　'.implode('',$subs);
	}
	public function Srt2Vtt($s){
		$ar=explode("\n\n",$s);
		foreach($ar as $k=>$v) {
			$aj=explode("\n",$v);
			$aj[0]='';
			$aj[1]=str_replace(',','.',$aj[1]);
			$ar[$k]=implode("\n",$aj);
		}
		$s='WEBVTT'."\n".implode("\n",$ar);
		return $s;
	}
	
	public function Vtt2Srt($s){
		$ar=explode("\n",$s);
		if($ar[0]=='WEBVTT'){
			$i=0;
			foreach($ar as $k=>$line) {
				if(stristr($line,' --> ')){
					$i++;
					$aj=explode(' ',$line);
					$ar[$k]=$i."\n".str_replace('.',',',$aj[0]).' '.$aj[1].' '.str_replace('.',',',$aj[2]);
				}
			}
			unset($ar[0]);
			if($ar[1]=='')unset($ar[1]);
			$s=implode("\n",$ar);
		}
		return $s;
	}
	public function Vtt2Ass($s,$from=''){
		$ar=explode("\n",$s);
		if($ar[0]=='WEBVTT'){
			$i=0;
			foreach($ar as $k=>$line) {
				if(stristr($line,' --> ')){
					$i++;
					$aj=explode(' ',$line);
					
					$d=$this->_time2seconds($aj[2])-$this->_time2seconds($aj[0]);
					$ar[$k]='Dialogue: 0,'.$aj[0].','.$aj[2].',Default,,0,0,0,,{\fad('.(300).', '.(300).')}'.($from=='lrc'?'{\K'.round($d*1000).'}':'');
				}elseif($k>0){
					if(@$line){
						if(@$ar[$k+1]){
							$ar[$k].='\N';
						}else{
							$ar[$k].="\n";
						}
					}
				}
			}
			$ar[0]='[Script Info]
ScriptType: v4.00+
WrapStyle: 0
ScaledBorderAndShadow: yes
YCbCr Matrix: TV.601
PlayResX: 1008
PlayResY: 567
	
[V4+ Styles]
Format: Name, Fontname, Fontsize, PrimaryColour, SecondaryColour, OutlineColour, BackColour, Bold, Italic, Underline, StrikeOut, ScaleX, ScaleY, Spacing, Angle, BorderStyle, Outline, Shadow, Alignment, MarginL, MarginR, MarginV, Encoding
Style: Default,微软雅黑,35,&H00FFFFFF,&H004200FF,&H009966FF,&H00000000,-1,0,0,0,100,110,3,0,1,1,1,2,10,10,35,1

[Events]
Format: Layer, Start, End, Style, Name, MarginL, MarginR, MarginV, Effect, Text'."\n";
			if($ar[1]=='')unset($ar[1]);
			$s=implode('',$ar);
		}
		return $s;
	}
	
	public function Xml2Lrc($s){
		$_a=simplexml_load_string($s);
		$_b=array();$_c=array();
		if($_a->chat){//NICO的XML转换
			foreach($_a->chat as $_k=>$_v){
				$_b[floor($_v['vpos'])]=$_v[0];
			}
			ksort($_b);
			foreach($_b as $_k=>$_v){
				$_m=floor(($_k/100/60));
				$_s=($_k/100-$_m*60);
				$_d=explode('.',$_s);
				$_c[]='['.sprintf("%02d",$_m).':'.sprintf("%02d",$_d[0]).'.'.$_d[1].']'.$_v;
			}
			return implode("\r\n",$_c);
		}else{
			return FALSE;
		}
	}
	
	public function utf8($data){
		if(!empty($data)){
			$fileType = mb_detect_encoding($data,array( 
				'GB2312','GBK','UTF-16','UTF-8','BIG5','ASCII'
			)) ;
			if($fileType===FALSE){
				exit(json_encode(array("success"=>false,"preventRetry" => true,"error"=>"字幕需UTF-8编码！")));
			}elseif($fileType!=='UTF-8'){
				$data = mb_convert_encoding($data ,'UTF-8');
			}
		}
		return $data;
	}
	
	public function convert($s,$to){
		$s=str_replace("\r",'',$s);//去除windows系统的\r标签，只保留\n
		$s=$this->utf8($s);
		$t=$this->type($s);
		switch($t)
		{
			case 'vtt':
				if($to=='vtt'){

				}elseif($to=='ass'){
					$s=$this->Vtt2Ass($s);	
				}elseif($to=='lrc'){
					$s=$this->Srt2Lrc($this->Vtt2Srt($s));
				}elseif($to=='srt'){
					$s=$this-Vtt2Srt($s);
				}elseif($to=='txt'){
					$s=$this->Srt2Txt($this->Vtt2Srt($s));
				}
			break;
			case 'ass':
				if($to=='vtt'){
					$s=$this->Ass2Vtt($s);	
				}elseif($to=='ass'){
					
				}elseif($to=='lrc'){
					$s=$this->Srt2Lrc($this->Ass2Srt($s));
				}elseif($to=='srt'){
					$s=$this->Ass2Srt($s);
				}elseif($to=='txt'){
					$s=$this->Srt2Txt($this->Ass2Srt($s));
				}
			break;
			case 'lrc':
				if($to=='vtt'){
					$s=$this->Lrc2Vtt($s);
				}elseif($to=='ass'){
					$s=$this->Lrc2Ass($s);
				}elseif($to=='lrc'){
	
				}elseif($to=='srt'){
					$s=$this->Lrc2Srt($s);
				}elseif($to=='txt'){
					$s=$this->Lrc2Txt($s);
				}
			break;
			case 'srt':
				if($to=='vtt'){
					$s=$this->Srt2Vtt($s);
				}elseif($to=='ass'){
					$s=$this->Srt2Ass($s);
				}elseif($to=='lrc'){
					$s=$this->Srt2Lrc($s);
				}elseif($to=='srt'){
	
				}elseif($to=='txt'){
					$s=$this->Srt2Txt($s);
				}
			break;
			default:;
		}
		return $s;
	}
}
?>