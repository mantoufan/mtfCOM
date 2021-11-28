<?php
class mtfColor{
	private $ColorThief;
	public function rgb2hsv($ar) // RGB Values:Number 0-255
	{// HSV Results:Number 0-1
	   $R=$ar['r'];$G=$ar['g'];$B=$ar['b'];
	   $HSL = array();
	   $var_R = ($R / 255);
	   $var_G = ($G / 255);
	   $var_B = ($B / 255);
	   $var_Min = min($var_R, $var_G, $var_B);
	   $var_Max = max($var_R, $var_G, $var_B);
	   $del_Max = $var_Max - $var_Min;
	   $V = $var_Max;
	   if ($del_Max == 0)
	   {
		  $H = 0;
		  $S = 0;
	   }
	   else
	   {
		  $S = $del_Max / $var_Max;
		  $del_R = ( ( ( $var_Max - $var_R ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
		  $del_G = ( ( ( $var_Max - $var_G ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
		  $del_B = ( ( ( $var_Max - $var_B ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
		  if      ($var_R == $var_Max) $H = $del_B - $del_G;
		  else if ($var_G == $var_Max) $H = ( 1 / 3 ) + $del_R - $del_B;
		  else if ($var_B == $var_Max) $H = ( 2 / 3 ) + $del_G - $del_R;
		  if ($H<0) $H++;
		  if ($H>1) $H--;
	   }
	   $HSL['h'] = $H*180;
	   $HSL['s'] = $S*255;
	   $HSL['v'] = $V*255;
	   return $HSL;
	}
	public function hsv2rgb($hsv)
	{
		$h = (integer)$hsv['h'];
		$s = (integer)$hsv['s'];
		$v = (integer)$hsv['v'];
		$s /= 256.0;
		if ($s == 0.0) return array($v,$v,$v);
		$h /= (256.0 / 6.0);
		$i = floor($h);
		$f = $h - $i;
		$p = (integer)($v * (1.0 - $s));
		$q = (integer)($v * (1.0 - $s * $f));
		$t = (integer)($v * (1.0 - $s * (1.0 - $f)));
		switch($i) {
			case 0: return array('r'=>$v,'g'=>$t,'b'=>$p);
			case 1: return array('r'=>$q,'g'=>$v,'b'=>$p);
			case 2: return array('r'=>$p,'g'=>$v,'b'=>$t);
			case 3: return array('r'=>$p,'g'=>$q,'b'=>$v);
			case 4: return array('r'=>$t,'g'=>$p,'b'=>$v);
			default: return array('r'=>$v,'g'=>$p,'b'=>$q);
		}
	}
	public function rec($_f_p,$_type='hsv'){
		$_root=str_replace('\\','/',dirname(__file__)).'/';
		include($_root.'../ColorThief/autoload.php');
		$_rgb=ColorThief\ColorThief::getColor($_f_p);
		if($_type==='hsv'){
			$_color=$this->rgb2hsv(array('r'=>$_rgb[0],'g'=>$_rgb[1],'b'=>$_rgb[2]));
		}else{
			$_color=array('r'=>$_rgb[0],'g'=>$_rgb[1],'b'=>$_rgb[2]);
		}
		
		return $_color;
	}
}

?>