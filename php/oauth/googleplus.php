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

/**
 * Show how to login through google plus with oauth
 */

$op = isset($_GET['op'])?$_GET['op']:'';

if ($op == 'redirect') {
	//process through javascript
	//you can store in a cookie, or pass through a post to avoid saving in history browser
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<body>
	<script type="text/javascript">
		window.location = (window.location.href).replace("?op=redirect#", '?op=token&');
	</script>
</body>
</html>
<?php 
} else if ($op == 'token') {
	//save token
	$access_token = $_GET['access_token'];
	
	//do something with the token, first check is real
	$data = @file_get_contents("https://www.googleapis.com/plus/v1/people/me?access_token={$access_token}");
	if ($data) {
		print $data;
	} else {
		print "Token not valid!";
	}
} else {	
	//redirect
	$client_id = 'your_client_id';
	$client_secret = 'your_client_secret';
	
	//needs to be the same as you provided in the console
	$url_redirect = 'your_uri_redirect';	
	
	$params = array(
		'client_id' => $client_id,
		'redirect_uri' => $url_redirect,
		'scope' => 'https://www.googleapis.com/auth/plus.me',
		'response_type' => 'token',
	);
	$url = "https://accounts.google.com/o/oauth2/auth?".http_build_query($params);
	
	//redirect
	header("Location: {$url}");
}
