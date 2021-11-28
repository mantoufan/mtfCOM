<?php  
include('mtf/mtfProxy/mtfProxy.php');
$mtfProxy=new mtfProxy();
$mtfProxy->cacheTime=360;
$mtfProxy->dir['data']='../..';
echo $mtfProxy->get($_GET['iptype'],array('html'=>1));
?>