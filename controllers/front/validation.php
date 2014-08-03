<?php

class etexamplepaymentValidationModuleFrontController extends ModuleFrontController
{

	public function postProcess()
	{
		// Compruebo que hallan llegado a este punto correctamente, caso contrario los redirecciono al paso 1
		$cart = $this->context->cart;
		if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active)
			Tools::redirect('index.php?controller=order&step=1');

		// Compruebe que la opción de pago está disponible en caso de que el cliente cambió de dirección justo antes del final del proceso de compra
		$authorized = false;
		foreach (Module::getPaymentModules() as $module)
			if ($module['name'] == 'etexamplepayment')
			{
				$authorized = true;
				break;
			}
		if (!$authorized)
			die($this->module->l('This payment method is not available.', 'validation'));

		// Valido que el cliente esté logueado
		$customer = new Customer($cart->id_customer);
		if (!Validate::isLoadedObject($customer))
			Tools::redirect('index.php?controller=order&step=1');

		// Compruebo el tipo de moneda actual
		$currency = $this->context->currency;
		$total = (float)$cart->getOrderTotal(true, Cart::BOTH);

		// Envío las variables Smarty al mail de la orden generada [public_html/mails/es/etexamplepayment.html]
		$mailVars = array(
			'{bankwire_owner}' => Configuration::get('BANK_WIRE_OWNER'),
			'{bankwire_details}' => nl2br(Configuration::get('BANK_WIRE_DETAILS')),
			'{bankwire_address}' => nl2br(Configuration::get('BANK_WIRE_ADDRESS'))
		);

		// Valido el pedido del carrito enviado todos los parametros definidos en la clase.
		//public function validateOrder($id_cart, $id_order_state, $amount_paid, $payment_method = 'Unknown', $message = null, $extra_vars = array(), currency_special = null, $dont_touch_amount = false,	$secure_key = false, Shop $shop = null)
		$this->module->validateOrder($cart->id, Configuration::get('PS_OS_EXAMPLE'), $total, $this->module->displayName, NULL, $mailVars, (int)$currency->id, false, $customer->secure_key);

		// Redirecciono al hookPaymentReturn para mostrar al cliente los datos de la empresa
		Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cart->id.'&id_module='.$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key);
	}
}
