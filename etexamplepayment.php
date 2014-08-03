<?php

if (!defined('_PS_VERSION_'))
	exit;

class EtExamplePayment extends PaymentModule
{
	private $_html = '';
	private $_postErrors = array();

	public $details;
	public $owner;
	public $address;
	public $extra_mail_vars;
	public function __construct()
	{
		$this->name = 'etexamplepayment';	// Tiene que ser el mismo nombre de la carpeta
		$this->tab = 'payments_gateways';	// Ubicación del Tab
		$this->version = '1.0.1';				// Versión del módulo
		$this->author = 'eTechnology';		// Autor del módulo

		$this->currencies = true;			// Dependerá del Tipo Monedas? 	[true|false]
		$this->currencies_mode = 'checkbox';// Tipo de elección de moneda	[checkbox|radio]

		// Captura en un Array multiple los valores de BANK_WIRE_DETAILS, BANK_WIRE_OWNER y BANK_WIRE_ADDRESS asignados al guardar los cambios del módulo [getContent()].
		$config = Configuration::getMultiple(array('BANK_WIRE_DETAILS', 'BANK_WIRE_OWNER', 'BANK_WIRE_ADDRESS'));

		// Valida que exista el valor de BANK_WIRE_OWNER para indicar que $this->owner será el valor de BANK_WIRE_OWNER.
		if (isset($config['BANK_WIRE_OWNER'])) $this->owner = $config['BANK_WIRE_OWNER'];
		if (isset($config['BANK_WIRE_DETAILS'])) $this->details = $config['BANK_WIRE_DETAILS'];
		if (isset($config['BANK_WIRE_ADDRESS'])) $this->address = $config['BANK_WIRE_ADDRESS'];

		// Código para poder editar a los atributos de la clase padre.
		parent::__construct();

		//Al colocar $this->l('') quiere decir que podrá ser traducible por el backoffice.
		$this->displayName = $this->l('Example Payment');	// Nombre que se mostrará en el listado de módulos.
		$this->description = $this->l('Accept payments for your products via bank.');	// Descripción del módulo.
		$this->confirmUninstall = $this->l('Are you sure about removing these details?');	// Texto que saldrá en la alerta al querer desistalar el módulo.

		// Si no existen $this->owner, $this->details y $this->address aparecerá el texto de Warning en el listado de módulos.
		if (!isset($this->owner) || !isset($this->details) || !isset($this->address))
			$this->warning = $this->l('Account owner and account details must be configured before using this module.');

		// Si no existen tipos de moneda en su tienda, aparecerá el texto de Warning en el listado de módulos.
		if (!count(Currency::checkPaymentCurrencies($this->id)))
			$this->warning = $this->l('No currency has been set for this module.');

		// Asigna las variables Smarty {bankwire_owner} con el valor asignado en BANK_WIRE_OWNER al capturar sus datos [getContect()].
		// Estas variables serán asignadas al email "etexamplepayment.html"
		$this->extra_mail_vars = array(
										'{bankwire_owner}' => Configuration::get('BANK_WIRE_OWNER'),
										'{bankwire_details}' => nl2br(Configuration::get('BANK_WIRE_DETAILS')),
										'{bankwire_address}' => nl2br(Configuration::get('BANK_WIRE_ADDRESS'))
										);
	}

	// Instalar Módulo
	public function install()
	{
		// Si la instalación fué dada || Si no ya fué registrado el hookPayment || Si ya fue registrado el hookPaymentReturn => no hagas nada [por eso pone false]
		if (!parent::install() || !$this->registerHook('payment') || !$this->registerHook('paymentReturn'))
			return false;
		return true; // En caso contrario realiza todo lo indicado anteriormente [por eso pone true].
	}

	// Desinstalar Módulo
	public function uninstall()
	{
		if (!Configuration::deleteByName('BANK_WIRE_DETAILS') 		// Si ya eliminaste el valor de BANK_WIRE_DETAILS
				|| !Configuration::deleteByName('BANK_WIRE_OWNER') 	// Si ya eliminaste el valor de BANK_WIRE_OWNER
				|| !Configuration::deleteByName('BANK_WIRE_ADDRESS')// Si ya eliminaste el valor de BANK_WIRE_ADDRESS
				|| !$this->genOrderState()							// Si el State ya ha sido generado
				|| !parent::uninstall())							// Si ya desistalaste
			return false;	// No hagas nada xD
		return true;		// Caso contrario realiza todo lo indicado anteriormente  :-D
	}


