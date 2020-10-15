<div class="control-group">
    <label class="control-label" for="merchant_id">{__("sisowmerchantid")}:</label>
    <div class="controls">
    	<input type="text" name="payment_data[processor_params][merchant_id]" id="merchant_id" value="{$processor_params.merchant_id}"   size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="merchant_key">{__("sisowmerchantkey")}:</label>
    <div class="controls">
    	<input type="text" name="payment_data[processor_params][merchant_key]" id="merchant_key" value="{$processor_params.merchant_key}"   size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="shop_id">{__("sisowshopid")}:</label>
    <div class="controls">
    	<input type="text" name="payment_data[processor_params][shop_id]" id="shop_id" value="{$processor_params.shop_id}"   size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="testmode">{__("sisowtestmode")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][testmode]" id="testmode">
            <option value="test" {if $processor_params.testmode == "test"}selected="selected"{/if}>{__("test")}</option>
            <option value="live" {if $processor_params.testmode == "live"}selected="selected"{/if}>{__("live")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="description">{__("sisowdescription")}:</label>
    <div class="controls">
    	<input type="text" name="payment_data[processor_params][description]" id="description" value="{$processor_params.description}"   size="60">
    </div>
</div>

{assign var="statuses" value=$smarty.const.STATUSES_ORDER|fn_get_simple_statuses}
    
<div class="control-group">
	<label class="control-label" for="status_success">{__("sisowstatussuccess")}:</label>
	<div class="controls">
		<select name="payment_data[processor_params][status_success]" id="status_success">
			{foreach from=$statuses item="s" key="k"}
			<option value="{$k}" {if $processor_params.status_success == $k || !$processor_params.status_success && $k == 'O'}selected="selected"{/if}>{$s}</option>
			{/foreach}
		</select>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="status_cancel">{__("sisowstatuscancel")}:</label>
	<div class="controls">
		<select name="payment_data[processor_params][status_cancel]" id="status_cancel">
			{foreach from=$statuses item="s" key="k"}
			<option value="{$k}" {if $processor_params.status_cancel == $k || !$processor_params.status_cancel && $k == 'O'}selected="selected"{/if}>{$s}</option>
			{/foreach}
		</select>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="status_expired">{__("sisowstatusexpired")}:</label>
	<div class="controls">
		<select name="payment_data[processor_params][status_expired]" id="status_expired">
			{foreach from=$statuses item="s" key="k"}
			<option value="{$k}" {if $processor_params.status_expired == $k || !$processor_params.status_expired && $k == 'O'}selected="selected"{/if}>{$s}</option>
			{/foreach}
		</select>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="status_failed">{__("sisowstatusfailed")}:</label>
	<div class="controls">
		<select name="payment_data[processor_params][status_failed]" id="status_failed">
			{foreach from=$statuses item="s" key="k"}
			<option value="{$k}" {if $processor_params.status_failed == $k || !$processor_params.status_failed && $k == 'O'}selected="selected"{/if}>{$s}</option>
			{/foreach}
		</select>
	</div>
</div>