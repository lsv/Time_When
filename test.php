<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('xdebug.var_display_max_depth', 100);

require 'Time_When.php';

$timers = array(
	array('endtimer' => time(), 'starttime' => time()-(60*60*4), 'id' => 'in 4 hours'),
	array('endtimer' => time(), 'starttime' => time()+(60*60*4), 'id' => '4 hours ago'),
);

Time_When::getInstance()->addTimer($timers);

$res = $obj = Time_When::getInstance()->getResult();
var_dump($res);