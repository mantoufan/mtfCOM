<?php
class RandUA{
	/**
	 * Random user agent creator
	 * @since Sep 4, 2011
	 * @version 1.0
	 * @link http://360percents.com/
	 * @author Luka Pušić <pusic93@gmail.com>
	 */
	
	/**
	 * Sources:
	 * http://en.wikipedia.org/wiki/Usage_share_of_web_browsers#Summary_table
	 * http://statowl.com/operating_system_market_share_by_os_version.php
	 */
	private function _chooseRandomBrowserAndOS() {
		$frequencies = array(
			34 => array(
				89 => array('chrome', 'win'),
				9 => array('chrome', 'mac'),
				2 => array('chrome', 'lin')
			),
	
			32 => array(
				100 => array('iexplorer', 'win')
			),
	
			25 => array(
				83 => array('firefox', 'win'),
				16 => array('firefox', 'mac'),
				1 => array('firefox', 'lin')
			),
	
			7 => array(
				95 => array('safari', 'mac'),
				4 => array('safari', 'win'),
				1 => array('safari', 'lin')
			),
	
			2 => array(
				91 => array('opera', 'win'),
				6 => array('opera', 'lin'),
				3 => array('opera', 'mac')
			)
		);
	
		$rand = rand(1, 100);
		$sum = 0;
		foreach ($frequencies as $freq => $osFreqs) {
			$sum += $freq;
			if ($rand <= $sum) {
				$rand = rand(1, 100);
				$sum = 0;
				foreach ($osFreqs as $freq => $choice) {
					$sum += $freq;
					if ($rand <= $sum) {
						return $choice;
					}
				}
			}
		}
	
		throw new Exception("Frequencies don't sum to 100.");
	}
		
	
	private function _array_random(array $array) {
		return $array[array_rand($array, 1)];
	}
	
	private function _nt_version() {
		return rand(5, 6) . '.' . rand(0, 1);
	}
	
	private function _ie_version() {
		return rand(10, 11) . '.0';
	}
	
	private function _trident_version() {
		return rand(3, 5) . '.' . rand(0, 1);
	}
	
	private function _osx_version() {
		return "10_" . rand(5, 7) . '_' . rand(0, 9);
	}
	
	private function _chrome_version() {
		return rand(13, 15) . '.0.' . rand(800, 899) . '.0';
	}
	
	private function _presto_version() {
		return '2.9.' . rand(160, 190);
	}
	
	private function _presto_version2() {
		return rand(10, 12) . '.00';
	}
	
	private function _firefox($arch) {
		$ver = $this->_array_random(array(
			'Gecko/' . date('Ymd', rand(strtotime('2011-1-1'), time())) . ' Firefox/' . rand(5, 7) . '.0',
			'Gecko/' . date('Ymd', rand(strtotime('2011-1-1'), time())) . ' Firefox/' . rand(5, 7) . '.0.1',
			'Gecko/' . date('Ymd', rand(strtotime('2010-1-1'), time())) . ' Firefox/3.6.' . rand(1, 20),
			'Gecko/' . date('Ymd', rand(strtotime('2010-1-1'), time())) . ' Firefox/3.8'
		));
	
		switch ($arch) {
		case 'lin':
			return "(X11; Linux {proc}; rv:" . rand(5, 7) . ".0) $ver";
		case 'mac':
			$osx = $this->_osx_version();
			return "(Macintosh; {proc} Mac OS X $osx rv:" . rand(2, 6) . ".0) $ver";
		case 'win':
		default:
			$nt = $this->_nt_version();
			return "(Windows NT $nt; {lang}; rv:1.9." . rand(0, 2) . ".20) $ver";
	
		}
	}
	
