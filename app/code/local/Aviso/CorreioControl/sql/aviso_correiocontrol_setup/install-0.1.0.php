<?php
/**
 * Aviso_CorreioControl install script
 * 
 * PHP version 5.3
 * 
 * @category  plugin
 * @package   Aviso_CorreioControl
 * @author    ldmotta <ldmotta@visie.com.br>
 * @copyright 2013 ldmotta
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @version   GIT: <0.1.0>
 * @link      http://www.motanet.com.br/
 *
 */



$installer = $this;

$installer->startSetup();

$installer->run(
    "INSERT INTO `{$this->getTable('core_email_template')}` 
    (`template_code`, `template_text`, `template_type`, `template_subject`)
    VALUES (
        'New Customer and First Order',
        'A first order by a new customer: {{htmlescape var=\$customer.getName()}}, id: {{var=\$customer.getId()}}',
        '2',
        'A first order by a new customer'
    )"
);


$installer->endSetup();