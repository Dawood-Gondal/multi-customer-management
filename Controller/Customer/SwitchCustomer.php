<?php
/**
 * @category    M2Commerce Enterprise
 * @package     M2Commerce_MultiCustomerManagement
 * @copyright   Copyright (c) 2023 M2Commerce Enterprise
 * @author      dawoodgondaldev@gmail.com
 */

namespace M2Commerce\MultiCustomerManagement\Controller\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use M2Commerce\MultiCustomerManagement\Block\Customer;

/**
 * SwitchCustomer
 */
class SwitchCustomer extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var RedirectInterface
     */
    protected $_redirect;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var PhpCookieManager
     */
    private $cookieMetadataManager;

    /**
     * @var Customer
     */
    private $customerData;

    /**
     * @param Context $context
     * @param ResultFactory $resultFactory
     * @param Session $customerSession
     * @param ManagerInterface $messageManager
     * @param CustomerRepositoryInterface $customerRepository
     * @param Customer $customerData
     */
    public function __construct(
        Context $context,
        ResultFactory $resultFactory,
        Session $customerSession,
        ManagerInterface $messageManager,
        CustomerRepositoryInterface $customerRepository,
        Customer $customerData
    ) {
        $this->customerRepository = $customerRepository;
        $this->resultFactory = $resultFactory;
        $this->_customerSession = $customerSession;
        $this->_redirect = $context->getRedirect();
        $this->messageManager = $messageManager;
        $this->customerData = $customerData;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('customer_ids');
            if (!empty($login['old_customer_id']) && !empty($login['new_customer_id'])) {
                if($this->authenticate($login['old_customer_id'], $login['new_customer_id'])){
                    if($this->customerLogin($login['new_customer_id'])) {
                        $this->_customerSession->setLoginFromSwitcher("yes");
                        $this->messageManager->addSuccessMessage("Customer has been changed successfully.");
                        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                        $redirect->setUrl($this->_redirect->getRefererUrl());
                        return $redirect;
                    }
                }
            }
        }
        $this->messageManager->addErrorMessage("Something went wrong during customer switching.");
        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $redirect->setUrl($this->_redirect->getRefererUrl());
        return $redirect;
    }

    /**
     * @param $id
     * @return true
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    public function customerLogin($id)
    {
        try {
            $customer = $this->customerRepository->getById($id);
        } catch (NoSuchEntityException $exception) {
            throw new \Exception(__('The wrong customer account is specified.'));
        }
        $this->_customerSession->setCustomerDataAsLoggedIn($customer);
        if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
            $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
            $metadata->setPath('/');
            $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
        }
        return true;
    }


    /**
     * @return PhpCookieManager|mixed
     */
    private function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = \Magento\Framework\App\ObjectManager::getInstance()->get(
                PhpCookieManager::class
            );
        }
        return $this->cookieMetadataManager;
    }

    /**
     * @return CookieMetadataFactory|mixed
     */
    private function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
                CookieMetadataFactory::class
            );
        }
        return $this->cookieMetadataFactory;
    }

    /**
     * @param $oldCustomerId
     * @param $newCustomerId
     * @return bool
     */
    public function authenticate($oldCustomerId, $newCustomerId)
    {
        $old_customer = $this->customerData->getCustomerById($oldCustomerId);
        $multiCustomerParentEmail = $old_customer->getCustomAttribute("multicustomer_parent_email")->getValue();
        $customers = $this->customerData->getFilteredCustomerCollection($multiCustomerParentEmail);
        if($customers->getItems()){
            foreach ((array)$customers->getItems() as $customer){
                if($customer->getEntityId() == $newCustomerId){
                    return true;
                }
            }
        }
        return false;
    }
}
