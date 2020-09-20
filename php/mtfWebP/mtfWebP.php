<?php 
/**
 * mtfWebP WebP format process module
 * Author: Shon Ng
 */
class mtfWebP{
    private $_bin = array(
        'cwebp' => '',
        'dwebp' => '',
        'gif2webp' => '',
    );
    public function __construct()
    {	
        $_root = str_replace('\\', '/', dirname(__file__)) . '/';
        foreach ($this->_bin as $bin => $v) {
            $this->_bin[$bin] = $_root . 'bin/WIN32/WebP/' . $bin;
        }
    }

    /**
     * Convert the image to WebP format
     * @param string $src File path of the source image.
     * @param string $des File path where to save the image.
     * @param number $quality Quality of image. Accepts number 0 - 100 where 0 is lowest and 100 is the highest quality. Or 75 for default.
     */
    public function convert($src, $des, $quality = 75) {
        $code = 200; $msg = 'success';
        $res = getimagesize($src);
        $res = exec($this->_bin[isset($res['mime']) && $res['mime'] === 'image/gif' ? 'gif2webp' : 'cwebp'] . ' ' . $src . ' -o '. $des . ' -q '. $quality);
        if(stripos(end($res), 'Error!')) {
            $code = -1;
            $msg = end($res);
        }
        return array(
            'code' => $code,
            'msg' => $msg
        );
    }

    /**
     * WebP is Supported by the Agent
     * @return boolean true - support false - unsupport
     */
    public function isSupport() {
        return stristr($_SERVER['HTTP_ACCEPT'], 'image/webp');
    }
}
?>