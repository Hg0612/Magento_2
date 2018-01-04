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

namespace Lofmp\Slider\Block\Marketplace\Slider;

use Magento\Customer\Model\Context as CustomerContext;

class NewSlider extends \Magento\Framework\View\Element\Template
{
    /**
     * Group Collection
     */
    protected $_sellerCollection;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Catalog\Helper\Category
     */
    protected $_sellerHelper = null;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource = null;
    
    /**
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager = null;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $request;

    /**
     * @var \Lofmp\Slider\Model\Slider
     */
    protected $slider;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry                      $registry
     * @param \Lof\MarketPlace\Helper\Data                     $sellerHelper
     * @param \Lof\MarketPlace\Model\Seller                    $sellerCollection
     * @param \Magento\Framework\App\Request\Http              $request
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Lof\MarketPlace\Helper\Data $sellerHelper,
        \Lof\MarketPlace\Model\Seller $sellerCollection,
        \Lofmp\Slider\Model\Slider $slider,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\App\Request\Http $request,
        array $data = []
        ) {
        parent::__construct($context, $data); 

        $this->_sellerCollection = $sellerCollection;
        $this->_sellerHelper = $sellerHelper;
        $this->_coreRegistry = $registry;
        $this->request = $resource;
        $this->storeManager =  $context->getStoreManager();
        $this->storeManager =  $request;
        $this->slider =  $slider;

    }

    public function _prepareLayout() {
        if($this->getRequest()->getParam('slider_id')){
            $this->pageConfig->getTitle()->set(__('Update Slider'));
        }else {
            $this->pageConfig->getTitle()->set(__('Create New Slider'));
        }

        return parent::_prepareLayout ();
    }

    public function getSliderByID() {
        $data = null;
        $slider_id = $this->getRequest()->getParam('slider_id');
        $model = $this->slider->load($slider_id,"slider_id");
        if($model->getId()){
            $data = $model->getData();
        }
        return $data;
    }

}