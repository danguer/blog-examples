<?php

/*
Copyright (c) 2011, Daniel Guerrero
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:
    * Redistributions of source code must retain the above copyright
      notice, this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright
      notice, this list of conditions and the following disclaimer in the
      documentation and/or other materials provided with the distribution.
    * Neither the name of the Daniel Guerrero nor the
      names of its contributors may be used to endorse or promote products
      derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL DANIEL GUERRERO BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/*
 * generate a signature to upload the file,
 * you must configure aws sdk:
 * http://aws.amazon.com/sdkforphp/
 * to work
 */ 

$op = isset($_GET['op'])?$_GET['op']:'';

if ($op == 'signature') {
	//init aws-sdk
	require_once 'sdk.class.php';
	
	//response will be in json
	header('Content-Type: application/json');	
	
	$bucket = 'danguer-blog'; //your s3 bucket
	$bucket_path = 'upload-html5/tmp'; //"dir" where is going to be stored inside the bucket
	$filename = isset($_POST['name'])?$_POST['name']:null;
	$filemime = isset($_POST['mime'])?$_POST['mime']:null;
	
	//handle errors
	if (empty($filename)) {		
		print json_encode(array(
			'error' => 'must provide the filename',
		));
		return;		
	}
	
	if (empty($filemime)) {		
		print json_encode(array(
			'error' => 'must provide the mime',
		));
		return;		
	}
	
	if (strpos($filename, '..') !== false) {
		print json_encode(array(
			'error' => 'not relative paths',
		));
		return;
	}
	
	$expires = '+15 minutes'; //token will be valid only for 15 minutes
	$path_file = "{$bucket_path}/{$filename}";
	$mime = $filemime; //help the browsers to interpret the content	
	
	//get the params for s3 to upload directly
	$s3_uploader = new S3BrowserUpload();
	$params = $s3_uploader->generate_upload_parameters($bucket, $expires, array(
		'acl' => AmazonS3::ACL_PUBLIC,
		'key' => $path_file,
		'Content-Type' => $mime,	
	));
	
	print json_encode(array(
		'error' => '',
		'url' => "http://{$params['form']['action']}",
		'params' => $params['inputs']
	));
	return;
}


//params for index.php
$url_iframe = 'http://danguer-blog.s3.amazonaws.com/upload-html5/index.html'; //setup where is stored your s3 files 
$url_iframe_host = parse_url($url_iframe);
$url_iframe_host = $url_iframe_host['host'];
?>

<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Example of direct upload through postmessage</title>
	
	<!-- load jquery -->
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>
	
	<script type="text/javascript">
		//ajax url to get signature
		var url_ajax_signature = window.location.href+'?op=signature';

		function resizeImage(file, mime) {
			var canvas = document.createElement("canvas"); 
		    var ctx = canvas.getContext("2d");
		    var canvasCopy = document.createElement("canvas");
		    var ctxCopy = canvasCopy.getContext("2d");

			var reader = new FileReader(); 
		    reader.onload = function() {
			    var image = new Image();
			    image.onload = function() {
				    //scale just at half
				    canvasCopy.width = image.width;
			        canvasCopy.height = image.height;
			        ctxCopy.drawImage(image, 0, 0);
	
			        canvas.width = image.width * 0.5;
			        canvas.height = image.height * 0.5;
			        ctx.drawImage(canvasCopy, 0, 0, canvasCopy.width, canvasCopy.height, 0, 0, canvas.width, canvas.height);
	
			        //convert into image and get as binary base64 encoded
			        //to pass through postMessage
			        var url = canvas.toDataURL(mime, 0.80);
			        uploadImage(file, url)
			    }

		        image.src = reader.result;
		    };

			//read contents
			reader.readAsDataURL(file);
		}

		function uploadImage(file, dataURL) {
			//load signature
			var signature_params = {
				mime: file.type,
				name: file.name
			};
			
			$.post(url_ajax_signature, signature_params, function(response) {
				//send through crossdomain page
				var windowFrame = document.getElementById('postMessageFrame').contentWindow ;
				var data = {
					params: response.params,
					url: response.url,
					content: dataURL
				}

				//send data of s3 request signature and base64 binary data
				windowFrame.postMessage(data, 'http://<?=$url_iframe_host?>');		
			}, 'json');
		}			

		function uploadFile(files) {
			var file = files[0];

			resizeImage(file, file.type);
		}
		 
	</script>
	
	<!-- hiden frame -->
	<iframe id="postMessageFrame" src="<?=$url_iframe?>">
	</iframe>
	
	<h3>Upload Files</h3>
	<input type="file" accept="image/*" onchange="uploadFile(this.files)">	
</head>
<body>
<!-- this will only serve as a container for postmessage params -->
</body>
</html>