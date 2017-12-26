<?php
/**
 * Venustheme
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://www.venustheme.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Venustheme
 * @package    Ves_Blog
 * @copyright  Copyright (c) 2016 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */
namespace Ves\Blog\Model;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Author extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Ves\Blog\Model\ResourceModel\Author');
    }

    public function loadByUserId($user_id = 0){
    	$this->getResource()->load($this, $user_id, 'user_id');
        return $this;
    }

    public function loadByUserName($user_name = ""){
    	$this->getResource()->load($this, $user_name, 'user_name');
        return $this;
    }
}