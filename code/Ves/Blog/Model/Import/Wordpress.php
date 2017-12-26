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
namespace Ves\Blog\Model\Import;

class Wordpress extends AbstractImport
{
	protected $_oldCategories = [];
	protected $_catCount = 0;

	public function execute()
	{

		$importPosts      = $this->getData('import_posts');
		$importCategories = $this->getData('import_categories');
		$importComments   = $this->getData('import_comments');
		$overwrite	      = $this->getData('overwrite');

		if($importPosts || $importCategories || $importComments)
		{
			$oldCategories = $this->_oldCategories;
			try{
				$_connect = $this->_connect();
				$_connect->set_charset("utf8");
				$prefix = mysqli_real_escape_string($_connect, $this->getData('prefix'));
				$postCount = $tagCount = $commentCount = 0;

				// Categories
				if($importCategories){
					$sql = 'SELECT * FROM ' . $prefix . 'terms t
					LEFT JOIN ' . $prefix . 'term_taxonomy tx on t.term_id = tx.term_id
					WHERE tx.taxonomy = "category"';
					$result = $this->_mysqliQuery($sql);

					$i = 0;
					while ($cat = mysqli_fetch_assoc($result)) {
						$data[$i]['term_id']     = $cat['term_id'];
						$data[$i]['identifier']  = $cat['slug'];
						$data[$i]['name']        = $cat['name'];
						$data[$i]['parent_id']   = $cat['parent'];
						$data[$i]['stores']      = [$this->_storeManager->getDefaultStoreView()->getStoreId()];
						$i++;
					}

					foreach ($data as $k => $_cat) {
						if($_cat['parent_id']==0)
						{
							$category = '';
							$category = $this->_objectManager->create('\Ves\Blog\Model\Category');
							if($overwrite){
								$category = $category->load($_cat['identifier']);
								$_cat['category_id'] = $category->getId();
								$_cat['image'] = $_cat['layout_type'] = $_cat['orderby'] = $_cat['comments'] = $_cat['item_per_page'] = $_cat['lg_column_item'] = $_cat['md_column_item'] = $_cat['sm_column_item'] = $_cat['xs_column_item'] = $_cat['page_layout'] = $_cat['page_title'] = $_cat['canonical_url'] = $_cat['layout_update_xml'] = $_cat['meta_keywords'] = $_cat['meta_description'] = $_cat['posts_style'] = $_cat['posts_template'] = $_cat['post_template'] = '';
							}
							$_cat['importing'] = true; 
							$category->setData($_cat)->save();
							$cats[] = $this->drawItems($data, $_cat, 0, $category->getId());
							$oldCategories = $this->_oldCategories;
							$oldCategories[$_cat['term_id']] = $category->getId();
							$this->_oldCategories = $oldCategories;
							$this->_catCount = $this->_catCount + 1;
						}
					}
				}

				// Posts
				if($importPosts){
					$sql = 'SELECT * FROM ' . $prefix . 'posts WHERE `post_type` = "post" AND `post_status` != "auto-draft"';
					$result = $this->_mysqliQuery($sql);
					$posts = [];
					$this->_oldCategories = $oldCategories;

					$oldCategories = $this->_oldCategories;
					while ($data = mysqli_fetch_assoc($result)) {
						$sql = 'SELECT tt.term_id as term_id FROM ' . $prefix . 'term_relationships tr
						LEFT JOIN ' . $prefix . 'term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
						WHERE tr.`object_id` = "' . $data['ID'] . '"';
						$result2 = $this->_mysqliQuery($sql);
						$postCategories = [];
						if($importCategories){
							while ($data2 = mysqli_fetch_assoc($result2)) {
								$oldTermId = $data2['term_id'];
								if (isset($oldCategories[$oldTermId])) {
									$postCategories[] = $oldCategories[$oldTermId];
								}
							}
						}
						$status = 0;

						// Image
						$image = '';
						$sql = "SELECT  ( SELECT guid FROM " . $prefix . "posts WHERE id = m.meta_value ) AS url 
						FROM " . $prefix . "posts p, " . $prefix . "postmeta m
						WHERE p.post_type =  'post'
						AND p.post_status =  'publish'
						AND p.id = m.post_id
						AND m.meta_key =  '_thumbnail_id' 
						AND p.id = '" . $data['ID'] . "'" ;
						$result3 = $this->_mysqliQuery($sql);
						$data3 = mysqli_fetch_assoc($result3);
						if(isset($data3['url']) && $data3['url']!=''){
							$data3['url'] = explode("uploads/", $data3['url']);
							if(isset($data3['url'][1])){
								$image = 'wysiwyg/uploads/' . $data3['url'][1];
							}
						}

						// Tags
						$sql = "SELECT name
						FROM " . $prefix . "term_relationships
						JOIN " . $prefix . "term_taxonomy
						ON " . $prefix . "term_relationships.term_taxonomy_id = " . $prefix . "term_taxonomy.term_taxonomy_id
						JOIN " . $prefix . "terms ON " . $prefix . "terms.term_id = " . $prefix . "term_taxonomy.term_id
						WHERE object_id = '" . $data['ID'] . "' AND taxonomy = 'post_tag'";
						$tags = $this->_mysqliQuery($sql);
						$postTags = '';
						while ($data2 = mysqli_fetch_assoc($tags)) {
							$postTags[] = $data2['name'];
						}
						if(!empty($postTags)){
							$postTags = implode($postTags, ",");
						}
						if($data['post_name'] == ''){
							$data['post_name'] = strtolower(str_replace(" ", "", $data['post_title']));

						}

						if($data['post_status'] === 'publish') $status = 1;
						$_post = [
							'old_id'         => $data['ID'],
							'title'          => $data['post_title'],
							'identifier'     => str_replace(" ", "", strtolower($data['post_name'])),
							'content'        => $this->decodeImg(utf8_encode($data['post_content'])),
							'short_content'  => $this->decodeImg(utf8_encode($data['post_excerpt'])),
							'is_active'      => $status,
							'creation_time'  => $data['post_date'],
							'update_time'    => $data['post_modified'],
							'image_type'     => 1,
							'image'          => $image,
							'thumbnail_type' => 1,
							'enable_comment' => 1,
							'thumbnail'      => $image,
							'user_id'        => $this->authSession->getUser()->getUserId(),
							'stores'         => [0],
							'categories'     => $postCategories,
							'tags'           => $postTags
						];

						$post = '';
						$post = $this->_objectManager->create('\Ves\Blog\Model\Post');
						$_post['importing'] = true;
						if($overwrite){
							$post = $post->load($_post['identifier']);
							$_post['post_id'] = $post->getId();
							$_post['page_layout'] = '1column';
							$_post['page_title'] = $_post['meta_keywords'] = $_post['meta_description'] = $_post['canonical_url'] = $_post['hits'] = $_post['like'] = $_post['disklike'] = '';
						}
						$post->setData($_post)->save();
						$_posts[$post->getId()] = $_post;
						$postCount++;
					}

					// Comments
					if($importComments){
						foreach ($_posts as $k => $_post) {
							$sql = 'SELECT * FROM ' . $prefix . 'comments WHERE comment_post_ID = "' . $_post['old_id'] . '"';
							$result = $this->_mysqliQuery($sql);
							while ($data = mysqli_fetch_assoc($result)) {
								$status = 0;
								if($data['comment_approved'] === 'publish') $status = 1;

								$comment = $commentData = '';
								$comment = $this->_objectManager->create('\Ves\Blog\Model\Comment');
								$commentData = [
									'post_id'       => $k,
									'content'       => $data['comment_content'],
									'user_name'     => $data['comment_author'],
									'user_email'    => $data['comment_author_email'],
									'creation_time' => $data['comment_date'],
									'has_read'		=> 1,
									'is_active'     => $data['comment_approved']
								];
								$commentData['importing'] = true;
								$comment->setData($commentData)->save();
								$commentCount++;
							}
						}
					}
				}

				$this->messageManager->addSuccess(__(
					'The import process was completed successfully %1 posts, %2 categories and %3 comments imported.',
					$postCount,
					$this->_catCount,
					$commentCount
					));
			} catch (\Exception $e) {
				$this->messageManager->addError($e->getMessage());
			}
		}
	}

