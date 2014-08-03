<!-- Este es el TPL de hookPayment -->
<p class="payment_module">
	<a href="{$link->getModuleLink('etexamplepayment', 'payment_execution')}" title="{l s='Pay by Example Payment' mod='etexamplepayment'}">
		<img src="{$this_path}etexamplepayment.jpg" alt="{l s='Pay by Example Payment' mod='etexamplepayment'}" height="49"/>
		{l s='Pay by Example Payment. Development by eTechnology' mod='etexamplepayment'}
	</a>
	<!-- Al hacer click al boton pagar lo estoy enviando al payment_execution.php [/etexamplepayment/controllers/front/payment_execution.php] -->
</p>