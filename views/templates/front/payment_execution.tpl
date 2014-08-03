<!-- Este payment_execution es el resultado del payment_execution.php [etexamplepaymet/views/templates/front/payment_execution.tpl] -->

{capture name=path}{l s='Bank-wire payment.' mod='etexamplepayment'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{l s='Order summary' mod='etexamplepayment'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if $nbProducts <= 0}
	<p class="warning">{l s='Your shopping cart is empty.' mod='etexamplepayment'}</p>
{else}

<h3>{l s='Example payment.' mod='etexamplepayment'}</h3>
<form action="{$link->getModuleLink('etexamplepayment', 'validation', [], true)}" method="post">
<p>
	<img src="{$this_path_etexampleypayment}etexamplepayment.jpg" alt="{l s='Bank wire' mod='etexamplepayment'}" width="86" height="49" style="float:left; margin: 0px 10px 5px 0px;" />
	{l s='You have chosen to pay by Example Payment.' mod='etexamplepayment'}
	<br/><br />
	{l s='Here is a short summary of your order:' mod='etexamplepayment'}
</p>
<p style="margin-top:20px;">
	- {l s='The total amount of your order is' mod='etexamplepayment'}
	<span id="amount" class="price">{displayPrice price=$total}</span>
	{if $use_taxes == 1}
    	{l s='(tax incl.)' mod='etexamplepayment'}
    {/if}
</p>
<p>
	-
	{if $currencies|@count > 1}
		{l s='We allow several currencies to be sent via Example Payment.' mod='etexamplepayment'}
		<br /><br />
		{l s='Choose one of the following:' mod='etexamplepayment'}
		<select id="currency_payement" name="currency_payement" onchange="setCurrency($('#currency_payement').val());">
			{foreach from=$currencies item=currency}
				<option value="{$currency.id_currency}" {if $currency.id_currency == $cust_currency}selected="selected"{/if}>{$currency.name}</option>
			{/foreach}
		</select>
	{else}
		{l s='We allow the following currency to be sent via Example Payment:' mod='etexamplepayment'}&nbsp;<b>{$currencies.0.name}</b>
		<input type="hidden" name="currency_payement" value="{$currencies.0.id_currency}" />
	{/if}
</p>
<p>
	{l s='Bank wire account information will be displayed on the next page.' mod='etexamplepayment'}
	<br /><br />
	<b>{l s='Please confirm your order by clicking "Place my order."' mod='etexamplepayment'}.</b>
</p>
<p class="cart_navigation">
	<input type="submit" name="submit" value="{l s='Place my order' mod='etexamplepayment'}" class="exclusive_large" />
	<a href="{$link->getPageLink('order', true, NULL, "step=3")}" class="button_large">{l s='Other payment methods' mod='etexamplepayment'}</a>
</p>
</form>
{/if}