	private function _safari($arch) {
		$saf = rand(531, 535) . '.' . rand(1, 50) . '.' . rand(1, 7);
		if (rand(0, 1) == 0) {
			$ver = rand(4, 5) . '.' . rand(0, 1);
		} else {
			$ver = rand(4, 5) . '.0.' . rand(1, 5);
		}
	
		switch ($arch) {
		case 'mac':
			$osx = $this->_osx_version();
			return "(Macintosh; U; {proc} Mac OS X $osx rv:" . rand(2, 6) . ".0; {lang}) AppleWebKit/$saf (KHTML, like Gecko) Version/$ver Safari/$saf";
		//case 'iphone':
		//    return '(iPod; U; CPU iPhone OS ' . rand(3, 4) . '_' . rand(0, 3) . " like Mac OS X; {lang}) AppleWebKit/$saf (KHTML, like Gecko) Version/" . rand(3, 4) . ".0.5 Mobile/8B" . rand(111, 119) . " Safari/6$saf";
		case 'win':
		default:
			$nt = $this->_nt_version();
			return "(Windows; U; Windows NT $nt) AppleWebKit/$saf (KHTML, like Gecko) Version/$ver Safari/$saf";
		}
	
	}
	
	private function _iexplorer($arch) {
		$ie_extra = array(
			'',
			'; .NET CLR 1.1.' . rand(4320, 4325) . '',
			'; WOW64'
		);
	
		$nt = $this->_nt_version();
		$ie = $this->_ie_version();
		$trident = $this->_trident_version();
		return "(compatible; MSIE $ie; Windows NT $nt; Trident/$trident)";
	}
	
	private function _opera($arch) {
		$op_extra = array(
			'',
			'; .NET CLR 1.1.' . rand(4320, 4325) . '',
			'; WOW64'
		);
	
		$presto = $this->_presto_version();
		$version = $this->_presto_version2();
	
		switch ($arch) {
		case 'lin':
			return "(X11; Linux {proc}; U; {lang}) Presto/$presto Version/$version";
		case 'win':
		default:
			$nt = $this->_nt_version();
			return "(Windows NT $nt; U; {lang}) Presto/$presto Version/$version";
		}
	}
	
	private function _chrome($arch) {
		$saf = rand(531, 536) . rand(0, 2);
		$chrome = $this->_chrome_version();
	
		switch ($arch) {
		case 'lin':
			return "(X11; Linux {proc}) AppleWebKit/$saf (KHTML, like Gecko) Chrome/$chrome Safari/$saf";
		case 'mac':
			$osx = $this->_osx_version();
			return "(Macintosh; U; {proc} Mac OS X $osx) AppleWebKit/$saf (KHTML, like Gecko) Chrome/$chrome Safari/$saf";
		case 'win':
		default:
			$nt = $this->_nt_version();
			return "(Windows NT $nt) AppleWebKit/$saf (KHTML, like Gecko) Chrome/$chrome Safari/$saf";
		}
	}
	
	/**
	 * Main function which will choose random browser
	 * @param  array $lang  languages to choose from
	 * @return string       user agent
	 */
	public function get(array $lang=array('zh-CN')) {
		list($browser, $os) = $this->_chooseRandomBrowserAndOs();
	
		$proc = array(
			'lin' => array('i686', 'x86_64'),
			'mac' => array('Intel', 'PPC', 'U; Intel', 'U; PPC'),
			'win' => array('foo')
		);
	
		switch ($browser) {
		case 'firefox':
			$ua = "Mozilla/5.0 " . $this->_firefox($os);
			break;
		case 'safari':
			$ua = "Mozilla/5.0 " . $this->_safari($os);
			break;
		case 'iexplorer':
			$ua = "Mozilla/5.0 " . $this->_iexplorer($os);
			break;
		case 'opera':
			$ua = "Opera/" . rand(8, 9) . '.' . rand(10, 99) . ' ' . $this->_opera($os);
			break;
		case 'chrome':
			$ua = 'Mozilla/5.0 ' . $this->_chrome($os);
			break;
		}
	
		$ua = str_replace('{proc}', $this->_array_random($proc[$os]), $ua);
		$ua = str_replace('{lang}', $this->_array_random($lang), $ua);
	
		return $ua;
	}
	
