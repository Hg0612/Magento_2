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

class Question extends \Magento\Framework\Model\AbstractModel
{
    protected $_resource;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Lof\MpFaq\Model\ResourceModel\Question $resource = null,
        \Lof\MpFaq\Model\ResourceModel\Question\Collection $resourceCollection,
        array $data = []
    ) {
        $this->_resource = $resource;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Lof\MpFaq\Model\ResourceModel\Question');
    }

    public function getRelateProductIds(){
        $connection = $this->_resource->getConnection();
        $select = 'SELECT * FROM ' . $this->_resource->getTable('lof_mpfaq_question_product') . ' WHERE question_id = ' . $this->getQuestionId();
        $result = $connection->fetchAll($select);
        $productIds = [];
        foreach ($result as $record) {
            $productIds[] = $record['product_id'];
        }
        return $productIds;
    }
}