	// Generando un nuevo tipo de Estado
	private function genOrderState()
	{
		$alignetState = new OrderState();			// Instancio la clase OrderSate().
		$languages = Language::getLanguages(false);	// Llamo a la funcion Static getLanguages.
		foreach ($languages as $lang) {				// Realizo el for para que a todos los idiomas le coloque el nombre del nuevo estado.
		    $alignetState->name[(int) $lang['id_lang']] = 'En Espera de Pago :: Payment Example';	// Nombre del nuevo estado
		}
		// Los siguientes parámetros son los que se ejecutarán al cambiar a este nueve estado.
		$alignetState->invoice = 0;		// Generará la factura?
		$alignetState->send_email = 0;	// Enviará eMail ?
		$alignetState->module_name = $this->name;	// Nombre del módulo ? [sugiero colocar el mismo nombre del módulo, por eso coloqué $this->name]
		$alignetState->color = '#4169E1';	// Color del Estado ?
		$alignetState->unremovable = 1;		// Podrá ser removido? [1=si|0=no]
		$alignetState->hidden = 0;			// Oculatar estado al cliente?
		$alignetState->logable = 0;			// Será disponible solo para Clientes? Al colocar "1" excluira a los registros como invitado
		$alignetState->delivery = 0;		// Se Considerará como enviado?
		$alignetState->shipped = 0;			// Se Considerará como entregado?
		$alignetState->paid = 0;			// Considerarlo como pagado?
		$alignetState->deleted = 0;			// Puede ser eliminar? [1=si|0=no]
		$alignetState->save();

		copy((dirname(__file__) ."/logo.gif"), (dirname(dirname(dirname(__file__))). "/img/os/$alignetState->id.gif"));	// copia el Icono del estado

		Configuration::updateValue("PS_OS_EXAMPLE", $alignetState->id);	// Genera el nuevo nombre del estado.

		return true;	// Valida todo lo declarado en este Método.
	}


	// Mostrar formulario
	private function displayForm()
	{
		// Este array es válido solo para las versiones 1.5 de Prestashop
		$fields_form[0]['form'] = array(
			'legend' => array(
				'title' => $this->l('Keys Private Commerce'),	// Titulo del campo.
				'image' => _MODULE_DIR_.$this->name.'/logo.gif'	// URL de la imagen del campo.
			),

			'input' => array(	// Tipo de boton.
              	array(
                  'type' => 'text',		// Tipo de input.
                  'label' => $this->l('Numeros de cuenta del Banco'),	// Nombre para mostrar.
                  'name' => 'BANK_WIRE_DETAILS',	// Nombre del value="" del input.
                  'desc' => $this->l("Description of number account <br> Context desription for your module"),	//descripción del input.
                  'required' => true 	// Es obligatorio este campo?  [true|false]
              	),
              	array(
                  'type' => 'textarea',	// Tipo Textarea
                  'label' => $this->l('Nombre del Titular'),	// Nombre para mostrar
                  'name' => 'BANK_WIRE_OWNER',	// Nombrel del value="" del textarea
                  'cols' => 32,		// Columnas del texarea
                  'rows' => 2,		// Filas del Texarea
                  'desc' => $this->l("Description of Owner, thi is <b>bold</b>"),	// Descripción del Textarea
                  'required' => true // Es obligatorio este campo?  [true|false]
              	),
              	array(
                  'type' => 'text',
                  'label' => $this->l('Nombre del Banco'),
                  'name' => 'BANK_WIRE_ADDRESS',
                  'desc' => $this->l("Description on HTML <ul> <li> number 1 </li> <li> number 2 </li> </ul>"),
                  'required' => true
              	)
			),
			'submit' => array(
				'title' => $this->l('Save'),	// Nombre del Submit
				'class' => 'button'
			)
		);

		$default_lang = (int)Configuration::get('PS_LANG_DEFAULT');	// Muestra el idioma por defecto

		$helper = new HelperForm();	// Instancio HelperForm

		// los siguientes campos no se deben de cambiar.
		$helper->module = $this;
		$helper->name_controller = $this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

		$helper->default_form_language = $default_lang;
		$helper->allow_employee_form_lang = $default_lang;

		$helper->title = $this->displayName;
		$helper->show_toolbar = true;
		$helper->toolbar_scroll = true;
		$helper->submit_action = 'submit'.$this->name;
		$helper->toolbar_btn = array(
		  'save' =>
		  array(
		      'desc' => $this->l('Save'),
		      'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
		      '&token='.Tools::getAdminTokenLite('AdminModules'),
		  ),
		  'back' => array(
		      'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
		      'desc' => $this->l('Back to list')
		  )
		);

		// Se colocan los valores de campos
		$helper->fields_value['BANK_WIRE_DETAILS'] = Configuration::get('BANK_WIRE_DETAILS');
		$helper->fields_value['BANK_WIRE_OWNER'] = Configuration::get('BANK_WIRE_OWNER');
		$helper->fields_value['BANK_WIRE_ADDRESS'] = Configuration::get('BANK_WIRE_ADDRESS');
		$helper->token_adminstores = Tools::getAdminTokenLite('AdminStores');

		return $helper->generateForm($fields_form);

	}

