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

namespace Lof\MpFaq\Model;

use Magento\Cms\Api\Data\BlockInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Setting extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Lof\MpFaq\Model\ResourceModel\Setting');
    }

    public function isEnableFaq(){
        return $this->getEnable();
    }

    public function isShowAuthor(){
        return $this->getShowAuthor();
    }

    public function isShowDate(){
        return $this->getShowDate();
    }

    public function isEnableRecentTab(){
        return $this->getRecentTab();
    }

    public function isEnablePopupForm(){
        return $this->getPopupForm();
    }

    public function isEnableRecaptcha(){
        return $this->getEnableRecaptcha();
    }
}
