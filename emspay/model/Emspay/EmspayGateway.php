<?php

namespace Model\Emspay;

require_once (_PS_MODULE_DIR_.'/emspay/model/Emspay/Emspay.php');

/**
 * Emspay Row gateway
 *
 * @author bojan
 */
class EmspayGateway
{

    private $db;

    public function __construct(\DbCore $db) 
    {
        $this->db = $db;
    }

    
    /**
     * save instance to emspay table
     * 
     * @param int $responseId
     * @param int $cartId
     * @param string $customerSecureKey
     * @param string $type
     * @param string $currentOrder
     * @param string $reference
     */
    public function save(Emspay $emspay)
    {
        if($emspay->getGingerOrderId() !== null) {
            $this->_deleteByCartId($emspay->getIdCart());
            $this->_saveOrder(
                    $emspay->getGingerOrderId(),
                    $emspay->getIdCart(),
                    $emspay->getKey(),
                    $emspay->getPaymentMethod(),
                    $emspay->getIdOrder(),
                    $emspay->getReference()
                    );
        }
    }

    private function _deleteByCartId($cartId) 
    {
        try {
            $this->db->Execute("DELETE FROM `" . \_DB_PREFIX_ . "emspay` WHERE `id_cart` = " . $cartId);
        } catch (\Exception $e) {
            
        }
    }

    private function _saveOrder($responseId, $cartId, $customerSecureKey, $type, $currentOrder = null, $reference = null) 
    {
        try {
            $fields = ['`id_cart`',
                '`ginger_order_id`',
                '`key`',
                '`payment_method`'];
            $values = ['"' . $cartId . '"',
                '"' . $responseId . '"',
                '"' . $customerSecureKey . '"',
                '"' . $type . '"'];
            if ($currentOrder !== null) {
                array_push($fields, '`id_order`');
                array_push($values, '"' . $currentOrder . '"');
            }
            if ($reference !== null) {
                array_push($fields, '`reference`');
                array_push($values, '"' . $reference . '"');
            }
            $this->db->Execute("INSERT INTO `" . \_DB_PREFIX_ . "emspay`
		            (" . implode(',', $fields) . ")
                            VALUES
                                (" . implode(',', $values) . ");
                    ");
        } catch (\Exception $e) {
            
        }
    }

    
    /**
     * fetch order by cart id
     * 
     * @param int $cartId
     * @return array
     */
    public function getByCartId($cartId) 
    {
        $row = $this->db->getRow(
                        sprintf(
                                'SELECT * FROM `%s` WHERE `id_cart` = \'%s\'', _DB_PREFIX_ . 'emspay', $cartId
                        )
        );
        $emspay = new Emspay();
        if (is_array($row) && count($row)) {
            $emspay->setGingerOrderId(isset($row['ginger_order_id']) ? $row['ginger_order_id'] : null)
                    ->setIdCart(isset($row['id_cart']) ? $row['id_cart'] : null)
                    ->setIdOrder(isset($row['id_order']) ? $row['id_order'] : null)
                    ->setKey(isset($row['key']) ? $row['key'] : null)
                    ->setPaymentMethod(isset($row['payment_method']) ? $row['payment_method'] : null)
                    ->setReference(isset($row['reference']) ? $row['reference'] : null);
        }
        return $emspay;
    }
    
    /**
     * 
     * @param int $cartId
     * @param int $orderId
     */
    public function update($cartId, $orderId) 
    {
        try {
            $this->db->Execute(
                    "UPDATE  `" . \_DB_PREFIX_ . "emspay` "
                    . "SET `id_order` =  $orderId  "
                    . "WHERE `id_cart` = " . $cartId
                    );
        } catch (\Exception $e) {
            return false;
        }
    }

}
