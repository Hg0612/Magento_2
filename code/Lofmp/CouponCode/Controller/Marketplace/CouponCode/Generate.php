<?php
/**
 * Landofcoder
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * http://www.landofcoder.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Landofcoder
 * @package    Lof_MarketPlace
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lofmp\CouponCode\Controller\Marketplace\CouponCode;


use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;


class Generate extends \Magento\Customer\Controller\AbstractAccount {

    protected $session;

    protected $resultPageFactory;

    protected $sellerFactory;

    const FLAG_IS_URLS_CHECKED = 'check_url_settings';

    protected $_frontendUrl;

    protected $_actionFlag;

    protected $_couponHelper;

    protected $couponGenerator;

    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Lof\MarketPlace\Model\SellerFactory $sellerFactory,
        \Magento\Framework\Url $frontendUrl,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        StoreManagerInterface $storeManager,
        \Lofmp\CouponCode\Helper\Data $helper,
        \Lofmp\CouponCode\Helper\Generator $generateHelper
    ) {
        parent::__construct ($context);

        $this->_frontendUrl = $frontendUrl;
        $this->_actionFlag = $context->getActionFlag();
        $this->sellerFactory     = $sellerFactory;
        $this->session           = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->_couponHelper       = $helper;
        $this->couponGenerator     = $generateHelper;
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
        $coupon_code = '';
        $seller_id = 2;
        $isAjax = $this->getRequest()->getParam('isAjax');
        $data = $this->getRequest()->getParams();
        if(!$data) {
            $customer_email = $this->getRequest()->getParam('email');
            $rule_id = (int)$this->getRequest()->getParam('rule_id');

        } else {
            $customer_email = isset($data['email'])?$data['email']:'';
            $rule_id = isset($data['rule_id'])?(int)$data['rule_id']:'';
        }

        if($rule_id && $customer_email) {

            $couponRuleData = $this->_couponHelper->getCouponRuleData($rule_id);
            $ruleId = (int)$couponRuleData->getRuleId();
            if($ruleId) {
                $limit_time_generated_coupon = (int)$couponRuleData->getLimitGenerated();
                $coupon_collection = $this->_objectManager->create('Lofmp\CouponCode\Model\Coupon')->getCollection();
                $number_generated_coupon = (int)$coupon_collection->getTotalByEmail($customer_email, $rule_id);
                if($limit_time_generated_coupon <= 0 || ($number_generated_coupon < $limit_time_generated_coupon)) {//check number coupons was generated for same email address
                    $this->couponGenerator->setCustomerEmail($customer_email);
                    $this->couponGenerator->setSellerId($seller_id);
                    $coupon_alias = "redeem-".md5($customer_email);
                    $this->couponGenerator->setCouponAlias($coupon_alias);

                    $coupon_exists = false;
                    $coupon_model = $this->_objectManager->create('Lofmp\CouponCode\Model\Coupon')->getCouponByAlias($coupon_alias);
                    if($coupon_model->getId()){
                        $coupon_exists = true;
                    }
                    if(!$coupon_exists){
                        $coupon_code = $this->couponGenerator->generateCoupon($rule_id);
                        $this->messageManager->addSuccess(__('The coupon code has been generated.'));
                    }
                }
            }
        }else{
            $this->messageManager->addError(__('Something went wrong while saving the coupon.'));
        }
        $this->_redirect ('catalog/couponcode' );
    }
}
