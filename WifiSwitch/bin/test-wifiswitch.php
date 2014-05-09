<?php

	define('EXCEPTION_LOG_FILE', 'error_log');
	$_SERVER['SERVER_NAME'] = 'home.emberframework.com';
	require_once('/var/www/ember/system/include/common.inc.php');
	#Debug::enable();

	$ip = Site::getSetting('wifi_switch_ip'); 

	$switch = new WifiSwitch_Switch($ip, 1);

	echo 'State: '.$switch->getState().PHP_EOL;
	$switch->closeConnection();
	$switch->flipSwitch();
	$switch->closeConnection();
	sleep(3);
	echo 'State: '.$switch->getState().PHP_EOL;
	$switch->closeConnection();
	$switch->flipSwitch();
	$switch->closeConnection();
	sleep(3);

	$continue = true;
	while($continue)
	{
		if($switch->switchChanged())
		{
			echo 'Fliped: '.$switch->getState().PHP_EOL;
			$continue = false;
		}
		sleep(3);
	}

	echo 'Done'.PHP_EOL;