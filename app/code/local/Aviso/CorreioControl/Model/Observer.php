<?php
/**
 * Aviso_CorreioControl Observer class
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

/** Aviso_CorreioControl_Model_Observer
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

class Aviso_CorreioControl_Model_Observer extends Mage_Sales_Model_Order_Payment
{
    
    /**
     * Exports an order after it is placed
     * 
     * @param Varien_Event_Observer $observer observer object 
     * 
     * @return boolean
     */
    public function exportOrder(Varien_Event_Observer $observer) 
    {

        $shipment = $observer->getEvent()->getShipment();

        Mage::getModel('aviso_correiocontrol/export')
            ->exportOrder($shipment);
        
        return true;
        
    }
    
    /**
     * Sends an email to the admin after a customer places his first order
     * online
     * 
     * @param Varien_Event_Observer $observer observer object
     * 
     * @return boolean
     */
    public function newCustomer(Varien_Event_Observer $observer)
    {
        $orderIds = $observer->getEvent()->getOrderIds();
        
        if (!is_array($orderIds) || (!array_key_exists(0, $orderIds))) {
            return;
        }
        
        $order = Mage::getModel('sales/order')->load($orderIds[0]);
        
        if (!$order->getId()) {
            return;
        }
        
        if (!$order->getCustomerId()) {
            //send a message only for registered customers
            return;
        }
        
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
        
        if (!$customer->getId()) {
            return;
        }
        
        $customerOrders = Mage::getModel('sales/order')
                ->getCollection()
                ->addAttributeToFilter('customer_id', $customer->getId());
        if (count($customerOrders) > 1) {
            // send a message only after the first order
            return;
        }
        
        return Mage::getModel('aviso_correiocontrol/customer')
            ->newCustomer($customer, $order);        
    }
}