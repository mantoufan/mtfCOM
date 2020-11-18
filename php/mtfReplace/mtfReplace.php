<?php
/** mtf替换类 作者：小宇 */
class mtfReplace {
    /** 指定位置埋点（区分是否已替换） */
    private function spacer($ext, $md5) {
        switch ($ext) {
            case 'js':
            case 'css':
            case 'wxss':
            case 'ttss':
            case 'qss':
            case 'acss':
                return "\n" . '/*yzhan:' . $md5 . '*/' . "\n";
            break;

        }
        return '';
    }
    /** 注释内容 */
    private function comment($ext, $md5, $content) {
        switch ($ext) {
            case 'js':
            case 'css':
            case 'wxss':
            case 'ttss':
            case 'qss':
            case 'acss':
                return '/*mzhan:' . $md5 . $content . '*/';
            break;
        }
        return '';
    }
    /** 追加（已追加则修改）内容到指定位置 */
    public function append($rules, $is_replace = false) {
        foreach ($rules as $path => $rule) {
            if (is_file($path)) {
                $content = file_get_contents($path);
                $info = pathinfo($path);
                $ext = $info['extension'];
                foreach ($rule as $pattern => $replace) {
                    $md5 = md5($pattern);
                    $spacer = $this->spacer($ext, $md5);
                    if ($spacer === '') {
                        $content = preg_replace($pattern, $replace, $content);
                    } else if (stripos($content, $spacer) === FALSE) {
                        $content = str_replace($pattern, ($is_replace ? $this->comment($ext, $md5, $pattern) : $pattern) . $spacer . $replace . $spacer, $content);
                    } else {
                        $spacer = addcslashes(addcslashes($spacer, '*'), '/');
                        $content = preg_replace('/(' . $spacer . ').*?(' . $spacer . ')/', $replace ? '$1' . $replace . '$2' : '', $content);
                        if ($is_replace && $replace === '') {
                            $content = str_replace($this->comment($ext, $md5, $pattern), $pattern, $content);
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