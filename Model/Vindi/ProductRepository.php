<?php

namespace Vindi\Payment\Model\Vindi;

use Vindi\Payment\Api\ProductRepositoryInterface;
use Vindi\Payment\Helper\Api;
use Vindi\Payment\Helper\Data;

/**
 * Class ProductRepository
 * @package Vindi\Payment\Model\Vindi
 */
class ProductRepository implements ProductRepositoryInterface
{
    /**
     * @var Api
     */
    private $api;

    /**
     * ProductRepository constructor.
     * @param Api $api
     */
    public function __construct(
        Api $api
    ) {
        $this->api = $api;
    }

    /**
     * @inheritDoc
     */
    public function findOrCreateProduct($itemSku, $itemName, $itemType = 'simple')
    {
        $itemName = $itemType == 'configurable' ? $itemSku : $itemName;
        $itemSku = Data::sanitizeItemSku($itemSku);
        $vindiProductId = $this->findProductByCode($itemSku);

        if ($vindiProductId) {
            return $vindiProductId;
        }

        $body = [
            'code' => $itemSku,
            'name' => $itemName,
            'status' => 'active',
            'pricing_schema' => [
                'price' => 0,
            ],
        ];

        $response = $this->api->request('products', 'POST', $body);

        if ($response) {
            return $response['product']['id'];
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function findProductByCode($code)
    {
        $response = $this->api->request("products?query=code%3D{$code}", 'GET');

        if ($response && (1 === count($response['products'])) && isset($response['products'][0]['id'])) {
            return $response['products'][0]['id'];
        }

        return false;
    }
}
