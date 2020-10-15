<?php
function smarty_function_sisow_banken($params)
{
	require_once ($params['dir'].'/sisow/sisow.cls5.php');
	$sisow = new Sisow('', '');
	$banken = '';
	
	$testmode = false;
	
	if($params['testmode'] == 'on')
		$testmode = true;
		
	$sisow->directoryRequest($banken, false, $testmode);
	
	$keuze = '<select id="issuerid" name="payment_info[issuerid]">
		<option selected value="">Kies uw bank...</option>';
		
	foreach ($banken as $k => $v) {
		$keuze .= "<option value=\"" . $k . "\">" . $v . "</option>";
	}
	$keuze .= '</select>';
    return $keuze;
	
}
?>