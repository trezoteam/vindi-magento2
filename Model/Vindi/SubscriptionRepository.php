<?php

namespace Vindi\Payment\Model\Vindi;

use Vindi\Payment\Api\SubscriptionRepositoryInterface;
use Vindi\Payment\Helper\Api;

/**
 * Class SubscriptionRepository
 * @package Vindi\Payment\Model\Vindi
 */
class SubscriptionRepository implements SubscriptionRepositoryInterface
{
    /**
     * @var Api
     */
    private $api;

    /**
     * SubscriptionRepository constructor.
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
    public function create($data = [])
    {
        if ($response = $this->api->request('subscription', 'POST', $data)) {
            return $response;
        }

        return false;
    }
}
