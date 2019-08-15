<?php

namespace Model\Ingpsp;

/**
 * Ingpsp Row gateway
 *
 * @author bojan
 */
class IngpspGateway 
{

    private $db;

    public function __construct(\DbCore $db) 
    {
        $this->db = $db;
    }

    
    /**
     * save instance to ingpsp table
     * 
     * @param int $responseId
     * @param int $cartId
     * @param string $customerSecureKey
     * @param string $type
     * @param string $currentOrder
     * @param string $reference
     */
    public function save(Ingpsp $ingpsp) 
    {
        if($ingpsp->getGingerOrderId() !== null) {
            $this->_deleteByCartId($ingpsp->getIdCart());
            $this->_saveOrder(
                    $ingpsp->getGingerOrderId(), 
                    $ingpsp->getIdCart(), 
                    $ingpsp->getKey(), 
                    $ingpsp->getPaymentMethod(),
                    $ingpsp->getIdOrder(),
                    $ingpsp->getReference()
                    );
        }
    }

    private function _deleteByCartId($cartId) 
    {
        try {
            $this->db->Execute("DELETE FROM `" . \_DB_PREFIX_ . "ingpsp` WHERE `id_cart` = " . $cartId);
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
            $this->db->Execute("INSERT INTO `" . \_DB_PREFIX_ . "ingpsp`
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
                                'SELECT * FROM `%s` WHERE `id_cart` = \'%s\'', _DB_PREFIX_ . 'ingpsp', $cartId
                        )
        );
        $ingpsp = new Ingpsp();
        if (is_array($row) && count($row)) {
            $ingpsp->setGingerOrderId(isset($row['ginger_order_id']) ? $row['ginger_order_id'] : null) 
                    ->setIdCart(isset($row['id_cart']) ? $row['id_cart'] : null)
                    ->setIdOrder(isset($row['id_order']) ? $row['id_order'] : null)
                    ->setKey(isset($row['key']) ? $row['key'] : null)
                    ->setPaymentMethod(isset($row['payment_method']) ? $row['payment_method'] : null)
                    ->setReference(isset($row['reference']) ? $row['reference'] : null);
        }
        return $ingpsp;
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
                    "UPDATE  `" . \_DB_PREFIX_ . "ingpsp` "
                    . "SET `id_order` =  $orderId  "
                    . "WHERE `id_cart` = " . $cartId
                    );
        } catch (\Exception $e) {
            return false;
        }
    }

}
