<?php
/***************************************************************************
*                                                                          *
*    Copyright (c) 2012 Sisow B.V. All rights reserved.                    *
*                                                                          *
****************************************************************************/

//
// $Id: base.php 2011-05-24
//

use Tygh\Registry;
use Tygh\Session;

if ( !defined('AREA') ) { die('Access denied'); }

include_once 'sisow.cls5.php';
	
if (defined('PAYMENT_NOTIFICATION')) {
	$order_id = $_REQUEST['ec'];

	if ($mode == 'return') {
		$payment_id = db_get_field("SELECT payment_id FROM ?:orders WHERE order_id = ?i", $order_id);
		$processor_data = fn_get_payment_method_data($payment_id);
		
		$succesStatus = array('Reservation', 'Pending', 'Open', 'Success');
		
		if(in_array($_GET['status'], $succesStatus) && !empty($processor_data['processor_params']['succesurl']))
		{
			fn_redirect($processor_data['processor_params']['succesurl'], true, true);
			exit;
		}
		else if(!in_array($_GET['status'], $succesStatus) && !empty($processor_data['processor_params']['failedurl']))
		{
			fn_redirect($processor_data['processor_params']['failedurl'], true, true);
			exit;
		}
		else
		{
			if (fn_check_payment_script($filename.'.php', $order_id)) {
				fn_order_placement_routines('route', $order_id, false);	
			}	
		}
	}
    elseif ($mode == 'notify') {
		$valid_id = db_get_field("SELECT order_id FROM ?:order_data WHERE order_id = ?i AND type = 'S'", $order_id);
		if (empty($valid_id)) {
			echo 'Order already Success';
			exit;
		}
	
		$pp_response = array();
		$payment_id = db_get_field("SELECT payment_id FROM ?:orders WHERE order_id = ?i", $order_id);
		$processor_data = fn_get_payment_method_data($payment_id);
		$order_info = fn_get_order_info($order_id);

		if (isset($processor_data['processor_params']['statussuccess']) && $processor_data['processor_params']['statussuccess'] != "") {
			$st = $processor_data['processor_params']['statussuccess'];
		}
		else {
			$st = 'P';
		}
		
		if($order_info['status'] == $st)
		{
			echo 'Order already success'; exit;
		}
		
		$sisow = new Sisow($processor_data['processor_params']['merchantid'], $processor_data['processor_params']['merchantkey'], $processor_data['processor_params']['shopid']);
		$trxid = $_REQUEST['trxid'];
		if ($sisow->StatusRequest($trxid) != 0) {
			exit('StatusRequest failed');
		}
		$pp_response['transaction_id'] = $trxid;
		
		if ($sisow->status == Sisow::statusSuccess || $sisow->status == Sisow::statusReservation) {
			$pp_response['order_status'] = $st;
			$pp_response['reason_text'] = 'Approved by Sisow';
			$pp_response['consumerAccount'] = $sisow->consumerBic . "/" . $sisow->consumerIban;
			$pp_response['consumerName'] = $sisow->consumerName;
			$pp_response['consumerCity'] = $sisow->consumerCity;
			
			fn_change_order_status($order_id, $pp_response['order_status'], '', true);
			fn_finish_payment($order_id, $pp_response, true);
		}
		elseif($sisow->status == Sisow::statusOpen|| $sisow->status == Sisow::statusPending || $sisow->pendingKlarna)
		{
			if (isset($processor_data['processor_params']['statuspending']) && $processor_data['processor_params']['statuspending'] != "") {
				$st = $processor_data['processor_params']['statuspending'];
			}
			else {
				$st = 'O';
			}
			$pp_response['order_status'] = $st;
			$pp_response['reason_text'] = 'Transaction is still open';
			fn_change_order_status($order_id, $pp_response['order_status'], '', false);
		}
		else {
			if (isset($processor_data['processor_params']['statusfailed']) && $processor_data['processor_params']['statusfailed'] != "") {
				$st = $processor_data['processor_params']['statusfailed'];
			}
			else {
				$st = 'I';
			}

			$pp_response['order_status'] = $st;
			$pp_response['reason_text'] = $sisow->status;
			fn_change_order_status($order_id, $pp_response['order_status'], '', false);
		}
		exit;
	}
}
else 
{	
	echo '<center><img src="images/sisow/'.$paymentcode.'.png" alt="payment logo" /> </center>';

	$arg = array();
	
	$currency = $processor_data['processor_params']['currency'] ;
	$amount = round(fn_format_price($order_info['total'], $currency) , 2);
	
	//wanneer ideal is geselecteerd
	//actie: controleren of er een bankkeuze is gemaakt
	//uitvoer: bij geen bankkeuze wordt er een foutmelding gegeven en een redirect geplaatst
	if($paymentcode == 'ideal' && (!isset($order_info['payment_info']['issuerid']) || $order_info['payment_info']['issuerid'] == ''))
	{
		fn_set_notification('E', fn_get_lang_var('warning'), 'Kies een bank', false, 'no_bank');
		fn_redirect(Registry::get('config.current_location') . "/" . $index_script . "?dispatch=checkout.checkout&order_id=".$order_id, true);
		exit;
	}
	
	//wanneer ecare is geselecteerd
	//actie: controleren of alle gegevens aanwezig zijn
	//uitvoer: bij onjuiste gegevens wordt er een foutmelding gegeven en een redirect geplaatst
	else if($paymentcode == 'ecare' && ($order_info['payment_info']['sisow_gender'] == '' || $order_info['payment_info']['sisow_voor'] == '' || $order_info['payment_info']['days'] < 0 || $order_info['payment_info']['days'] > 31 || $order_info['payment_info']['month'] < 0  || $order_info['payment_info']['month'] > 12 || $order_info['payment_info']['year'] == ''))
	{
		if($order_info['payment_info']['sisow_gender'] == '')
			fn_set_notification('E', fn_get_lang_var('warning'), 'Kies een aanhef', false, 'no_gender');
		
		if($order_info['payment_info']['phone'] == '')
			fn_set_notification('E', fn_get_lang_var('warning'), 'Vul uw voorletter(s) in.', false, 'no_initials');
		
		if($order_info['payment_info']['days'] < 0 || $order_info['payment_info']['days'] > 31 || $order_info['payment_info']['month'] < 0  || $order_info['payment_info']['month'] > 12 || $order_info['payment_info']['year'] == '')
			fn_set_notification('E', fn_get_lang_var('warning'), 'Vul een juiste geboortedatum in.', false, 'invalid_date');
			
		fn_redirect(Registry::get('config.current_location') . "/" . $index_script . "?dispatch=checkout.checkout&order_id=".$order_id, true);
		exit;
	}
	else if($paymentcode == 'focum' && 
		($order_info['payment_info']['iban'] == '' ||$order_info['payment_info']['sisow_gender'] == '' || $order_info['payment_info']['phone'] == '' || $order_info['payment_info']['days'] < 0 || $order_info['payment_info']['days'] > 31 || $order_info['payment_info']['month'] < 0  || $order_info['payment_info']['month'] > 12 || $order_info['payment_info']['year'] == ''))
	{
		if($order_info['payment_info']['iban'] == '')
			fn_set_notification('E', fn_get_lang_var('warning'), 'Voer uw iBAN in', false, 'no_iban');
			
		if($order_info['payment_info']['sisow_gender'] == '')
			fn_set_notification('E', fn_get_lang_var('warning'), 'Kies een aanhef', false, 'no_gender');
		
		if($order_info['payment_info']['phone'] == '')
			fn_set_notification('E', fn_get_lang_var('warning'), 'Vul uw telefoonnummer in.', false, 'no_initials');
		
		if($order_info['payment_info']['days'] < 0 || $order_info['payment_info']['days'] > 31 || $order_info['payment_info']['month'] < 0  || $order_info['payment_info']['month'] > 12 || $order_info['payment_info']['year'] == '')
			fn_set_notification('E', fn_get_lang_var('warning'), 'Vul een juiste geboortedatum in.', false, 'invalid_date');
			
		fn_redirect(Registry::get('config.current_location') . "/" . $index_script . "?dispatch=checkout.checkout&order_id=".$order_id, true);
		exit;
	}
	else if(($paymentcode == 'afterpay' || $paymentcode == 'billink') && 
		($order_info['payment_info']['sisow_gender'] == '' || $order_info['payment_info']['phone'] == '' || $order_info['payment_info']['days'] < 0 || $order_info['payment_info']['days'] > 31 || $order_info['payment_info']['month'] < 0  || $order_info['payment_info']['month'] > 12 || $order_info['payment_info']['year'] == ''))
	{			
		if($order_info['payment_info']['sisow_gender'] == '')
			fn_set_notification('E', fn_get_lang_var('warning'), 'Kies een aanhef', false, 'no_gender');
		
		if($order_info['payment_info']['phone'] == '')
			fn_set_notification('E', fn_get_lang_var('warning'), 'Vul uw telefoonnummer in.', false, 'no_initials');
		
		if($order_info['payment_info']['days'] < 0 || $order_info['payment_info']['days'] > 31 || $order_info['payment_info']['month'] < 0  || $order_info['payment_info']['month'] > 12 || $order_info['payment_info']['year'] == '')
			fn_set_notification('E', fn_get_lang_var('warning'), 'Vul een juiste geboortedatum in.', false, 'invalid_date');
			
		fn_redirect(Registry::get('config.current_location') . "/" . $index_script . "?dispatch=checkout.checkout&order_id=".$order_id, true);
		exit;
	}
	else if($paymentcode == 'capayable' && 
		($order_info['payment_info']['sisow_gender'] == '' || $order_info['payment_info']['phone'] == '' || $order_info['payment_info']['days'] < 0 || $order_info['payment_info']['days'] > 31 || $order_info['payment_info']['month'] < 0  || $order_info['payment_info']['month'] > 12 || $order_info['payment_info']['year'] == ''))
	{			
		if($order_info['payment_info']['sisow_gender'] == '')
			fn_set_notification('E', fn_get_lang_var('warning'), 'Kies een aanhef', false, 'no_gender');
		
		if($order_info['payment_info']['phone'] == '')
			fn_set_notification('E', fn_get_lang_var('warning'), 'Vul uw telefoonnummer in.', false, 'no_initials');
		
		if($order_info['payment_info']['days'] < 0 || $order_info['payment_info']['days'] > 31 || $order_info['payment_info']['month'] < 0  || $order_info['payment_info']['month'] > 12 || $order_info['payment_info']['year'] == '')
			fn_set_notification('E', fn_get_lang_var('warning'), 'Vul een juiste geboortedatum in.', false, 'invalid_date');
			
		fn_redirect(Registry::get('config.current_location') . "/" . $index_script . "?dispatch=checkout.checkout&order_id=".$order_id, true);
		exit;
	}
	
	else if(($paymentcode == 'eps' || $paymentcode == 'giropay') && empty($order_info['payment_info']['bic_' . $paymentcode]))
	{
		fn_set_notification('E', '', __('Bitte geben Sie Ihren Bankauswahl'));
		fn_redirect(Registry::get('config.current_location') . "/" . $index_script . "?dispatch=checkout.checkout&order_id=".$order_id, true);
		exit;
	}
	else
	//alle variabelen zetten voor de betaling
	{						
		$arg['ipaddress'] = $_SERVER['REMOTE_ADDR'];
		
		$arg['billing_firstname'] = $order_info['b_firstname'];
		$arg['billing_lastname'] = $order_info['b_lastname'];
		
		if(isset($order_info['payment_info']['phone']))
		{
			$arg['billing_phone'] = $order_info['payment_info']['phone'];
		}
		else
		{
			$arg['billing_phone'] = $order_info['b_phone'];
		}
		
		$arg['billing_address1'] = $order_info['b_address'];
		$arg['billing_city'] = $order_info['b_city'];
		$arg['billing_zip'] = $order_info['b_zipcode'];
		
		$arg['makeinvoice'] = 'false';
		$arg['mailinvoice'] = 'false';
		
		if(isset($processor_data['processor_params']['makeinvoice']) && $processor_data['processor_params']['makeinvoice'] == 'on')
			$arg['makeinvoice'] = 'true';
		
		if(isset($order_info['payment_method']['params']['mailinvoice']) && $order_info['payment_method']['params']['mailinvoice'] == 'on')
			$arg['mailinvoice'] = 'true';
		
		if(isset($order_info['payment_info']['days']) && isset($order_info['payment_info']['month']) && isset($order_info['payment_info']['year']))
		{
			if($order_info['payment_info']['year'] < 100)
			{
				$jaar = $order_info['payment_info']['year'] + 1900;
			}
			else
			{
				$jaar = $order_info['payment_info']['year'];
			}
			$arg['birthdate'] = sprintf('%02d%02d%04d', $order_info['payment_info']['days'], $order_info['payment_info']['month'], $jaar);
		}
		
		if(isset($order_info['payment_info']['sisow_gender']))
			$arg['gender'] = $order_info['payment_info']['sisow_gender'];
		
		if(isset($order_info['payment_info']['sisow_initials']))
			$arg['initials'] = $order_info['payment_info']['sisow_initials'];
		
		$arg['billing_mail'] = $order_info['email'];
		$arg['billing_company'] = (isset($order_info['company'])) ? $order_info['company'] : '';
		$arg['billing_coc'] = array_key_exists('sisow_coc', $order_info['payment_info']) ? $order_info['payment_info']['sisow_coc'] : '';
		$arg['billing_address2'] = $order_info['b_address_2'];
		$arg['billing_country'] = $order_info['b_country_descr'];
		$arg['billing_countrycode'] = $order_info['b_country'];
		
		$arg['shipping_firstname'] = $order_info['s_firstname'];
		$arg['shipping_lastname'] = $order_info['s_lastname'];
		$arg['shipping_mail'] = $order_info['email'];
		$arg['shipping_company'] = (isset($order_info['company'])) ? $order_info['company'] : '';
		$arg['shipping_address1'] = $order_info['s_address'];
		$arg['shipping_address2'] = $order_info['s_address_2'];
		$arg['shipping_zip'] = $order_info['s_zipcode'];
		$arg['shipping_city'] = $order_info['s_city'];
		$arg['shipping_country'] = $order_info['s_country_descr'];
		$arg['shipping_countrycode'] = $order_info['s_country'];
		$arg['shipping_phone'] = $order_info['s_phone'];
		$arg['shipping'] = $order_info['shipping_cost'];	
				
		//producten en taxes
		//kijken hoeveel btw aanwzig is
		$arg['tax'] = 0;
		foreach ($order_info['taxes'] as $tax)
		{
			$arg['tax'] += $tax['tax_subtotal'];
		}
		
		$arg['currency'] = $currency;
		
		if($paymentcode == 'eps' || $paymentcode == 'giropay')
			$arg['bic'] = $order_info['payment_info']['bic_' . $paymentcode];

		if(isset($order_info['payment_method']['processor_params']['include']) && $order_info['payment_method']['processor_params']['include'] == 'on')
			$arg['including'] = 'true';
		
		if(isset($order_info['payment_method']['processor_params']['include']))
			$arg['inlcuding'] = $order_info['payment_method']['params']['days'];
			
		//producten
		//producten ophalen en toevoegen	
		$product_id = 1;
		
		$taxes = fn_get_taxes();
		$taxids = array();
		foreach($taxes as $tax)
			$taxids[] = $tax["tax_id"];
					
		if (!empty($order_info['products'])) {				
            foreach ($order_info['products'] as $k => $v) {
				$v['tax_ids'] = $taxids;
				fn_get_taxed_and_clean_prices($v, $_SESSION['auth']);
				
				$v['product'] = htmlspecialchars(strip_tags($v['product']));
				
				$taxrate = 0;
				foreach ($order_info['taxes'] as $tax_id => $tax){
					$taxrate = $tax["rate_value"];
				}
				
				// get total excl tax
				$totalExclTax = $taxrate == 0 ? $v['taxed_price'] : ($v['taxed_price'] * 100) / ($taxrate + 100);
				
				$arg['product_id_' . $product_id] 			= (empty($v['product_code'])) ? 'product'+$product_id : $v['product_code'];
				$arg['product_description_' . $product_id] 	= $v['product'];
				$arg['product_quantity_' . $product_id] 	= $v['amount'];
				$arg['product_netprice_' . $product_id] 	= round(($totalExclTax / $v['amount']) * 100.0);
				$arg['product_total_' . $product_id] 		= round($v['taxed_price'] * 100.0);
				$arg['product_nettotal_' . $product_id] 	= round($totalExclTax * 100.0);
				$arg['product_tax_' . $product_id] 			= $arg['product_total_' . $product_id] - $arg['product_nettotal_' . $product_id];
				$arg['product_taxrate_' . $product_id] 		= round($taxrate * 100.0);
				$arg['product_type_' . $product_id] 		= 'physical';
				
				$product_id++;
            }
        }
				
		//producten
		//verzendkosten toevoegen aan de producten
		if($order_info['shipping_cost'] > 0)
		{			
			$arg['product_id_'.$product_id] = 'shipping';
			
			foreach($order_info['shipping'] as $shipping)
				$arg['product_description_'.$product_id] = $shipping['shipping'];
			
			$arg['product_total_'.$product_id] = round(fn_order_shipping_cost($order_info) * 100.0);
			$taxrate = 0;
			$taxIncluded = false;
			foreach ($order_info['taxes'] as $tax_id => $tax)
			{			
				foreach($tax['applies'] as $k => $v){
					if($k == "S")
					{
						$taxrate = $tax["rate_value"];
						$taxIncluded = $tax['price_includes_tax'] == "Y";
					}
				}				
			}
			
			$arg['product_tax_'.$product_id] = round(($arg['product_total_'.$product_id] * $taxrate) / ($taxrate + 100));				
				
			$arg['product_quantity_'.$product_id] = '1';
			$arg['product_netprice_'.$product_id] = $arg['product_total_'.$product_id] - $arg['product_tax_'.$product_id];
			$arg['product_nettotal_'.$product_id] = $arg['product_netprice_'.$product_id];
			$arg['product_taxrate_'.$product_id] = round($taxrate * 100.0, 0);
			$arg['product_type_' . $product_id] 		= 'shipping_fee';
			$product_id++;
		}

		//producten
		//payment fee toevoegen aan de producten		
		if( isset($order_info['payment_surcharge']) && $order_info['payment_surcharge'] > 0)
		{			
			$arg['product_id_'.$product_id] = 'paymentfee';
			$arg['product_description_'.$product_id] = $order_info['payment_method']['surcharge_title'];
			$arg['product_quantity_'.$product_id] = '1';
			
			$netprice = fn_format_price($order_info['payment_surcharge'], $currency);
			
			foreach ($order_info['taxes'] as $key => $tax)
			{
				if(in_array($key, $order_info['payment_method']['tax_ids']) && $tax['price_includes_tax'] == 'Y')
				{
					if($tax['rate_type'] == 'P')
					{
						$netprice = $netprice /((100.0 + $tax['rate_value']) / 100);
					}
				}
			}
			
			$taxamount = 0;
			$taxrate = 0;
			foreach ($order_info['taxes'] as $key => $tax)
			{
				if(in_array($key, $order_info['payment_method']['tax_ids']))
				{
					if($tax['rate_type'] == 'P')
					{
						$taxamount += $netprice * ($tax['rate_value'] / 100);
						$taxrate += $tax['rate_value'];
					}
				}
			}

			$arg['product_netprice_'.$product_id] = round($netprice * 100.0, 0);
			$arg['product_total_'.$product_id] = round(($netprice + $taxamount) * 100.0, 0);
			$arg['product_nettotal_'.$product_id] = $arg['product_netprice_'.$product_id];
			$arg['product_tax_'.$product_id] = round($taxamount * 100.0, 0);
			$arg['product_taxrate_'.$product_id] = round($taxrate * 100.0, 0);
			$arg['product_type_' . $product_id] 		= 'surcharge';
			$product_id ++;
		}
		
		//producten
		//eventuele korting toevoegen
		if(isset($order_info['discount']) && $order_info['discount'] > 0)
		{
			$arg['product_id_'.$product_id] = 'Disc';
			$arg['product_description_'.$product_id] = 'Koring';
			$arg['product_quantity_'.$product_id] = '1';
			$arg['product_netprice_'.$product_id] = round(fn_format_price($order_info['discount'], $currency) * 100.0, 0);
			$arg['product_total_'.$product_id] = $arg['product_netprice_'.$product_id];
			$arg['product_nettotal_'.$product_id] = $arg['product_netprice_'.$product_id];
			$arg['product_tax_'.$product_id] = '0';
			$arg['product_taxrate_'.$product_id] = '0';
			$arg['product_type_' . $product_id] 		= 'discount';
			$product_id ++;
		}
		
		//omschrijving inladen
		if (isset($processor_data['params']['description']) && $processor_data['params']['description'] != "") {
			$descr = str_replace("ORDER_ID", $order_id, $processor_data['params']['description']);
		}
		else {
			$descr = "Order " . $order_id;
		}
		
		//urls voor terugkoppeling inladen		
		$notifyurl = fn_url("payment_notification.notify?payment=".$filename."&order_id=".$order_id, AREA, 'current');
		$returnurl = fn_url("payment_notification.return?payment=".$filename."&order_id=".$order_id, AREA, 'current');
		
		//kijken of de testmodues geactiveerd moet worden
		if (array_key_exists('testmode', $processor_data['processor_params']) && $processor_data['processor_params']['testmode'] == 'on') {
			$arg['testmode'] = 'true';
		}
		else {
			$arg['testmode'] = 'false';
		}
				
		if(isset($order_info['payment_info']['iban']))
		{
			$arg['iban'] = $order_info['payment_info']['iban'];
		}
	
		//class sisow inladen en ontbrekene attributen inladen
		$sisow = new Sisow($processor_data['processor_params']['merchantid'], $processor_data['processor_params']['merchantkey'], $processor_data['processor_params']['shopid']);
		$sisow->amount = $amount;
		$sisow->payment = $paymentcode;
		$sisow->purchaseId = $order_id;
		$sisow->description = $descr;
		$sisow->notifyUrl = $notifyurl;
		$sisow->callbackUrl = $notifyurl;
		$sisow->returnUrl = $returnurl;
			
		if(isset($order_info['payment_info']['issuerid']) && $order_info['payment_info']['issuerid'] > 0)
			$sisow->issuerId = $order_info['payment_info']['issuerid'];
				

		//transaction request starten!
		if(($ex = $sisow->transactionRequest($arg)) < 0)
		{
			//print_r($sisow);
			//exit;
			if($sisow->payment == 'klarna' || $sisow->payment == 'klarnaacc')
			{				
				$error = 'Op dit moment is het niet mogelijk om te betalen via Klarna, kies een andere betaaloptie. ('. $ex . ', ' . $sisow->errorCode . ')';
			}
			else
			{
				$error = 'Betalen met '.$order_info['payment_method']['payment'].' is nu niet mogelijk, betaal anders. ('. $ex . ', ' . $sisow->errorCode . ')';
			}
			$pp_response['reason_text'] = $error;
			$pp_response['order_status'] = (isset($processor_data['processor_params']['statusfailed']) && $processor_data['processor_params']['statusfailed']!= '') ? $processor_data['processor_params']['statusfailed'] : "F";
		}
		else
		{
			$url = $sisow->issuerUrl;

			if($redirect == false)
			{
				$pp_response['transaction_id'] = $sisow->trxId;
				
				if(($sisow->StatusRequest()) < 0)
				{
					$pp_response['reason_text'] = 'StatusRequest Failed';			
					$pp_response['order_status'] = 'O';
				}
				else
				{
					if ($sisow->status == Sisow::statusSuccess || $sisow->status == 'Reservation') 
					{
						if (isset($processor_data['params']['statussuccess']) && $processor_data['params']['statussuccess'] != "") {
							$st = $processor_data['params']['statussuccess'];
						}
						else {
							$st = 'P';
						}
						
						$pp_response['order_status'] = $st;
						$pp_response['reason_text'] = 'Approved by Sisow';
					}
					elseif($sisow->status == Sisow::statusOpen|| $sisow->status == Sisow::statusPending || $sisow->pendingKlarna)
					{
						if (isset($processor_data['processor_params']['statuspending']) && $processor_data['processor_params']['statuspending'] != "") {
							$st = $processor_data['processor_params']['statuspending'];
						}
						else {
							$st = 'O';
						}
						$pp_response['order_status'] = $st;
						$pp_response['reason_text'] = 'Waiting for Klarna';
					}
					else
					{
						$pp_response['reason_text'] = ($sisow->pendingKlarna == 'true') ? 'Transactie wordt gecontroleerd door Klarna.' : 'Wachten op betaling, betaalinstructies zijn per mail naar u toegestuurd.';
						$pp_response['order_status'] = (isset($order_info['payment_method']['params']['statuspending'])) ? $order_info['payment_method']['params']['statuspending'] : 'O';
					}	
				}
				
				fn_change_order_status($order_id, $pp_response['order_status'], '', false);
				
				fn_redirect(fn_url("payment_notification.return?payment=".$filename."&order_id=".$order_id."&ec=".$order_id."&status=Success", AREA, 'current'));
				exit;
			}
			else
			{
				fn_redirect($url, true, true);
				exit;
			}
		}
	}
}
?>