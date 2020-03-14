<?php

namespace Vindi\Payment\Model\Vindi;

use Magento\Sales\Model\Order;
use Vindi\Payment\Api\ProductManagementInterface;
use Vindi\Payment\Api\ProductRepositoryInterface;

/**
 * Class ProductManagement
 * @package Vindi\Payment\Model\Vindi
 */
class ProductManagement implements ProductManagementInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * ProductManagement constructor.
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    /**
     * @inheritDoc
     */
    public function findOrCreateProductsFromOrder(Order $order)
    {
        $list = [];
        foreach ($order->getItems() as $item) {
            $productType = $item->getProduct()->getTypeId();
            $vindiProductId = $this->productRepository
                ->findOrCreateProduct($item->getSku(), $item->getName(), $productType);

            for ($i = 0; $i < $item->getQtyOrdered(); $i++) {
                $itemPrice = $this->getItemPrice($item, $productType);

                if (! $itemPrice)
                    continue;

                array_push($list, [
                    'product_id' => $vindiProductId,
                    'amount' => $itemPrice
                ]);
            }
        }

        $list = $this->buildTax($list, $order);
        $list = $this->buildDiscount($list, $order);
        $list = $this->buildShipping($list, $order);

        return $list;
    }

    private function getItemPrice($item, $productType)
    {
        if ('bundle' == $productType)
            return 0;

        return $item->getPrice();
    }

    /**
     * @param array $list
     * @param Order $order
     * @return array
     */
    private function buildTax(array $list, Order $order)
    {
        if ($order->getTaxAmount() > 0) {
            $productId = $this->productRepository->findOrCreateProduct('taxa', 'Taxa');
            array_push($list, [
                'product_id' => $productId,
                'amount' => $order->getTaxAmount()
            ]);
        }

        return $list;
    }

    /**
     * @param array $list
     * @param Order $order
     * @return array
     */
    private function buildDiscount(array $list, Order $order)
    {
        if ($order->getDiscountAmount() < 0) {
            $productId = $this->productRepository->findOrCreateProduct('cupom', 'Cupom de Desconto');
            array_push($list, [
                'product_id' => $productId,
                'amount' => $order->getDiscountAmount()
            ]);
        }

        return $list;
    }

    /**
     * @param array $list
     * @param Order $order
     * @return array
     */
    private function buildShipping(array $list, Order $order)
    {
        if ($order->getShippingAmount() > 0) {
            $productId = $this->productRepository->findOrCreateProduct('frete', 'frete');
            array_push($list, [
                'product_id' => $productId,
                'amount' => $order->getShippingAmount()
            ]);
        }

        return $list;
    }
}