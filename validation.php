<?php

// ESTE VALIDATION es el mismo que el de /Controllers/front/validation  solo que es para las version 1.4 de Prestashop

/**
 * @deprecated 1.5.0 This file is deprecated, use moduleFrontController instead
 */

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/etexamplepayment.php');

$context = Context::getContext();
$cart = $context->cart;
$etexamplepayment = new EtExamplePayment();

if ($cart->id_customer == 0 OR $cart->id_address_delivery == 0 OR $cart->id_address_invoice == 0 OR !$etexamplepayment->active)
	Tools::redirect('index.php?controller=order&step=1');

// Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
$authorized = false;
foreach (Module::getPaymentModules() as $module)
	if ($module['name'] == 'etexamplepayment')
	{
		$authorized = true;
		break;
	}
if (!$authorized)
	die($etexamplepayment->l('This payment method is not available.', 'validation'));

$customer = new Customer((int)$cart->id_customer);

if (!Validate::isLoadedObject($customer))
	Tools::redirect('index.php?controller=order&step=1');

$currency = $context->currency;
$total = (float)($cart->getOrderTotal(true, Cart::BOTH));

$etexamplepayment->validateOrder($cart->id, Configuration::get('PS_OS_EXAMPLE'), $total, $etexamplepayment->displayName, NULL, array(), (int)$currency->id, false, $customer->secure_key);

$order = new Order($etexamplepayment->currentOrder);
Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cart->id.'&id_module='.$etexamplepayment->id.'&id_order='.$etexamplepayment->currentOrder.'&key='.$customer->secure_key);
