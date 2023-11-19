<?php
/**
 * @category    M2Commerce Enterprise
 * @package     M2Commerce_MultiCustomerManagement
 * @copyright   Copyright (c) 2023 M2Commerce Enterprise
 * @author      dawoodgondaldev@gmail.com
 */

namespace M2Commerce\MultiCustomerManagement\Block;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Http\Context;

/**
 * customer
 */
class Customer extends \Magento\Framework\View\Element\Template
{
    /**
     * @var UserContextInterface
     */
    protected $userContext;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var Context
     */
    protected $httpContext;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param CustomerFactory $customerFactory
     * @param UserContextInterface $userContext
     * @param Context $httpContext
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        CustomerRepositoryInterface $customerRepositoryInterface,
        CustomerFactory $customerFactory,
        UserContextInterface $userContext,
        Context $httpContext
    ) {
        $this->customerFactory = $customerFactory;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->userContext = $userContext;
        $this->httpContext = $httpContext;
        parent::__construct($context);
    }

    /**
     * @return int|null
     */
    public function getLoginCustomerId()
    {
        return $this->userContext->getUserId();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * @param $customerId
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     */
    public function getCustomerById($customerId)
    {
        try {
            return $this->customerRepositoryInterface->getById($customerId);
        } catch (\Exception $exception) {
        }
        return null;
    }

    /**
     * @param $multicustomerParentEmail
     * @return mixed
     */
    public function getFilteredCustomerCollection($multicustomerParentEmail)
    {
        return $this->customerFactory->create()->getCollection()
            ->addAttributeToSelect("entity_id")
            ->addAttributeToSelect("multicustomer_parent_email")
            ->addAttributeToFilter("multicustomer_parent_email", ["eq" => $multicustomerParentEmail])
            ->load();
    }
}
