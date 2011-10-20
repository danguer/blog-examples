<?php
//video id by default
$video_id = 'v6xU96KLBL4';

if (isset($_REQUEST['video_id']))
        $video_id = trim($_REQUEST['video_id']);

//get vars from video
$url_info = 'http://www.youtube.com/get_video_info?video_id='.$video_id;
$info = file_get_contents($url_info);

$vars = array();
parse_str($info, $vars);

if ($vars['status'] == 'ok') {
	$vars_fmt = array();
	parse_str($vars['url_encoded_fmt_stream_map'], $vars_fmt);

	print "FLV: {$vars_fmt['url']}";
} else 
	print "Error: {$vars['reason']}";