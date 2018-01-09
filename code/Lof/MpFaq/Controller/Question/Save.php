<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * http://landofcoder.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_MpFaq
 * @copyright  Copyright (c) 2017 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */
namespace Lof\MpFaq\Controller\Question;

use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManager;
use Magento\Framework\Controller\Result\JsonFactory;

class Save extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJsonFactory;


    /**
     * @param \Magento\Framework\App\Action\Context
     * @param \Magento\Store\Model\StoreManager
     * @param \Magento\Framework\Controller\Result\JsonFactory
     */
    public function __construct(
        Context $context,
        StoreManager $storeManager,
        JsonFactory $resultJsonFactory
    ) {
        $this->_resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $response = $this->_resultJsonFactory->create();
        $questionModel = $this->_objectManager->create('Lof\MpFaq\Model\QuestionFactory')->create();
        $sellerProductModel = $this->_objectManager->create('Lof\MarketPlace\Model\SellerProduct');
        if ($this->getRequest()->isAjax())
        {
            $request = $this->getRequest()->getPostValue();
            $sellerId = $sellerProductModel->load($request['product_id'])->getSellerId();
            $request['seller_id'] = $sellerId;
            $questionModel->setData($request);
            try {
                $questionModel->save();
                return $response->setData(['message' => 'OK']);
            } catch (\Exception $e) {
                return $response->setData(['message' => 'Err']);
            }
        }
    }
}