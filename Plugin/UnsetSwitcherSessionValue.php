<?php
/**
 * @category    M2Commerce Enterprise
 * @package     M2Commerce_MultiCustomerManagement
 * @copyright   Copyright (c) 2023 M2Commerce Enterprise
 * @author      dawoodgondaldev@gmail.com
 */

namespace M2Commerce\MultiCustomerManagement\Plugin;

use Magento\Customer\Controller\Account\Logout;
use Magento\Customer\Model\Session;

/**
 * Invalidate expired and not active Login as Customer sessions.
 */
class UnsetSwitcherSessionValue
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @param Session $session
     */
    public function __construct(
        Session $session,
    ) {
        $this->customerSession = $session;
    }

    /**
     * @param Logout $subject
     * @return void
     */
    public function beforeExecute(Logout $subject)
    {
        if ($this->customerSession->getLoginFromSwitcher()) {
            $this->customerSession->unsLoginFromSwitcher();
        }
    }
}
