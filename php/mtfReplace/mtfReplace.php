<?php
class mtfReplaceUtil {
    private $ext = '';
    private $md5 = '';
    function __construct($ext, $md5) {
        $this->ext = $ext;
        $this->md5 = $md5;
    }
    /** 指定位置埋点（区分是否已替换） */
    public function spacer() {
        switch ($this->ext) {
            case 'js':
            case 'css':
                return "\n" . '/*yzhan:' . $this->md5 . '*/' . "\n";
            break;
        }
        return '';
    }
    /** 注释内容 */
    public function comment($content) {
        switch ($this->ext) {
            case 'js':
            case 'css':
                return '/*mzhan:' . $this->md5 . $content . '*/';
            break;
        }
        return '';
    }
}
/** mtf替换类 作者：小宇 */
class mtfReplace {
    /** 追加（已追加则修改）内容到指定位置 */
    public function append($rules, $is_replace = false) {
        foreach ($rules as $path => $rule) {
            if (is_file($path)) {
                $content = file_get_contents($path);
                $info = pathinfo($path);
                $ext = $info['extension'];
                foreach ($rule as $pattern => $replace) {
                    $md5 = md5($pattern);
                    $util = new \mtfReplaceUtil($ext, $md5);
                    $spacer = $util->spacer();
                    if (stripos($content, $spacer) === FALSE) {
                        $content = str_replace($pattern, ($is_replace ? $util->comment($pattern) : $pattern) . $spacer . $replace . $spacer, $content);
                    } else {
                        $spacer = addcslashes(addcslashes($spacer, '*'), '/');
                        $content = preg_replace('/(' . $spacer . ').*?(' . $spacer . ')/', $replace ? '$1' . $replace . '$2' : '', $content);
                        if ($is_replace && $replace === '') {
                            $content = str_replace($util->comment($pattern), $pattern, $content);
                        }
                    } 
                }
                file_put_contents($path, $content);
            } 
        }
    }
    /** 替换指定位置的内容，原始内容可复原 */
    public function replace($rules) {
        $this->append($rules, true);
    }
}
?>