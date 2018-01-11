<?php

namespace Lofmp\CouponCode\Controller\Marketplace\CouponCode;


use Magento\Framework\App\Action\Context;


class Delete extends \Magento\Customer\Controller\AbstractAccount {

    protected $session;

    protected $resultPageFactory;

    const FLAG_IS_URLS_CHECKED = 'check_url_settings';

    protected $_frontendUrl;

    protected $_actionFlag;

    protected $sellerFactory;

    protected $helper;

    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Lof\Marketplace\Model\SellerFactory $sellerFactory,
        \Magento\Framework\Url $frontendUrl,
        \Lof\MarketPlace\Helper\Data $sellerHelper,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct ($context);

        $this->_frontendUrl      = $frontendUrl;
        $this->_actionFlag       =  $context->getActionFlag();
        $this->sellerFactory     = $sellerFactory;
        $this->session           = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->helper            = $sellerHelper;
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
            $id = $this->getRequest()->getParam('couponcode_id');
            $model = $this->_objectManager->create('Lofmp\CouponCode\Model\Coupon');

            if ($id) {
                $model->load($id);
                if (!$model->getId()) {
                    $this->messageManager->addError(__('This couponcode no longer exists.'));
                    $this->_redirectUrl ($this->getFrontendUrl('marketplace/catalog/couponcode'));
                }else{
                    $data = $model->getData();
                    if($data["seller_id"] != $this->helper->getSellerId()){
                        $this->messageManager->addError(__('This couponcode no longer exists.'));
                        $this->_redirectUrl ($this->getFrontendUrl('marketplace/catalog/couponcode'));
                    }
                }
            }
            $model->delete();
            if(!$model->delete()){
                $this->messageManager->addNotice(__('Cannot delete this item') );
            }else{
                $this->messageManager->addSuccess(__('Delete data success') );
            }
            $this->_redirectUrl ($this->getFrontendUrl('marketplace/catalog/couponcode'));
        } elseif($customerSession->isLoggedIn() && $status == 0) {
            $this->_redirectUrl ( $this->getFrontendUrl('lofmarketplace/seller/becomeseller') );
        } else {
            $this->messageManager->addNotice(__( 'You must have a seller account to access' ) );
            $this->_redirectUrl ($this->getFrontendUrl('lofmarketplace/seller/login'));
        }
    }
}