	public function getMobile() {
		$ar=array('Mozilla/5.0 (Linux; Android 5.0.1; GEM-702L Build/HUAWEIGEM-702L) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/37.0.0.0 Mobile Safari/537.36 MicroMessenger/6.3.18.800 NetType/WIFI Language/zh_TW',
		'Mozilla/5.0 (Linux; U; Android 4.1.2; zh-cn; XT885 Build/6.7.2_GC-385) AppleWebKit/533.1 (KHTML, like Gecko)Version/4.0 MQQBrowser/5.4 TBS/025440 Mobile Safari/533.1 MicroMessenger/6.2.4.51_rdf8da56.600 NetType/WIFI Language/zh_CN',
		'Mozilla/5.0 (iPhone; CPU iPhone OS 8_3 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Mobile/12F70 baiduboxapp/0_0.0.5.6_enohpi_6311_046/3.8_4C2%255enohPi/1099a/0E12BC204E06E175FD283E21BFE1661EE0A20B6CAFNTCGOKCPB/1',
		'Mozilla/5.0 (iPhone 5CGLOBAL; CPU iPhone OS 8_3 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Version/6.0 MQQBrowser/5.8 Mobile/12F70 Safari/8536.25',
		'Mozilla/5.0 (iPhone; CPU iPhone OS 8_3 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Version/8.0 Mobile/12F70 Safari/600.1.4',
		'Mozilla/5.0 (Linux; U; Android 4.4.4; zh-cn; MI 4LTE Build/KTU84P) AppleWebKit/534.24 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.24 T5/2.0 baiduboxapp/6.5.1 (Baidu; P1 4.4.4)',
		'Mozilla/5.0 (Linux; U; Android 4.4.4; zh-cn; MI 4LTE Build/KTU84P) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/39.0.0.0 Mobile Safari/537.36 XiaoMi/MiuiBrowser/2.1.1',
		'Mozilla/5.0 (Linux; U; Android 4.4.4; zh-cn; MI 4LTE Build/KTU84P) AppleWebKit/537.36 (KHTML, like Gecko)Version/4.0 MQQBrowser/5.8 Mobile Safari/537.36',
		'Mozilla/5.0 (Linux; U; Android 4.4.4; zh-cn; HM NOTE 1LTE Build/KTU84P) AppleWebKit/534.24 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.24 T5/2.0 baiduboxapp/6.5 (Baidu; P1 4.4.4)',
		'Mozilla/5.0 (Linux; U; Android 4.4.4; zh-cn; HM NOTE 1LTE Build/KTU84P) AppleWebKit/537.36 (KHTML, like Gecko)Version/4.0 MQQBrowser/5.8 Mobile Safari/537.36',
		'Mozilla/5.0 (Linux; U; Android 4.1.1; zh-cn; SCH-N719 Build/JRO03C) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30',
		'Mozilla/5.0 (Linux; U; Android 4.4.2; zh-cn; X9180 Build/KVT49L) AppleWebKit/533.1 (KHTML, like Gecko)Version/4.0 MQQBrowser/5.4 TBS/025411 Mobile Safari/533.1 MicroMessenger/6.1.0.66_r1062275.542 NetType/WIFI',
		'Mozilla/5.0 (Linux; U; Android 4.2.1; zh-cn; 2013022 Build/HM2013022) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Mobile Safari/537.36 XiaoMi/MiuiBrowser/2.1.1',
		'Mozilla/5.0 (Linux; Android 4.1.1; Nexus 7 Build/JRO03D) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.166 Safari/535.19',
		'Mozilla/5.0 (Linux; U; Android 4.4.4; zh-CN; HM NOTE 1LTE Build/KTU84P) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 UCBrowser/10.2.0.535 U3/0.8.0 Mobile Safari/534.30');
		return $ar[array_rand($ar)];
	}
}
?>