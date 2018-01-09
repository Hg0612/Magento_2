<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://venustheme.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_Slagento
 * @copyright  Copyright (c) 2017 Landofcoder (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */

namespace Lof\MpFaq\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    protected $_storeManager;
    protected $_objectManager;

    const PATH_MPFAQ = 'lofmpfaq/';

    public function __construct(Context $context,
                                ObjectManagerInterface $objectManager,
                                StoreManagerInterface $storeManager)
    {
        $this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    public function getConfig($filed, $storeId = null)
    {
        return $this->scopeConfig->getValue(self::PATH_MPFAQ . $filed, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getSellerSettings($sellerId)
    {
        return $this->_objectManager->create('Lof\MpFaq\Model\Setting')->load($sellerId);
    }
}

?>