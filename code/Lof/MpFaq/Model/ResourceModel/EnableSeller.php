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

namespace Lof\MpFaq\Model\ResourceModel;

class EnableSeller extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected $_isPkAutoIncrement = false;

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('lof_mpfaq_enable_seller', 'seller_id');
    }

    /**
     * Assign default setting to seller
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $table = $this->getTable('lof_mpfaq_setting');
        $sql = $this->getConnection()->select()
                              ->from($table)
                              ->where('seller_id = ?', $object->getSellerId());
        $result = $this->getConnection()->fetchAll($sql);
        if(count($result) == 0){
            $data = [
                'seller_id' => $object->getSellerId(),
                'enable' => 1,
                'layout' => '1-column',
                'answer_slide_speed' => 'fast',
                'show_author' => 1,
                'show_date' => 1,
                'recent_tab' => 1,
                'popup_form' => 1,
                'enable_recaptcha' => 1
            ];

            $this->getConnection()->insert($table, $data);
        }


        return parent::_afterSave($object);
    }
}
