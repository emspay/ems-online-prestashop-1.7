<?php

namespace Lib\components;

trait GingerOrderLinesTrait
{

    /**
     * @param $cart
     * @return array
     */
    public function getOrderLines($cart)
    {
        $orderLines = [];

        foreach ($cart->getProducts() as $key => $product) {
            $orderLines[] = array_filter([
                'ean' => $this->getProductEAN($product),
                'url' => $this->getProductURL($product),
                'name' => $product['name'],
                'type' => GingerOrderBuilder::PHYSICAL,
                'amount' => $this->orderBuilder->getAmountInCents(\Tools::ps_round($product['price_wt'], 2)),
                'currency' => $this->orderBuilder->getOrderCurrency(),
                'quantity' => (int)$product['cart_quantity'],
                'image_url' => $this->getProductCoverImage($product),
                'vat_percentage' => ((int) $product['rate'] * 100),
                'merchant_order_line_id' => $product['unique_id']
            ], function ($var) {
                return !is_null($var);
            });
        }

        $shippingFee = $cart->getOrderTotal(true, \Cart::ONLY_SHIPPING);

        if ($shippingFee > 0) {
            $orderLines[] = $this->getShippingOrderLine($cart, $shippingFee);
        }

        return count($orderLines) > 0 ? $orderLines : null;
    }

    /**
     * @param $product
     * @return string|null
     */
    public function getProductEAN($product)
    {
        return (key_exists('ean13', $product) && strlen($product['ean13']) > 0) ? $product['ean13'] : null;
    }

    /**
     * @param $product
     * @return string|null
     */
    public function getProductURL($product)
    {
        $productURL = $this->context->link->getProductLink($product);

        return strlen($productURL) > 0 ? $productURL : null;
    }

    /**
     * @param $cart
     * @param $shippingFee
     * @return array
     */
    public function getShippingOrderLine($cart, $shippingFee)
    {
        return [
            'name' => $this->l("Shipping Fee"),
            'type' => GingerOrderBuilder::SHIPPING_FEE,
            'amount' => $this->orderBuilder->getAmountInCents($shippingFee),
            'currency' => $this->orderBuilder->getPaymentCurrency(),
            'vat_percentage' => $this->orderBuilder->getAmountInCents($this->getShippingTaxRate($cart)),
            'quantity' => 1,
            'merchant_order_line_id' => (string)(count($cart->getProducts()) + 1)
        ];
    }

    /**
     * @param $product
     * @return mixed
     */
    public function getProductCoverImage($product)
    {
        $productCover = \Product::getCover($product['id_product']);

        if ($productCover)
        {
            $link_rewrite = $product['link_rewrite'] ?? \Db::getInstance()->getValue('SELECT link_rewrite FROM '._DB_PREFIX_.'product_lang WHERE id_product = '.(int) $product['id_product']);
            return $this->context->link->getImageLink($link_rewrite, $productCover['id_image']);
        }
    }

    /**
     * @param $cart
     * @return mixed
     */
    public function getShippingTaxRate($cart)
    {
        $carrier = new \Carrier((int) $cart->id_carrier, (int) $this->context->cart->id_lang);

        return $carrier->getTaxesRate(
            new Address((int) $this->context->cart->{\Configuration::get('PS_TAX_ADDRESS_TYPE')})
        );
    }

    public function getOrderLinesForRefunds($order)
    {
        $orderLines = [];

        foreach ($order->getProducts() as $key => $product) {
            $orderLines[] = array_filter([
                'ean' => $this->getProductEAN($product),
                'url' => $this->getProductURL($product),
                'name' => $product['product_name'],
                'type' => GingerOrderBuilder::PHYSICAL,
                'amount' => $this->orderBuilder->getAmountInCents(\Tools::ps_round($product['product_price_wt'], 2)),
                'currency' => $this->orderBuilder->getOrderCurrency(),
                'quantity' => (int) $product['product_quantity'],
                'image_url' => $this->getProductCoverImage($product),
                'vat_percentage' => ((int) $product['tax_rate'])   * 100,
                'merchant_order_line_id' => $product['product_id']
            ], function ($var) {
                return !is_null($var);
            });
        }

        return $orderLines;
    }
}