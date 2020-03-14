<?php

namespace Vindi\Payment\Api;

/**
 * Interface ProductManagementInterface
 * @package Vindi\Payment\Api
 */
interface ProductManagementInterface
{
    /**
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function findOrCreateProductsFromOrder(\Magento\Sales\Model\Order $order);
}
