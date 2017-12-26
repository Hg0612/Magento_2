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
 * @package    Lof_MarketPlace
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\Slider\Block\Marketplace\Slider;

class Slider extends \Magento\Framework\View\Element\Template {

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    /**
     * @var \Lof\Slider\Model\Slider
     */
    protected $_SliderFactory;
    /**
     * @var \Lof\Slider\Model\Data
     */
    protected $_helper;
     /**
     * @var \Lof\Slider\Model\Slider
     */
    protected $slider;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resource;
    /**
     * @param \Magento\Framework\View\Element\Template\Context
     * @param \Magento\Framework\Registry
     * @param \Lof\Slider\Model\Slider
     * @param \Magento\Framework\App\ResourceConnection
     * @param array
    */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Lof\Slider\Model\Slider $SliderFactory,
        \Lof\MarketPlace\Helper\Data $helper,
        \Lof\Slider\Model\Slider $slider,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\ResourceConnection $resource,
        array $data = []
        ) {
        $this->slider = $slider;
        $this->_helper        = $helper;
        $this->_coreRegistry  = $registry;
        $this->_SliderFactory = $SliderFactory;
        $this->_resource      = $resource;
        $this->session           = $customerSession;
        parent::__construct($context);
    }
    /**
     *  get Slider Colection
     *
     * @return Object
     */
     public function getSliderCollection(){
        $store            = $this->_storeManager->getStore();
        $SliderCollection = $this->_SliderFactory->getCollection();
        return $SliderCollection;
    }

    public function getSlider() {
        if($this->getCurrentSlider()) {
            $slider_id = $this->getCurrentSlider()->getData('slider_id');
        } else {
            $slider_id = $this->getSliderId();
        }
        $slider = $this->slider->getCollection()->addFieldToFilter('is_active','1')->addFieldToFilter('seller_id',$this->_helper->getSellerId());
        return $slider;
    }

    public function getCurrentSlider()
    {
        $Slider = $this->_coreRegistry->registry('current_Slider');
        if ($Slider) {
            $this->setData('current_Slider', $Slider);
        }
        return $Slider;
    }

    /**
     * Prepare layout for change buyer
     *
     * @return Object
     */
    public function _prepareLayout() {
        return parent::_prepareLayout ();
    }

}