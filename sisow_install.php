<?php
	// Load database settings

	// Set default timezone (required in PHP 5+)
	if (function_exists('date_default_timezone_set')) {
		date_default_timezone_set('Europe/Amsterdam');
	}

	// Load user configuration
	define('BOOTSTRAP', true);
	define('DIR_ROOT', dirname(__FILE__));
	require_once(dirname(__FILE__) . '/config.php');

	// Connect to database	
	$mysqli = new mysqli($config['db_host'], $config['db_user'], $config['db_password'], $config['db_name']);

	/* check connection */
	if ($mysqli->connect_errno) {
		printf("Connect failed: %s\n", $mysqli->connect_error);
		exit();
	}

	$payments = array(
		'iDEAL'=>'ideal',
		'MisterCash'=>'mc',
		'SofortBanking'=>'de',
		'PayPal'=>'pp',
		'Webshop Giftcard'=>'wg',
		'VVV Giftcard'=>'vvv',
		'ebill'=>'ebill',
		'OverBoeking'=>'ob',
		'Maestro'=>'maestro',
		'MasterCard'=>'mastercard',
		'Visa'=>'visa',
		'Focum' => 'focum',
		'Giropay' => 'giropay',
		'EPS' => 'eps',
		'ING HomePay' => 'homepay',
		'bunq' => 'bunq',
		'iDEAL QR' => 'idealqr',
		'V PAY' => 'vpay',
		'Afterpay' => 'afterpay',
		'Capayable' => 'capayable',
		'Belfius' => 'belfius',
		'CBC Betaalknop' => 'cbc',
		'KBC Betaalknop' => 'kbc',
		'Billink Achteraf Betalen' => 'billink',
		'Spraypay' => 'spraypay',
		'Klarna' => 'klarna',
	);
	
	foreach($payments as $naam => $paymentcode)
	{		
		if($paymentcode == 'ideal')
		{
			$template = 'sisow_ideal.tpl';
		}
		elseif ($paymentcode == 'ecare')
		{
			$template = 'sisow_ecare.tpl';
		}
		elseif ($paymentcode == 'focum')
		{
			$template = 'sisow_focum.tpl';
		}
		elseif ($paymentcode == 'afterpay')
		{
			$template = 'sisow_afterpay.tpl';
		}
		elseif ($paymentcode == 'ob' || $paymentcode == 'ebill')
		{
			$template = 'sisow_obeb.tpl';
		}
		elseif ($paymentcode == 'giropay')
		{
			$template = 'sisow_giropay.tpl';
		}
		elseif ($paymentcode == 'eps')
		{
			$template = 'sisow_eps.tpl';
		}
		elseif ($paymentcode == 'capayable')
		{
			$template = 'sisow_capayable.tpl';
		}
		elseif ($paymentcode == 'billink')
		{
			$template = 'sisow_billink.tpl';
		}
		else
		{
			$template = 'cc_outside.tpl';
		}

		upd($paymentcode, "`" . $config['table_prefix'] . "payment_processors` SET `processor` = 'Sisow ".$naam."', `processor_script` = 'sisow".$paymentcode.".php', `admin_template` = 'sisow".$paymentcode.".tpl', `processor_template` = 'views/orders/components/payments/".$template."', `callback` = 'N', `type` = 'P'", $config['table_prefix'], $mysqli);
	}
	
	echo '
<h1>Sisow Installatie</h1>
<p style="color: red;">Please remove this file after installation and clear you CS-Cart cache!</p>
';

	function upd($script, $query, $prefix, $link) {
		$result = mysqli_query($link, "SELECT * FROM `" . $prefix . "payment_processors` WHERE `processor_script` = 'sisow" . $script . ".php'");
		if(!$result)
			return;
		$numrows = $result->num_rows;
		$result->free();
		
		if ($numrows == 0) {
			$ex = mysqli_query($link, "INSERT INTO " . $query);
		}
		else {		
			if ($result = $link->query("SELECT * FROM `" . $prefix . "payment_processors` WHERE `processor_script` = 'sisow" . $script . ".php'")) {
				/* fetch associative array */
				while ($row = $result->fetch_assoc()) {
					mysqli_query($link, "UPDATE " . $query . " WHERE `processor_id` = '" . $row['processor_id'] . "'");
				}

				/* free result set */
				$result->free();
			}
		}
	}
?>