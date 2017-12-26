<?php
namespace Lof\CouponCode\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Framework\App\Action\Action {

     /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    protected $_couponHelper;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Lof\CouponCode\Helper\Data $helper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Lof\CouponCode\Helper\Data $helper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_couponHelper = $helper;
        parent::__construct($context);
    }

	public function execute() {
        if(!$this->_couponHelper->getConfig('general_settings/show') || !$this->_couponHelper->getConfig('general_settings/show_on_customer')) {
            $resultForward = $this->resultPageFactory->create();
            return $resultForward->forward('defaultnoroute');
        }
		/** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('My Coupons'));

        $block = $resultPage->getLayout()->getBlock('customer.account.link.back');
        if ($block) {
            $block->setRefererUrl($this->_redirect->getRefererUrl());
        }
        return $resultPage;
	}
}