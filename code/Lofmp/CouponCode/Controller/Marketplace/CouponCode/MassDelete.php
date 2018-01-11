<?php

namespace Lofmp\CouponCode\Controller\Marketplace\CouponCode;


use Magento\Framework\App\Action\Context;


class MassDelete extends \Magento\Customer\Controller\AbstractAccount {

    protected $session;

    protected $resultPageFactory;

    const FLAG_IS_URLS_CHECKED = 'check_url_settings';

    protected $_frontendUrl;

    protected $_actionFlag;

    protected $sellerFactory;

    protected $helper;

    protected $filter;

    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Lof\Marketplace\Model\SellerFactory $sellerFactory,
        \Magento\Framework\Url $frontendUrl,
        \Lof\MarketPlace\Helper\Data $sellerHelper,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Lofmp\CouponCode\Model\CouponFactory $collectionFactory
    ) {
        parent::__construct ($context);

        $this->_frontendUrl      = $frontendUrl;
        $this->_actionFlag       =  $context->getActionFlag();
        $this->sellerFactory     = $sellerFactory;
        $this->session           = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->helper            = $sellerHelper;
        $this->filter            = $filter;
        $this->collectionFactory  = $collectionFactory;
    }

      public function getFrontendUrl($route = '', $params = []){
        return $this->_frontendUrl->getUrl($route,$params);
    }

    protected function _redirectUrl($url){
        $this->getResponse()->setRedirect($url);
        $this->session->setIsUrlNotice($this->_actionFlag->get('', self::FLAG_IS_URLS_CHECKED));
        return $this->getResponse();
    }

    public function execute() {
        $customerSession = $this->session;
        $customerId = $customerSession->getId();
        $status = $this->sellerFactory->create()->load($customerId,'customer_id')->getStatus();
        if ($customerSession->isLoggedIn() && $status == 1) {
            $data = $this->getRequest()->getParams();
            if (isset($data['selected'])) {
                $collection = $this->collectionFactory->create()->getCollection()->addFieldToFilter('couponcode_id', ['in' => $data['selected']]);
            }
            foreach ($collection as $rule) {
                $coupon_id = $rule->getCouponId();
                $rule->delete();
                if($coupon_id) {
                    $model_sale_coupon = $this->_objectManager->create('Magento\SalesRule\Model\Coupon');
                    $model_sale_coupon->load($coupon_id);
                    $model_sale_coupon->delete();
                }
            }
            $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $collection->count()));
            $this->_redirectUrl ($this->getFrontendUrl('marketplace/catalog/couponcode'));
        } elseif($customerSession->isLoggedIn() && $status == 0) {
            $this->_redirectUrl ( $this->getFrontendUrl('lofmarketplace/seller/becomeseller') );
        } else {
            $this->messageManager->addNotice(__( 'You must have a seller account to access' ) );
            $this->_redirectUrl ($this->getFrontendUrl('lofmarketplace/seller/login'));
        }
    }
}