	// Capturar contendido del formulario creado en el método displayForm().
	public function getContent()
	{
		$output = null;	// Limpia la variable de salida
		if (Tools::isSubmit('submit'.$this->name))	// Si hiciste click en submit has lo siguiente:
		{
			// captura los valores del formulario (displayForm()) en variables para proceder a validarlas.
			$bank_wire_details = strval(Tools::getValue('BANK_WIRE_DETAILS'));
			$bank_wire_owner = strval(Tools::getValue('BANK_WIRE_OWNER'));
			$bank_wire_address = strval(Tools::getValue('BANK_WIRE_ADDRESS'));

			// Si las variables están vacias o falsas realizar so siguiente:
			if (!$bank_wire_details  || empty($bank_wire_details) || !$bank_wire_owner || empty($bank_wire_owner) || !$bank_wire_address || empty($bank_wire_address))
			{
				// Asignas el valor de la variable al sistema... (para que al momento de salir el error salgo lo último que pusiste)
				Configuration::updateValue('BANK_WIRE_DETAILS', $bank_wire_details);
				Configuration::updateValue('BANK_WIRE_OWNER', $bank_wire_owner);
				Configuration::updateValue('BANK_WIRE_ADDRESS', $bank_wire_address);
				//... y muestras el error indicando que faltan completar datos
				$output .= $this->displayError( $this->l('Complete all fields required'));
			}
			else
			{
				// Caso contrarios, Si los campos fueron colocados correctamente asigna las variables al sistema
				// Configuration::updateValue('VALUE',$value) hace que lo que tiene $value lo suba al sistema con el nombre VALUE
				Configuration::updateValue('BANK_WIRE_DETAILS', $bank_wire_details);
				Configuration::updateValue('BANK_WIRE_OWNER', $bank_wire_owner);
				Configuration::updateValue('BANK_WIRE_ADDRESS', $bank_wire_address);
				$output .= $this->displayConfirmation($this->l('Settings updated'));
			}
		}
		// Mostrar en la salida el Formulario  :-)
		return $output.$this->displayForm();
	}


	// HookPayment: este hook hace que mi módulo se liste en los métodos de pago
	public function hookPayment($params)
	{
		// Compruebas activdad del módulo
		if (!$this->active)
			return;
		// Compruebas Tipo de móneda del carrito
		if (!$this->checkCurrency($params['cart']))
			return;

		// Asignas las variables al TPL [etexamplepayment/views/templates/hook/payment.tpl]
		$this->smarty->assign(array(
			'this_path' => $this->_path,
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'	// variable que me dará la URl del módulo, sirve para indicar imagenes o links. el resultado es:  http://www.midominio.com/modules/nombremodulo/
		));

		// indicas que el tpl que usarás para mostrar este hook será payment.tpl
		return $this->display(__FILE__, 'payment.tpl');
	}

	// HookPaymentReturm: este hook muestra la página de confirmación de pedido final, donde salen los datos de la empresa, numeros de cuenta, etc.
	public function hookPaymentReturn($params)
	{
		// Compruebas activdad del módulo
		if (!$this->active)
			return;

		$state = $params['objOrder']->getCurrentState();	// Captura el estado actual del carrito (el que se colocó en payment.php) [PS_OS_EXAMPLE]

		// Si el estado de PS_OS_EXAMPLE es igual al $state o el stado es PS_OS_OUTOFSTOCK realiza lo siguiente:
		if ($state == Configuration::get('PS_OS_EXAMPLE') || $state == Configuration::get('PS_OS_OUTOFSTOCK'))
		{
			// Asignando variables Smarty al TPL [etexamplepayment/views/templates/hook/payment_return.tpl]
			$this->smarty->assign(array(
				'total_to_pay' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
				'bankwireDetails' => Tools::nl2br($this->details),
				'bankwireAddress' => Tools::nl2br($this->address),
				'bankwireOwner' => $this->owner,
				'status' => 'ok',	// Si la transacción es corecta el status será "ok"  caso contrario será "failed" (linea:272)
				'id_order' => $params['objOrder']->id
			));
			if (isset($params['objOrder']->reference) && !empty($params['objOrder']->reference))
				$this->smarty->assign(
					'reference', $params['objOrder']->reference
					);
		}
		else
			$this->smarty->assign('status', 'failed');

		// indicas que el tpl que usarás para mostrar este hook será payment_return.tpl
		return $this->display(__FILE__, 'payment_return.tpl');
	}

	// Validas los tipos de monedas selecciondos en el backoffice con el carrito actual.
	public function checkCurrency($cart)
	{
		$currency_order = new Currency($cart->id_currency);
		$currencies_module = $this->getCurrency($cart->id_currency);

		if (is_array($currencies_module))
			foreach ($currencies_module as $currency_module)
				if ($currency_order->id == $currency_module['id_currency'])
					return true;
		return false;
	}
}
