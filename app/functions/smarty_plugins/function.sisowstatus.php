<?php
function smarty_function_sisowstatus($params)
{
	$keuze = '<select id="'.$params['state'].'" name="payment_data[processor_params]['.$params['state'].']">';
	
	foreach(fn_get_simple_statuses() as $k =>$v)
	{
		$selected = '';
		
		if($k == $params['current'])
			$selected = 'selected';
		
		$keuze .= "<option value=\"" . $k . "\" ".$selected." >" . $v . "</option>";
	}
	
	$keuze .= '</select>';
	
	return $keuze;
}
?>