	public function decodeImg($str){
		$count = substr_count($str, "<img");
		$tmpImg = '';
		for ($i=0; $i < $count; $i++) {
			$s = strpos($str, 'src="http', 0);
			$e = strpos($str, '"', $s+5);
			if($e>$s){
				$img = substr($str, $s+5, $e-$s-5);
				$tmpImg = explode('uploads/', $img);
				$newImg = '';
				if( isset($tmpImg[1]) ) {
					$newImg = '{{media url="wysiwyg/uploads/' . $tmpImg[1] . '"}}';
					$str = str_replace($img, $newImg, $str);
				}
			}
		}
		return $str;
	}

	public function drawItems($collection, $cat, $level = 0, $parentId = ''){
		$overwrite	   = $this->getData('overwrite');
		$oldCategories = $this->_oldCategories;
		foreach ($collection as $_cat) {
			if($_cat['parent_id'] == $cat['term_id']){
				$cat1 = $_cat;
				$cat1['level'] = $level;
				$category = '';
				$category = $this->_objectManager->create('\Ves\Blog\Model\Category');
				if($overwrite){
					$category = $category->load($_cat['identifier']);
					$_cat['category_id'] = $category->getId();
					$_cat['image'] = $_cat['layout_type'] = $_cat['orderby'] = $_cat['comments'] = $_cat['item_per_page'] = $_cat['lg_column_item'] = $_cat['md_column_item'] = $_cat['sm_column_item'] = $_cat['xs_column_item'] = $_cat['page_title'] = $_cat['canonical_url'] = $_cat['layout_update_xml'] = $_cat['meta_keywords'] = $_cat['meta_description'] = $_cat['posts_style'] = $_cat['posts_template'] = $_cat['post_template'] = '';
					$_cat['page_layout'] = '1column';
				}
				$_cat['parent_id'] = $parentId;
				$_cat['importing'] = true;
				$category->setData($_cat)->save();
				$this->_catCount = $this->_catCount + 1;
				$oldCategories[$_cat['term_id']] = $category->getId();
				$children[] = $this->drawItems($collection, $cat1, $level+1, $category->getId());
				$cat['children'] = $children;
				$oldCategories = $this->_oldCategories;
				$oldCategories[$_cat['term_id']] = $category->getId();
				$this->_oldCategories = $oldCategories;
			}
		}
		$cat['level'] = $level;

		return $cat;
	}
}