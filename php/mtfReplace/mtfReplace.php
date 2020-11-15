<?php
/** mtf替换类 作者：小宇 */
class mtfReplace {
    /** 指定位置埋点（区分是否已替换） */
    private function spacer($ext, $md5) {
        switch ($ext) {
            case 'js':
            case 'css':
                return "\n" . '/*yzhan:' . $md5 . '*/' . "\n";
            break;
        }
        return '';
    }
    /** 追加（已追加则修改）内容到指定位置 */
    public function append($rules) {
        foreach ($rules as $path => $rule) {
            if (is_file($path)) {
                $content = file_get_contents($path);
                $info = pathinfo($path);
                $ext = $info['extension'];
                foreach ($rule as $pattern => $replace) {
                    $md5 = md5($pattern);
                    $spacer = $this->spacer($ext, $md5);
                    if (stripos($content, $spacer) === FALSE) {
                        $content = str_replace($pattern, $pattern . $spacer . $replace . $spacer, $content);
                    } else {
                        $spacer = addcslashes(addcslashes($spacer, '*'), '/');
                        $content = preg_replace('/(' . $spacer . ').*?(' . $spacer . ')/', $replace ? '$1' . $replace . '$2' : '', $content);
                    } 
                }
                file_put_contents($path, $content);
            } 
        }
    }
}
?>