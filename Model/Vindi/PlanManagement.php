<?php

namespace Vindi\Payment\Model\Vindi;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Vindi\Payment\Api\PlanRepositoryInterface;
use Vindi\Payment\Api\PlanManagementInterface;
use Vindi\Payment\Api\ProductRepositoryInterface as VindiProductRepositoryInterface;
use Vindi\Payment\Helper\Data;

/**
 * Class PlanManagement
 * @package Vindi\Payment\Model\Vindi
 */
class PlanManagement implements PlanManagementInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var PlanRepositoryInterface
     */
    private $planRepository;
    /**
     * @var VindiProductRepositoryInterface
     */
    private $vindiProductRepository;

    /**
     * PlansManagement constructor.
     * @param ProductRepositoryInterface $productRepository
     * @param VindiProductRepositoryInterface $vindiProductRepository
     * @param PlanRepositoryInterface $planRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        VindiProductRepositoryInterface $vindiProductRepository,
        PlanRepositoryInterface $planRepository
    ) {
        $this->productRepository = $productRepository;
        $this->planRepository = $planRepository;
        $this->vindiProductRepository = $vindiProductRepository;
    }

    /**
     * @param Product $product
     * @return int
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function create(Product $product)
    {
        if ($product->getTypeId() != Grouped::TYPE_CODE) {
            throw new LocalizedException(__('Product Type not support to plan'));
        }

        $planItems = $this->getPlanItems($product);

        $data = [
            'name' => $product->getName(),
            'code' => Data::sanitizeItemSku($product->getSku()),
            'plan_items' => $planItems,
            'interval' => $product->getVindiInterval(),
            'interval_count' => $product->getVindiIntervalCount(),
            'billing_trigger_type' => $product->getVindiBillingTriggerType(),
            'billing_trigger_day' => $product->getVindiBillingTriggerDay(),
            'billing_cycles' => $product->getVindiBillingCycles()
        ];

        return $this->planRepository->save($data);
    }

    /**
     * @param $plan
     * @return array
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    private function getPlanItems($plan)
    {
        $planItems = [];
        $associatedItemsIds = $this->getLinkedProductsIds($plan);
        foreach ($associatedItemsIds as $itemId) {
            $item = $this->productRepository->getById($itemId);
            $remoteItemId = $this->vindiProductRepository->findOrCreateProduct(
                $item->getSku(),
                $item->getName(),
                $item->getTypeId()
            );

            if (!$remoteItemId) {
                throw new LocalizedException(__('Error saving product'));
            }

            $planItems[] = [
                'cycles' => 1,
                'product_id' => $remoteItemId
            ];
        }

        return $planItems;
    }

    /**
     * @param $product
     * @return array
     */
    private function getLinkedProductsIds($product)
    {
        /** @var Grouped $typeInstance */
        $typeInstance = $product->getTypeInstance();
        return $typeInstance->getAssociatedProductIds($product);
    }
}
