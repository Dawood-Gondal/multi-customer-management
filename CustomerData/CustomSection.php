<?php
/**
 * @category    M2Commerce Enterprise
 * @package     M2Commerce_MultiCustomerManagement
 * @copyright   Copyright (c) 2023 M2Commerce Enterprise
 * @author      dawoodgondaldev@gmail.com
 */

namespace M2Commerce\MultiCustomerManagement\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use M2Commerce\MultiCustomerManagement\Block\Customer;

/**
 * CustomSection
 */
class CustomSection implements SectionSourceInterface
{
    /**
     * @var Customer
     */
    protected $customer;

    /**
     * @param Customer $customer
     */
    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }

    /**
     * @return string[]
     * @throws \Exception
     */
    public function getSectionData()
    {
        try {
            $customer_id = $this->customer->getLoginCustomerId();
            if ($customer_id) {
                $current_customer = $this->customer->getCustomerById($customer_id);
                $multicustomer_parent_email = $current_customer->getCustomAttribute("multicustomer_parent_email");
                if ($multicustomer_parent_email) {
                    $multicustomer_parent_email = $multicustomer_parent_email->getValue();
                    $customers = $this->customer->getFilteredCustomerCollection($multicustomer_parent_email);
                    if ($customers->getItems() && sizeof($customers->getItems()) > 1) {
                        return [
                            'customdata' => "Switch Customer",
                        ];
                    }
                }
            }
            return [
                'customdata' => "",
            ];
        } catch (\Exception $e) {
            throw new \Exception(__($e->getMessage()));
        }
    }
}
