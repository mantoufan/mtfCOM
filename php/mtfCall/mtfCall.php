<?php
class mtfCall {
    private $_root;
    private $calls = array(
        'qrcode'=>'qrcode/qrcode',
        'img'=>'img/convert',
        'scan'=>'img/scan',
    );
	public function __construct()
    {	
		$_root = dirname(__file__).'/';
		$this->_root = $_root;
    }
    public function call($name, $command) {
        exec('C:/Windows/mtfCallBin/' . $this->calls[$name] . ' ' .  $command, $res);
        return $res;
    }
}
?>