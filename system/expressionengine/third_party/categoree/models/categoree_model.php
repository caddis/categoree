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
	 * Categoree nested cat data
	 *
	 * @param array $options An array of options which is passed through to the
	 *	get_cat_data function, so it take the same array keys and values as that
	 *  function
	 *
	 * @return Nested cat data array of parent categories, and one level of children
	 */
	public function get_nested_cat_data($options)
	{
		$return_data = array();

		// Get cats
		$cat_data = $this->get_cat_data($options);

		// Build the parents
		$cat_count = 0;
		foreach ($cat_data as $cat_key => $cat_value) {
			// Make sure category is a parent
			if ($cat_value['parent_id'] == '0') {
				$return_data[$cat_count] = $cat_value;

				// Set the children array so that if there are now children
				// No unparsed tags will be output in the template
				$return_data[$cat_count]['children'] = array();

				// Remove the category from the array since we're done with it
				unset($cat_data[$cat_key]);

				$cat_count++;
			}
		}

		// Build Level 2 Categories

		// Make sure there are more categories to build
		if (! empty($cat_data)) {
			// Loop through each parent in the return data
			foreach ($return_data as $return_key => $return_value) {

				// Loop through each category left in the array
				$child_count = 1;
				foreach ($cat_data as $cat_key => $cat_value) {
					// If the category patches the parent, add it to the array
					if ($cat_value['parent_id'] == $return_value['category_id']) {
						// Prefix the children with "child:"
						foreach ($cat_value as $key => $value) {
							$new_key = 'child:' . $key;
							$cat_value[$new_key] = $value;
							unset($cat_value[$key]);
						}

						// Add the count to the child array to make it available
						// as a template tag
						$cat_value['child:count'] = $child_count;

						// Set the total results tag for the child
						// The final iteration of this loop will be the final override
						$return_data[$return_key]['child:total_results'] = $child_count;
						$child_count++;

						// Add the child data array to the parent array
						$return_data[$return_key]['children'][] = $cat_value;
						unset($cat_data[$cat_key]);
					}
				}
			}
		}

		return $return_data;
	}

	/**
	 * Categoree cat data
	 *
	 * @param array $options An array of options to pass into the function
	 *   cat_ids array key (string) - Pipe separated cat IDs to get
	 *   group_ids (string) - Pipe separated category group IDs to pull from
	 *   parent_only (string) - Set to yes to get parent categories only
	 *   parent_id (string) - Specify a parent ID that retrieved categories must belong to
	 *
	 * @return Cat data array of categories
	 */
	public function get_cat_data($options)
	{
		ee()->db
			->select('cat_id, cat_name, cat_url_title, cat_description, parent_id')
			->from('categories');

		if (array_key_exists('cat_ids', $options)) {
			$cat_ids = explode('|', $options['cat_ids']);
			ee()->db->where_in('cat_id', $cat_ids);
		}

		if (array_key_exists('group_ids', $options)) {
			$group_ids = explode('|', $options['group_ids']);
			ee()->db->where_in('group_id', $group_ids);
		}

		if (array_key_exists('parent_only', $options)) {
			if ($options['parent_only'] == 'yes') {
				ee()->db->where('parent_id', 0);
			}
		}

		if (array_key_exists('parent_id', $options)) {
			ee()->db->where('parent_id', $options['parent_id']);
		}

		$query = ee()->db
			->order_by('cat_order', 'asc')
			->get();

		return $this->build_result_array($query->result());
	}

	/**
	 * Categoree result array building
	 *
	 * @param array $query_data Array of objects returned from the EE query.
	 *
	 * @return Cat data array of categories
	 */
	private function build_result_array($query_data)
	{
		$result = array();

		foreach ($query_data as $result_key => $result_value) {
			foreach ($result_value as $key => $value) {
				$key = str_replace('cat_', 'category_', $key);
				$result[$result_key][$key] = $value;
			}
		}

		return $result;
	}
}