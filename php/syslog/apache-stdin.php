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
 * Will separate a log format into a pattern for decode variables
 *
 * @param string $format
 * @return array [0] = keys, [1] = regex pattern
 */
function convertLogIntoPattern($format) {
	//get all the patterns
	$items = preg_split('/\s+/', $format);
	
	//common names for logs
	$keys = array(
		'%a' => 'remote_ip',
		'%A' => 'local_ip',
		'%B' => 'bytes_response',
		'%b' => 'bytes_response_clf',
		'%D' => 'time_ms',
		'%f' => 'filename',
		'%h' => 'remote_host',
		'%H' => 'request_protocol',
		'%k' => 'keepalive',
		'%l' => 'remote_logname',
		'%m' => 'request_method',
		'%p' => 'port',
		'%P' => 'process_id',
		'%q' => 'query',
		'%r' => 'first_line_request',
		'%R' => 'handler',
		'%s' => 'status',
		'%>s' => 'status_last',
		'%t' => 'time',
		'%T' => 'time',
		'%u' => 'remote_user',
		'%U' => 'path',
		'%v' => 'hostname',
		'%V' => 'hostname_canonical',
		'%X' => 'connection_status',
		'%I' => 'bytes_received',
		'%O' => 'bytes_sent',
	);
	
	$pattern = array();
	$keys_used = array();
	
	$last_item = end($items);
	foreach($items as $item) {
		//check the key name
		$key = null;
		$item_clean = str_replace('\"', "", $item);
		
		if (isset($keys[$item_clean])) {
			$key = $keys[$item_clean];
			$keys_used[] = $key;
		}
		
		$add_space = true;		
		if ($item == $last_item)
			$add_space = false;
		
		if ($key == null) {
			$pattern[] = '[^\s]+'; //just pass the item without any processing
		} else if ($item == '%t') {
			$pattern[] = '[([^\]]+)\]';
		} else if (strpos($item, '\"') !== false) {
			$pattern[] = '"([^"]+)"';
		} else {
			$pattern[] = '([^\s]+)';//normal
		}
				
		if ($add_space) {
			//add the space
			$pattern[] = '\s';
		}
	}
	
	$pattern_log = '/^'.implode("", $pattern).'/U';
	return array($keys_used, $pattern_log);
		
}

$fp = fopen('php://stdin', 'r');
$type = isset($argv[1])?$argv[1]:'log';

$fp_out = fopen('/tmp/check-'.$type, 'w');
if ($type == 'log') {
	$format = '%v %A %D \"%r\" %>s %O \"%{Referer}i\" \"%{User-Agent}i\"';
	list($keys, $pattern) = convertLogIntoPattern($format);
} else {
	$pattern = '/^\[([^\]]+)\]\s\[([^\]]+)\]\s(.*)$/U';
	$keys = array('date', 'priority', 'message');
}

do {
	//read a line from apache, if not, will block until have it
	$data = fgets($fp);
	$data = trim($data); //remove line end
	
	if (empty($data)) {
		break; //no more data so finish it
	}
	
	$matches = array();
	if (preg_match($pattern, $data, $matches)) {
		$found = array();
		foreach($keys as $index => $key) {
			$found[$key] = $matches[$index+1];
		}
		
	}
	
	//just write to output, you can process here and sent to db, alerting, etc
	fwrite($fp_out, "{$type}: {$data} - ".print_r($found, true)."\n");	
} while(true);

fclose($fp);
fclose($fp_out);