<?php

namespace Vindi\Payment\Observer;

use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Vindi\Payment\Api\PlanManagementInterface;
use Vindi\Payment\Setup\UpgradeData;

/**
 * Class ProductLogObserver
 * @package Vindi\Payment\Observer
 */
class ProductSaveObserver implements ObserverInterface
{
    /**
     * @var AttributeSetRepositoryInterface
     */
    private $attributeSetRepository;
    /**
     * @var PlanManagementInterface
     */
    private $plansManagement;

    /**
     * ProductLogObserver constructor.
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     * @param PlanManagementInterface $plansManagement
     */
    public function __construct(
        AttributeSetRepositoryInterface $attributeSetRepository,
        PlanManagementInterface $plansManagement
    ) {
        $this->attributeSetRepository = $attributeSetRepository;
        $this->plansManagement = $plansManagement;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $product = $observer->getData('product');
        $attrSet = $this->attributeSetRepository->get($product->getAttributeSetId());
        if ($attrSet->getAttributeSetName() != UpgradeData::VINDI_PLANOS) {
            return;
        }

        $this->plansManagement->create($product);
    }
}
