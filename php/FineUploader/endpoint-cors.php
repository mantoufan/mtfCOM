<?php
/**
 * PHP Server-Side Example for Fine Uploader (traditional endpoint handler).
 * Maintained by Widen Enterprises.
 *
 * This example:
 *  - handles chunked and non-chunked requests
 *  - supports the concurrent chunking feature
 *  - assumes all upload requests are multipart encoded
 *  - handles delete requests
 *  - handles cross-origin environments
 *
 * Follow these steps to get up and running with Fine Uploader in a PHP environment:
 *
 * 1. Setup your client-side code, as documented on http://docs.fineuploader.com.
 *
 * 2. Copy this file and handler.php to your server.
 *
 * 3. Ensure your php.ini file contains appropriate values for
 *    max_input_time, upload_max_filesize and post_max_size.
 *
 * 4. Ensure your "chunks" and "files" folders exist and are writable.
 *    "chunks" is only needed if you have enabled the chunking feature client-side.
 *
 * 5. If you have chunking enabled in Fine Uploader, you MUST set a value for the `chunking.success.endpoint` option.
 *    This will be called by Fine Uploader when all chunks for a file have been successfully uploaded, triggering the
 *    PHP server to combine all parts into one file. This is particularly useful for the concurrent chunking feature,
 *    but is now required in all cases if you are making use of this PHP example.
 */


// Include the upload handler class
require_once "handler.php";

$uploader = new UploadHandler();

// Specify the list of valid extensions, ex. array("jpeg", "xml", "bmp")
$uploader->allowedExtensions = array(); // all files types allowed by default

// Specify max file size in bytes.
$uploader->sizeLimit = null;

// Specify the input name set in the javascript.
$uploader->inputName = "qqfile"; // matches Fine Uploader's default inputName value by default

// If you want to use the chunking/resume feature, specify the folder to temporarily save parts.
$uploader->chunksFolder = "chunks";

//$method = $_SERVER["REQUEST_METHOD"];
$method = getMethod();

// This will retrieve the "intended" request method.  Normally, this is the
// actual method of the request.  Sometimes, though, the intended request method
// must be hidden in the parameters of the request.  For example, when attempting to
// send a DELETE request in a cross-origin environment in IE9 or older, it is not
// possible to send a DELETE request.  So, we send a POST with the intended method,
// DELETE, in a "_method" parameter.
public function getMethod() {
    global $HTTP_RAW_POST_DATA;

    // This should only evaluate to true if the Content-Type is undefined
    // or unrecognized, such as when XDomainRequest has been used to
    // send the request.
    if(isset($HTTP_RAW_POST_DATA)) {
    	parse_str($HTTP_RAW_POST_DATA, $_POST);
    }

    if (isset($_POST["_method"]) && $_POST["_method"] != null) {
        return $_POST["_method"];
    }

    return $_SERVER["REQUEST_METHOD"];
}


private function _headers() {
    $headers = array();
    foreach($_SERVER as $key => $value) {
        if (substr($key, 0, 5) <> 'HTTP_') {
            continue;
        }
        $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
        $headers[$header] = $value;
    }
    return $headers;
}

public function setCorsHeader($allow_origin) {
    header('Access-Control-Allow-Origin: '.($allow_origin?((@$_SERVER['HTTPS']?'https':'http').'://'.$allow_origin):'*'));
	header('Access-Control-Allow-Methods: POST, DELETE');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Headers: Content-Type, X-Requested-With, Cache-Control');
}

// Determine whether we are dealing with a regular ol' XMLHttpRequest, or
// an XDomainRequest
public function getFrame(){
	$_HEADERS = _headers();
	$_iframe='';
	if (!isset($_HEADERS['X-Requested-With']) || $_HEADERS['X-Requested-With'] != "XMLHttpRequest") {
		$_iframe='<script>(function() {
		"use strict";
		var match = /(\{.*\})/.exec(document.body.innerHTML);
		if (match) {
			parent.postMessage(match[1], "*");
		}
		}());</script>';
	}
	return $_iframe;
}
?>
