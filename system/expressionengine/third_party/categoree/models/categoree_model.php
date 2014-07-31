<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Categoree Model
 *
 * @package Categoree
 * @author  Caddis
 * @link    http://www.caddis.co
 */

class Categoree_model extends CI_Model {

	/**
	 * Category Data Retrieval
	 *
	 * @param array $model_options - Array of options to pass into the function
	 *   cat_ids array key (string) - Pipe separated cat IDs to get
	 *   group_ids (string) - Pipe separated category group IDs to pull from
	 *   parent_only (string) - Set to yes to get parent categories only
	 *   parent_id (string) - Specify a parent ID categories should be in
	 *
	 * @return Array
	 */
	public function get_cat_data($model_options)
	{
		ee()->db
			->select('
				cat_id,
				cat_name,
				cat_url_title,
				cat_description,
				parent_id
				')
			->from('categories');

		if (! empty($model_options['cat_ids'])) {
			$cat_ids = explode('|', $model_options['cat_ids']);
			ee()->db->where_in('cat_id', $cat_ids);
		}

		if (! empty($model_options['group_ids'])) {
			$group_ids = explode('|', $model_options['group_ids']);
			ee()->db->where_in('group_id', $group_ids);
		}

		if ($model_options['parent_only'] === 'yes') {
			ee()->db->where('parent_id', 0);
		}

		if (! empty($model_options['parent_id'])) {
			ee()->db->where('parent_id', $model_options['parent_id']);
		}

		if (! empty($model_options['limit'])) {
			ee()->db->limit($model_options['limit']);
		}

		$query = ee()->db
			->order_by('group_id, parent_id, cat_order')
			->get();

		return $this->_build_result_array($query->result());
	}

	/**
	 * Categoree result array building
	 *
	 * @param array $query_data Array of objects returned from the EE query.
	 *
	 * @return Array
	 */
	private function _build_result_array($query_data)
	{
		$result = array();

		foreach ($query_data as $result_key => $result_value) {
			foreach ($result_value as $key => $value) {
				$key = str_replace('cat_', '', $key);
				$key = 'category_' . $key;
				$result[$result_key][$key] = $value;
			}
		}

		return $result;
	}
}