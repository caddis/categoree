<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Categoree Model
 *
 * @package Categoree
 * @author  Caddis
 * @link    http://www.caddis.co
 */

class Categoree_model extends CI_Model {

	public function get_cat_data($cat_ids)
	{
		$query = ee()->db
			->select('cat_name, cat_url_title, cat_description')
			->from('categories')
			->where_in('cat_id', $cat_ids)
			->get();

		$result = array();

		foreach ($query->result() as $result_key => $result_value) {
			foreach ($result_value as $key => $value) {
				$key = str_replace('cat_', 'category_', $key);
				$result[$result_key][$key] = $value;
			}
		}

		return $result;
	}
}