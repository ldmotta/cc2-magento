<?php
/**
 * Aviso_CorreioControl Export class
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

/** Aviso_CorreioControl_Model_Export
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
class Aviso_CorreioControl_Model_Export 
{
// extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface
    /**
     * Value and Weight
     */
    protected $_packageValue            = null;
    protected $_packageWeight           = null;
    protected $_volumeWeight            = null;
    protected $_freeMethodWeight        = null;
    
    /**
     * Generates an XML file from the order data and places it into
     * the var/export directory
     * 
     * @param Mage_Sales_Model_Order $order order object
     * 
     * @return boolean
     */
    public function exportOrder($shipment) 
    {
        if (!$this->_getConfigData('active'))
        {
            //Disabled
            Mage::log('Correio_Control: Disabled');
            return false;
        }

        // Instancia o pedido
        $order = $shipment->getOrder();

        // Pega os dadierdos de envio
        $shipping_address = $order->getShippingAddress();

        $full_name = $shipping_address->getFirstname() . ' ' . $shipping_address->getLastname(); 

        // Instancia o active shipping method
        $carrier = $this->_getActiveCarrierInstance();

        $weight = $order->getWeight();

        if ($carrier->getConfigData('weight_type')=='kg') {
            $weight = number_format($weight*1000, 0, '', '');
        }

        $KEY = $this->_getConfigData('auth_apikey');
        $data = array(
            // Autenticação e configuração
            'id'            => $this->_getConfigData('auth_id'),
            'token'         => $this->_make_token($KEY),
            'grupo'         => $this->_getConfigData('auth_group'),
            'atribuir'      => $this->_getConfigData('assign_record'),
            // Destinatário
            'nome'          => $full_name,
            'endereco'      => $shipping_address->getStreet(1),
            'bairro'        => $shipping_address->getStreet(2),
            'cep'           => $shipping_address->getPostcode(),
            'cidade'        => $shipping_address->getCity(),
            'uf'            => $shipping_address->getRegion(),
            'email'         => $shipping_address->getEmail(),
            // Dados do Objeto
            'modalidade'    => $this->_explode_modality_pt($order->getShippingMethod()),
            'peso'          => $weight,
            'comprimento'   => $carrier->getConfigData('comprimento_padrao'),
            'largura'       => $carrier->getConfigData('largura_padrao'), 
            'altura'        => $carrier->getConfigData('altura_padrao'),
        );

        //$url = 'http://pdf.aviso.visie.com.br/correiocontrol/action/novoobjeto/?';
        $url = $this->_getUrlAction('novoobjeto');
        $url .= http_build_query($data);

        try {
            Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('sales')
                ->__("<b style='font-size: 15px'>Informações de monitoramento de objetos do CoreioControl</b>"));
            $object = new Zend_Http_Client();
            $object->setUri($url);
            $object_data = $object->request();
            $result = json_decode($object_data->getBody());
            if ($result->error){
                throw new Exception($result->msg);
            }
            Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('sales')->__($result->msg));
        } catch (Exception $e) {
            $arr_message = explode(';', $e->getMessage());
            foreach($arr_message as $msg){
                Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('sales')->__($msg));                
            }
            Mage::log($e->getMessage());
            return false;
        }

        return true;
    }

    function _getAllCarriers()
    {
        $methods = Mage::getSingleton('shipping/config')->getActiveCarriers();
        $shipping = array();
        foreach($methods as $_ccode => $_carrier) {
            if($_methods = $_carrier->getAllowedMethods())  {
                if(!$_title = Mage::getStoreConfig("carriers/$_ccode/title"))
                    $_title = $_ccode;
                foreach($_methods as $_mcode => $_method)   {
                    $_code = $_ccode . '_' . $_mcode;
                    $shipping[$_code]=array('title' => $_method,'carrier' => $_title);
                }
            }
        }
        return $shipping;
    }

    function _getActiveCarrierInstance()
    {
        return Mage::getModel('shipping/config')->getCarrierInstance($this->_getCarrierCode(), 
                                                                    Mage::app()->getStore());        
    }

    function _getCarrierCode()
    {
        $carrierInstances = Mage::getSingleton('shipping/config')->getActiveCarriers();
    
        foreach ($carrierInstances as $code => $carrier) 
        {
            if ($carrier->isTrackingAvailable()) 
            {
                return $code;
            }
        }  

    }

    function _getConfigData($field)
    {
        return Mage::getStoreConfig(
                    'carriers/aviso_correiocontrol/' . $field,
                    Mage::app()->getStore()
                );
    }

    function _getUrlAction($action)
    {
        return $this->_getConfigData('action_url') . '/' . $action . '/?';
        // return 'http://local.host:8000/correiocontrol/action/' . $action . '/?';
    }

    function _make_token($key)
    {
        return substr(md5(date("Ymd") . $key), 0, 8);
    }

    function _explode_modality_pt($modality_string='')
    {
        $modality_arr = explode('_', $modality_string);
        return array_pop($modality_arr);
    }

}
