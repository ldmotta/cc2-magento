<?php
/**
 * Aviso_CorreioControl Source class
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

class Aviso_CorreioControl_Model_Source_Modality
{

    public function toOptionArray()
    {
        $KEY = $this->_getConfigData('auth_apikey');
        $data = array(
            'id' => $this->_getConfigData('auth_id'),
            'token' => $this->_make_token($KEY),
            'group' => $this->_getConfigData('auth_group')
        );

        $url = $this->_getConfigData('action_url') . '/obtermodalidades/?';
        $url .= http_build_query($data);

        try {
            $object = new Zend_Http_Client();
            $object->setUri($url);
            $object_data = $object->request();
            $result = json_decode($object_data->getBody());
        } catch (Exception $e) {
            return array(array('value'=>0, 'label'=>Mage::helper('adminhtml')->__($e->getMessage())));
        }

        $result_info = array();
        foreach ($result as $value) {
            $result_info[] = array('value'=>$value->id, 'label'=>Mage::helper('adminhtml')->__($value->nome));
            
        }

        return $result_info;        
    }

    function _getConfigData($field)
    {
        return Mage::getStoreConfig(
                    'carriers/aviso_correiocontrol/' . $field,
                    Mage::app()->getStore()
                );
    }

    function _make_token($key)
    {
        return substr(md5(date("Ymd") . $key), 0, 8);
    }
}
