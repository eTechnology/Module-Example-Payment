<?php

class etexamplepaymentPayment_ExecutionModuleFrontController extends ModuleFrontController
{
	public $ssl = true;

	public function initContent()
	{
		$this->display_column_left = true; // muestra la columna de la izquierda [true|false]
		parent::initContent();

		// Valido si el tipo de moneda está permitido
		$cart = $this->context->cart;
		if (!$this->module->checkCurrency($cart))
			Tools::redirect('index.php?controller=order');

		// Asigno las variables Smarty para el TPL [etexamplepaymet/views/templates/front/payment_execution.tpl]
		$this->context->smarty->assign(array(
			'nbProducts' => $cart->nbProducts(),
			'cust_currency' => $cart->id_currency,
			'currencies' => $this->module->getCurrency((int)$cart->id_currency),
			'total' => $cart->getOrderTotal(true, Cart::BOTH),
			'this_path_etexampleypayment' => $this->module->getPathUri(),
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/'
		));

		// Indico cual es le TPL de este PHP
		$this->setTemplate('payment_execution.tpl');
	}
}
