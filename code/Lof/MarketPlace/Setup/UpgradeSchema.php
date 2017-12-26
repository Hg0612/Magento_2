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
 * @copyright  Copyright (c) 2014 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */
namespace Lof\MarketPlace\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\DB\Ddl\Table;


class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'lof_marketplace_product'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('lof_marketplace_product')
        )->addColumn(
            'seller_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'primary' => true],
            'Seller ID'
        )->addColumn(
            'product_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Product ID'
        )->addColumn(
            'position',
            Table::TYPE_INTEGER,
            11,
            ['nullable' => true],
            'Position'
        )->setComment(
            'Lof Seller To Product Linkage Table'
        );
        $installer->getConnection()->createTable($table);
         /**
         *  setup for Seller Settings
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('lof_marketplace_seller_settings')
        )->addColumn(
            'setting_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Setting Id'
        )->addColumn(
            'seller_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Seller Id'
        )->addColumn(
            'group',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '32',
            ['nullable' => false],
            'Group'
        )->addColumn(
            'key',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64',
            ['nullable' => false],
            'Key'
        )->addColumn(
            'value',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '500',
            ['nullable' => false],
            'value'
        )->addColumn(
            'serialized',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'serialized'
        )->setComment(
            'MarketPlace Seller Settings'
        );
        $installer->getConnection()->createTable($table);
        
        $installer->endSetup();
    }
}
