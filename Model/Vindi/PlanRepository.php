<?php

namespace Vindi\Payment\Model\Vindi;

use Magento\Framework\Exception\LocalizedException;
use Vindi\Payment\Api\PlanRepositoryInterface;
use Vindi\Payment\Helper\Api;

/**
 * Class PlanRepository
 * @package Vindi\Payment\Model\Vindi
 */
class PlanRepository implements PlanRepositoryInterface
{
    /**
     * @var Api
     */
    private $api;

    /**
     * PlanRepository constructor.
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
    public function save($data = []): int
    {
        $endpoint = 'plans';
        $method = 'POST';

        if ($plan = $this->findOneByCode($data['code'])) {
            $endpoint .= '/' . $plan['id'];
            $method = 'PUT';
        }

        $response = $this->api->request($endpoint, $method, $data);
        if (!$response) {
            throw new LocalizedException(__('The plan could not be saved'));
        }

        return $response['plan']['id'];
    }

    /**
     * @inheritDoc
     */
    public function findOneByCode($code)
    {
        $response = $this->api->request("plans?query=code%3D{$code}", 'GET');

        if ($response && (1 === count($response['plans'])) && isset($response['plans'][0]['id'])) {
            return reset($response['plans']);
        }

        return false;
    }
}
