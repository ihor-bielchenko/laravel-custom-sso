<?php

function _api(string $type ='')
{
	$protocol = env($type .'_PROTOCOL');
	$path = env($type .'_PATH');
	$port = env($type .'_PORT');

	return $protocol .'://'. $path .':'. ($port ?? 80);
}
