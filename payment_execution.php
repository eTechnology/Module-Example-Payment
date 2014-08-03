<?php
// ESTE PAYMENT es el mismo que el de /Controllers/front/validation  solo que es para las version 1.4 de Prestashop

/**
 * @deprecated 1.5.0 This file is deprecated, use moduleFrontController instead
 */

/* SSL Management */
$useSSL = true;

require('../../config/config.inc.php');
Tools::displayFileAsDeprecated();

// init front controller in order to use Tools::redirect
$controller = new FrontController();
$controller->init();

Tools::redirect(Context::getContext()->link->getModuleLink('etexamplepayment', 'payment_execution'));