<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */
namespace Buzzi\PublishCustomerLogout\Observer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\Observer;
use Buzzi\PublishCustomerLogout\Model\DataBuilder;

class CustomerLogout implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Buzzi\Publish\Model\Config\Events
     */
    private $configEvents;

    /**
     * @var \Buzzi\Publish\Api\QueueInterface
     */
    private $queue;

    /**
     * @var \Buzzi\PublishCustomerLogout\Model\DataBuilder
     */
    private $dataBuilder;

    /**
     * @var \Magento\Store\Api\StoreResolverInterface
     */
    private $storeResolver;

    /**
     * @var \Buzzi\Publish\Helper\ExceptsMarketing
     */
    private $exceptsMarketingHelper;

    /**
     * @param \Buzzi\Publish\Model\Config\Events $configEvents
     * @param \Buzzi\Publish\Api\QueueInterface $queue
     * @param \Buzzi\PublishCustomerLogout\Model\DataBuilder $dataBuilder
     * @param \Magento\Store\Api\StoreResolverInterface $storeResolver
     * @param \Buzzi\Publish\Helper\ExceptsMarketing|null $exceptsMarketingHelper
     */
    public function __construct(
        \Buzzi\Publish\Model\Config\Events $configEvents,
        \Buzzi\Publish\Api\QueueInterface $queue,
        \Buzzi\PublishCustomerLogout\Model\DataBuilder $dataBuilder,
        \Magento\Store\Api\StoreResolverInterface $storeResolver,
        \Buzzi\Publish\Helper\ExceptsMarketing $exceptsMarketingHelper = null
    ) {
        $this->configEvents = $configEvents;
        $this->queue = $queue;
        $this->dataBuilder = $dataBuilder;
        $this->storeResolver = $storeResolver;
        $this->exceptsMarketingHelper = $exceptsMarketingHelper ?: ObjectManager::getInstance()->get(\Buzzi\Publish\Helper\ExceptsMarketing::class);
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $observer->getData('customer');
        $storeId = $this->storeResolver->getCurrentStoreId();

        if (!$this->configEvents->isEventEnabled(DataBuilder::EVENT_TYPE, $storeId)
            || !$this->exceptsMarketingHelper->isExcepts(DataBuilder::EVENT_TYPE, $storeId, $customer->getDataModel())
        ) {
            return;
        }

        $payload = $this->dataBuilder->getPayload($customer->getId());

        if ($this->configEvents->isCron(DataBuilder::EVENT_TYPE, $storeId)) {
            $this->queue->add(DataBuilder::EVENT_TYPE, $payload, $storeId);
        } else {
            $this->queue->send(DataBuilder::EVENT_TYPE, $payload, $storeId);
        }
    }
}
