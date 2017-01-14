<?php
spl_autoload_register(function($class){
	$prefix = "MofgForm\\";
	$base_dir = __DIR__."/src/";
	$len = strlen($prefix);
	if( strncmp($prefix, $class, $len) !== 0 ) return;
	$file = $base_dir.str_replace("\\", "/", substr($class, $len)).".php";
	require($file);
});
