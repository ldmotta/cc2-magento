<?php
/**
 * Aviso_CorreioControl Customer class
 * 
 * PHP version 5.3
 * 
 * @category plugin
 * @package   Aviso_CorreioControl
 * @author    ldmotta <ldmotta@visie.com.br>
 * @copyright 2013 ldmotta
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @version   GIT: <0.1.0>
 * @link      http://www.motanet.com.br/
 *
 */

/** Aviso_CorreioControl_Model_Customer
 * 
 * @category plugin
 * @package  Aviso_CorreioControl
 * 
 * @author   ldmotta <ldmotta@visie.com.br>
 * @license  http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @version  Release: <package_version>
 * @link     http://www.motanet.com.br/
 * 
 * 
 */

class Aviso_CorreioControl_Model_Customer 
{
    /**
     * Sends a message to the shop admin about a new customer registration
     * 
     * @param Mage_Customer_Model_Customer $customer customer object
     * @param Mage_Sales_Model_Order       $order    order object
     * 
     * @return boolean
     */
    public function newCustomer($customer, $order)
    {
        try {
            $storeId = $order->getStoreId();

            $templateId = Mage::getStoreConfig(
                'sales_email/order/new_customer_template', 
                $storeId
            );

            $mailer = Mage::getModel('core/email_template_mailer');
            $emailInfo = Mage::getModel('core/email_info');
            $emailInfo->addTo(
                Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_IDENTITY)
            );

            $mailer->addEmailInfo($emailInfo);

            // Set all required params and send emails
            $mailer->setSender(
                Mage::getStoreConfig(
                    Mage_Sales_Model_Order::XML_PATH_EMAIL_IDENTITY, 
                    $storeId
                )
            );
            $mailer->setStoreId($storeId);
            $mailer->setTemplateId($templateId);
            $mailer->setTemplateParams(
                array(
                    'customer'  => $customer
                )
            );
            $mailer->send();
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        } 
        
        return true;

    }
}