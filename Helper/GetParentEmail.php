<?php
/**
 * @category    M2Commerce Enterprise
 * @package     M2Commerce_MultiCustomerManagement
 * @copyright   Copyright (c) 2023 M2Commerce Enterprise
 * @author      dawoodgondaldev@gmail.com
 */

namespace M2Commerce\MultiCustomerManagement\Helper;

use Magento\Customer\Model\Customer;
use Magento\Setup\Exception;
use Magento\Store\Model\StoreManagerInterface;

/**
 * GetParentEmail
 */
class GetParentEmail
{
    /**
     * @var Customer
     */
    protected $customer;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Customer $customer
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Customer              $customer,
        StoreManagerInterface $storeManager
    ) {
        $this->customer = $customer;
        $this->storeManager = $storeManager;
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getWebsiteId(): int
    {
        return $this->storeManager->getStore()->getWebsiteId();
    }

    /**
     * @param $email
     * @return string
     */
    public function getMultiCustomerParentEmail($email)
    {
        $multiCustomer = '';
        try {
            $customer = $this->customer->setWebsiteId($this->getWebsiteId());
            $customer = $customer->loadByEmail($email);
            if ($customer->getId()) {
                $multiCustomer = $customer->getMulticustomerParentEmail();
            }
        } catch (\Exception $e) {
        }
        return $multiCustomer;
    }
}
