<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */
namespace Buzzi\PublishCustomerLogout\Model;

use Magento\Framework\DataObject;

class DataBuilder
{
    const EVENT_TYPE = 'buzzi.ecommerce.user-logout';

    /**
     * @var \Buzzi\Publish\Helper\DataBuilder\Base
     */
    protected $dataBuilderBase;

    /**
     * @var \Buzzi\Publish\Helper\DataBuilder\Customer
     */
    protected $dataBuilderCustomer;

    /**
     * @var \Magento\Customer\Model\CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventDispatcher;

    /**
     * @param \Buzzi\Publish\Helper\DataBuilder\Base $dataBuilderBase
     * @param \Buzzi\Publish\Helper\DataBuilder\Customer $dataBuilderCustomer
     * @param \Magento\Customer\Model\CustomerRegistry $customerRegistry
     * @param \Magento\Framework\Event\ManagerInterface $eventDispatcher
     */
    public function __construct(
        \Buzzi\Publish\Helper\DataBuilder\Base $dataBuilderBase,
        \Buzzi\Publish\Helper\DataBuilder\Customer $dataBuilderCustomer,
        \Magento\Customer\Model\CustomerRegistry $customerRegistry,
        \Magento\Framework\Event\ManagerInterface $eventDispatcher
    ) {
        $this->dataBuilderBase = $dataBuilderBase;
        $this->dataBuilderCustomer = $dataBuilderCustomer;
        $this->customerRegistry = $customerRegistry;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param int $customerId
     * @return mixed[]
     */
    public function getPayload($customerId)
    {
        $customer = $this->customerRegistry->retrieve($customerId);

        $payload = $this->dataBuilderBase->initBaseData(self::EVENT_TYPE);
        $payload['customer'] = $this->dataBuilderCustomer->getCustomerData($customer);

        $transport = new DataObject(['customer' => $customer, 'payload' => $payload]);
        $this->eventDispatcher->dispatch('buzzi_publish_customer_logout_payload', ['transport' => $transport]);

        return (array)$transport->getData('payload');
    }
}
