{* $Id: sisowideal.tpl 6560 2008-12-15 11:41:36Z zeke $ *}

<hr />
<div class="form-field">
	<label for="merchantid">Website Key:</label>
	<input type="text" name="payment_data[processor_params][merchantid]" id="merchantid" value="{$processor_params.merchantid}" class="input-text" size="20" />
</div>
<div class="form-field">
	<label for="merchantkey">Secret Key:</label>
	<input type="text" name="payment_data[processor_params][merchantkey]" id="merchantkey" value="{$processor_params.merchantkey}" class="input-text"  size="40" />
</div>
<div class="form-field">
	<label for="shopid">Shop ID:</label>
	<input type="text" name="payment_data[processor_params][shopid]" id="shopid" value="{$processor_params.shopid}" class="input-text"  size="40" />
</div>
<div class="form-field">
	<label for="statussuccess">Status Success:</label>
	{sisowstatus state="statussuccess" current=$processor_params.statussuccess}
</div>
<div class="form-field">
	<label for="statusfailed">Status GEEN Success:</label>
	{sisowstatus state="statusfailed" current=$processor_params.statusfailed}
</div>
<div class="form-field">
	<label for="currency">{__("currency")}:</label>
	<select name="payment_data[processor_params][currency]" id="currency">
		<option value="EUR" {if $processor_params.currency == "EUR"}selected="selected"{/if}>{__("currency_code_eur")}</option>
	</select>
</div>
<div class="form-field">
	<label for="note">Bank keuze tekst:</label>
	<input type="text" name="payment_data[processor_params][note]" id="banknote" value="{$processor_params.note}" class="input-text"  size="60" />
</div>
<div class="form-field">
	<label for="description">Omschrijving:</label>
	<input type="text" name="payment_data[processor_params][description]" id="description" value="{$processor_params.description}" class="input-text"  size="60" />
</div>
<div class="form-field">
	<label for="succesurl">Succes URL:</label>
	<input type="text" name="payment_data[processor_params][succesurl]" id="succesurl" value="{$processor_params.succesurl}" class="input-text" />
</div>
<div class="form-field">
	<label for="failedurl">Failed URL:</label>
	<input type="text" name="payment_data[processor_params][failedurl]" id="failedurl" value="{$processor_params.failedurl}" class="input-text" />
</div>
<p>
Wanneer u de omschrijving leeg laat, wordt hiervoor "Order: order_id" gebruikt.<br />
Als u het woord <b>ORDER_ID</b> gebruikt in de omschrijving, wordt dit vervangen met het ordernummer.
</p>
