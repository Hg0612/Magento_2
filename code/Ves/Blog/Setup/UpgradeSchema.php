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
namespace Ves\Blog\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * @codeCoverageIgnore
 */
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
        $tableItems = $installer->getTable('ves_blog_category');

        $installer->getConnection()->addColumn(
            $tableItems,
            'parent_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'comment' => 'Parent Id'
            ]
        );


        $installer->getConnection()->addColumn(
            $tableItems,
            'posts_style',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'comment' => 'Posts Style'
            ]
        );

        $installer->getConnection()->addColumn(
            $tableItems,
            'posts_template',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'comment' => 'Posts Template'
            ]
        );


        $postTable = $installer->getTable('ves_blog_post');

        $installer->getConnection()->addColumn(
            $postTable,
            'is_private',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => true,
                'comment'  => 'Is Private'
            ]
        );

        $installer->getConnection()->addColumn(
            $postTable,
            'like',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'nullable' => true,
                'comment'  => 'Like'
            ]
        );

        $installer->getConnection()->addColumn(
            $postTable,
            'disklike',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'nullable' => true,
                'comment'  => 'Disk Like'
            ]
        );

        $installer->getConnection()->addColumn(
            $tableItems,
            'post_template',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'comment' => 'Post Template'
            ]
        );


        /**
         * Create table 'ves_blog_products_related'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ves_blog_post_products_related')
        )->addColumn(
            'post_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Post ID'
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Entity ID'
        )->addColumn(
            'position',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Position'
        )->addForeignKey(
            $installer->getFkName('ves_blog_post_products_related', 'post_id', 'ves_blog_post', 'post_id'),
            'post_id',
            $installer->getTable('ves_blog_post'),
            'post_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
                $installer->getFkName('ves_blog_post_products_related_entity_id', 'entity_id', 'catalog_product_entity', 'entity_id'),
                'entity_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
        ->setComment('Post To Product Linkage Table');
        $installer->getConnection()->createTable($table);


        /**
         * Create table 'ves_blog_post_author'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ves_blog_post_author')
        )->addColumn(
            'author_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Author Id'
        )->addColumn(
            'email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Email'
        )->addColumn(
            'user_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Email'
        )->addColumn(
            'nick_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Nick Name'
        )->addColumn(
            'description',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M',
            [],
            'Description'
        )->addColumn(
            'avatar',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Avatar'
        )->addColumn(
            'user_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'User Id'
        )->addColumn(
            'page_title',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Page Title'
        )->addColumn(
            'meta_keywords',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M',
            [],
            'Meta Keywords'
        )->addColumn(
            'meta_description',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M',
            [],
            'Meta Description'
        )->addColumn(
            'social_networks',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M',
            [],
            'Social Networks'
        )->setComment('Post Author');
        $installer->getConnection()->createTable($table);


        /**
         * Create table 'ves_blog_post_vote'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ves_blog_post_vote')
        )->addColumn(
            'vote_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Vote Id'
        )->addColumn(
            'like',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Like'
        )->addColumn(
            'disklike',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Disk Like'
        )->addColumn(
            'customer_email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Customer Email'
        )->addColumn(
            'customer_name',
             \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Customer Name'
        )->addColumn(
            'ip',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Customer IP'
        )->addColumn(
            'post_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Post Id'
        )->addIndex(
            $installer->getIdxName('ves_blog_post_vote', ['vote_id','post_id']),
            ['vote_id']
        )->addForeignKey(
            $installer->getFkName('ves_blog_post_vote', 'post_id', 'ves_blog_post', 'post_id'),
            'post_id',
            $installer->getTable('ves_blog_post'),
            'post_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Vote'
        );
        $installer->getConnection()->createTable($table);


        if (version_compare($context->getVersion(), '1.0.5', '<')) {
            $table = $installer->getTable('ves_blog_comment');

            $installer->getConnection()->addColumn(
                $table,
                'parent_id',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'length'   => 10,
                    'nullable' => false,
                    'default'  => 0,
                    'comment'  => 'Parent Comment Id'
                ]
            );
        }
        $installer->endSetup();
    }
}