<!-- Este es el hookPaymentReturn -->

<!-- Si la variable de Smarty 'status' creada en hookPaymentReturn es 'ok' realizo lo siguiente: -->
{if $status == 'ok'}
<p>{l s='Your order on %s is complete.' sprintf=$shop_name mod='etexamplepayment'}
		<br /><br />
		{l s='Please send us a Example Payment with' mod='etexamplepayment'}
		<br /><br />- {l s='Amount' mod='etexamplepayment'} <span class="price"> <strong>{$total_to_pay}</strong></span>
		<br /><br />- {l s='Name of account owner' mod='etexamplepayment'}  <strong>{if $bankwireOwner}{$bankwireOwner}{else}___________{/if}</strong>
		<br /><br />- {l s='Include these details' mod='etexamplepayment'}  <strong>{if $bankwireDetails}{$bankwireDetails}{else}___________{/if}</strong>
		<br /><br />- {l s='Bank name' mod='etexamplepayment'}  <strong>{if $bankwireAddress}{$bankwireAddress}{else}___________{/if}</strong>
		{if !isset($reference)}
			<br /><br />- {l s='Do not forget to insert your order number #%d in the subject of your bank wire' sprintf=$id_order mod='etexamplepayment'}
		{else}
			<br /><br />- {l s='Do not forget to insert your order reference %s in the subject of your bank wire.' sprintf=$reference mod='etexamplepayment'}
		{/if}		<br /><br />{l s='An email has been sent with this information.' mod='etexamplepayment'}
		<br /><br /> <strong>{l s='Your order will be sent as soon as we receive payment.' mod='etexamplepayment'}</strong>
		<br /><br />{l s='If you have questions, comments or concerns, please contact our' mod='etexamplepayment'} <a href="{$link->getPageLink('contact', true)}">{l s='expert customer support team. ' mod='etexamplepayment'}</a>.
	</p>
{else}

<!-- Caso contrario mostraré esto: -->
	<p class="warning">
		{l s='We noticed a problem with your order. If you think this is an error, feel free to contact our' mod='etexamplepayment'} 
		<a href="{$link->getPageLink('contact', true)}">{l s='expert customer support team. ' mod='etexamplepayment'}</a>.
	</p>
{/if}
