<?php

namespace Riskified\Decider\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Riskified\Decider\Model\Api\Api;
use Riskified\Decider\Model\Api\Log as LogApi;
use Riskified\Decider\Model\Api\Order as OrderApi;

class SalesOrderShipmentSaveAfter implements ObserverInterface
{
    /**
     * @var Api
     */
    private Api $api;

    /**
     * @var LogApi
     */
    private LogApi $logger;

    /**
     * @var OrderApi
     */
    private OrderApi $apiOrderLayer;

    /**
     * SalesOrderShipmentSaveAfter constructor.
     *
     * @param Api $api
     * @param LogApi $logger
     * @param OrderApi $orderApi
     */
    public function __construct(Api $api, LogApi $logger, OrderApi $orderApi)
    {
        $this->api = $api;
        $this->logger = $logger;
        $this->apiOrderLayer = $orderApi;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $observer->getShipment();
        $this->api->initSdk($shipment->getOrder());

        try {
            $this->apiOrderLayer->post($shipment, Api::ACTION_FULFILL);
        } catch (\Exception $e) {
            $this->logger->log(
                sprintf(
                    __("Order fulfilment was not able to sent. Order #%s, shipment #%s"),
                    $shipment->getOrder()->getIncrementId(),
                    $shipment->getIncrementId()
                )
            );
        }
    }
}
