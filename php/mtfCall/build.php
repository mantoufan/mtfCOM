<?php
if (file_exists('mtfCall.phar')) {
    unlink('mtfCall.phar');
}
$phar = new Phar('mtfCall.phar');
$phar->buildFromDirectory(dirname(__FILE__) . '/../mtfCall');
$phar->setStub($phar->createDefaultStub('mtfCall.php'));
$phar->compressFiles(Phar::GZ);
deleteFiles(array('build.php', 'mtfCall.exe', 'favicon.bmp', 'favicon.ico'));
function deleteFiles ($files) {
    global $phar;
    foreach($files as $k => $file) {
        if (file_exists($file)) {
            $phar->delete($file);
        }
    }
}
?>