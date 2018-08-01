<?php

namespace Vindi\Payment\Model\Payment;


use Magento\Framework\Module\ModuleListInterface;
use Vindi\Payment\Helper\Data;

class Api extends \Magento\Framework\Model\AbstractModel
{
    private $apiKey;

    public function __construct(
        Data $helperData,
        ModuleListInterface $moduleList,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Message\ManagerInterface $messageManager
    )
    {
        $this->apiKey = $helperData->getModuleConfig("api_key");
        $this->base_path = $helperData->getModuleConfig("api_endpoint");

        $this->moduleList = $moduleList;
        $this->logger = $logger;
        $this->messageManager = $messageManager;

    }

    public function request($endpoint, $method = 'POST', $data = [], $dataToLog = null)
    {
        if (!$this->apiKey) {
            return false;
        }
        $url = $this->base_path . $endpoint;
        $body = $this->buildBody($data);
        $requestId = rand();
        $dataToLog = null !== $dataToLog ? $this->buildBody($dataToLog) : $body;
        $this->logger->info(__(sprintf('[Request #%s]: New Api Request.\n%s %s\n%s', $requestId, $method, $url,
            $dataToLog)));
        $ch = curl_init();
        $ch_options = [
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => 'Vindi-Magento/' . $this->getVersion(),
            CURLOPT_SSLVERSION => 'CURL_SSLVERSION_TLSv1_2',
            CURLOPT_USERPWD => $this->apiKey . ':',
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => $method
        ];
        if (!empty($body)) {
            $ch_options[CURLOPT_POSTFIELDS] = $body;
        }
        curl_setopt_array($ch, $ch_options);
        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $body = substr($response, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
        if (curl_errno($ch) || $response === false) {
            $this->logger->error(__(sprintf('[Request #%s]: Error while executing request!\n%s', $requestId, print_r($response, true))));
            return false;
        }
        curl_close($ch);
        $status = "HTTP Status: $statusCode";
        $this->logger->info(__(sprintf('[Request #%s]: New API Answer.\n%s\n%s', $requestId, $status, $body)));
        $responseBody = json_decode($body, true);
        if (!$responseBody) {
            $this->logger->info(__(sprintf('[Request #%s]: Error while recovering request body! %s', $requestId,
                print_r($body, true))));
            return false;
        }
        if (!$this->checkResponse($responseBody, $endpoint)) {
            return false;
        }
        return $responseBody;
    }

    /**
     * Make an API request to create a Customer.
     *
     * @param array $body (name, email, code)
     *
     * @return array|bool|mixed
     */
    public function createCustomer($body)
    {
        if ($response = $this->request('customers', 'POST', $body)) {
            return $response['customer']['id'];
        }

        return false;
    }

    /**
     * Make an API request to retrieve an existing Customer.
     *
     * @param string $code
     *
     * @return array|bool|mixed
     */
    public function findCustomerByCode($code)
    {
        $response = $this->request("customers/search?code={$code}", 'GET');

        if ($response && (1 === count($response['customers'])) && isset($response['customers'][0]['id'])) {
            return $response['customers'][0]['id'];
        }

        return false;
    }

    private function buildBody($data)
    {
        $body = null;

        if (!empty($data)) {
            $body = json_encode($data);
        }

        return $body;
    }

    public function getVersion()
    {
        return $this->moduleList
            ->getOne('Vindi_Payment')['setup_version'];
    }

    /**
     * @param array $response
     * @param       $endpoint
     *
     * @return bool
     */
    private function checkResponse($response, $endpoint)
    {
        if (isset($response['errors']) && !empty($response['errors'])) {
            foreach ($response['errors'] as $error) {
                $message = $this->getErrorMessage($error, $endpoint);

                $this->messageManager->addErrorMessage($message);

                $this->lastError = $message;
            }

            return false;
        }

        $this->lastError = '';

        return true;
    }

    /**
     * @param array $error
     * @param       $endpoint
     *
     * @return string
     */
    private function getErrorMessage($error, $endpoint)
    {
        return "Erro em $endpoint: {$error['id']}: {$error['parameter']} - {$error['message']}";
    }
}