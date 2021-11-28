<?php
$id = $_POST['id'];
t($id.' deploying...');
$tmp_name = $_FILES['upload']['tmp_name'];
$zip_path = './'.$id.'/tmp.zip';
if ($id && $tmp_name) {
  rename($tmp_name, $zip_path);
	$zip = new ZipArchive;
	if ($zip->open($zip_path) === TRUE)
	{
		$zip->extractTo('./'.$id);
		$zip->close();
	}
	unlink($zip_path);
	t('success');
	exit;
}
t('failed');
function t($t) {
	echo $t."\r\n";
}
?>