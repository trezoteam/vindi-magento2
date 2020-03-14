<?php

namespace Vindi\Payment\Api;

/**
 * Interface PlansManagementInterface
 * @package Vindi\Payment\Api
 */
interface PlanManagementInterface
{
    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create(\Magento\Catalog\Model\Product $product);
}
