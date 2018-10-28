<?php
//$salt = mcrypt_create_iv(16, MCRYPT_DEV_URANDOM);
$salt = 'м :7WhрФcЦ»|bб';

return new \Phalcon\Config(array(
	'database' => array(
		'adapter'  => 'Mysql',
		'host'     => 'localhost',
		'username' => 'tvz',
		'password' => 'E719zbRY',
		'name'     => 'client_tvz',
	),
	'application' => array(
		'controllersDir' => __DIR__ . '/../../app/controllers/',
		'modelsDir'      => __DIR__ . '/../../app/models/',
		'viewsDir'       => __DIR__ . '/../../app/views/',
		'libraryDir'     => __DIR__ . '/../../app/library/',
		'helpersDir'     => __DIR__ . '/../../app/helpers/',
		'baseDir'		 => __DIR__,
		'baseUri'        => '/',
	),
	'options' => array(
		'salt' 				=> $salt,
		'admin_pass' 		=> hash_pbkdf2('sha256','8yNR3inaki',$salt, 100),//'46058a92372d43b8bfc9e8fd7fb22fac86a32a1bee283586fb33e612a7bdd860',//hash_pbkdf2('sha256','oF8hh58CiLYz',$salt, 100),
		'debug_sql' 		=> false,
		'date_format' 		=> 'Y-m-d',
		'time_format'		=> 'H:i:s',
		'default_timezone'	=> new DateTimeZone('Europe/Moscow'),
		'api_key'			=> '1e0ea8aa4aaa21c40424b8e1fde30d96'
	)
));