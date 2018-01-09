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

namespace Lof\MpFaq\Model\ResourceModel\Seller;

use Lof\MarketPlace\Model\ResourceModel\Seller\Collection as SellerCollection;

class Collection extends SellerCollection
{
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()->joinLeft(
            ['enable_seller_table' => $this->getTable('lof_mpfaq_enable_seller')],
            'main_table.seller_id = enable_seller_table.seller_id',
            ['faq_status' => 'COALESCE(enable_seller_table.status, 0)']
        );
    }
}