<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array (
	'pi_name' => 'Categoree',
	'pi_version' => '1.2.0',
	'pi_author' => 'Caddis (TJ Draper)',
	'pi_author_url' => 'http://www.caddis.co',
	'pi_description' => 'Retrieve category data in a sane manner.',
	'pi_usage' => Categoree::usage()
);

class Categoree {

	// Default get_cat_data Options
	public $model_options = array(
		'cat_ids' => false,
		'entry_id' => false,
		'group_ids' => false,
		'parent_only' => false,
		'parent_id' => false,
		'limit' => false
	);

	public function __construct()
	{
		ee()->load->model('categoree_model');

		$this->cat_ids = ee()->TMPL->fetch_param('show');
		$this->entry_id = ee()->TMPL->fetch_param('entry_id');
		$this->field = ee()->TMPL->fetch_param('field', 'category_name');
		$this->group = ee()->TMPL->fetch_param('group');
		$this->parent_only = ee()->TMPL->fetch_param('parent_only');
		$this->parent_id = ee()->TMPL->fetch_param('parent_id');
		$this->namespace = ee()->TMPL->fetch_param('namespace');
		$this->nest = ee()->TMPL->fetch_param('nest');
		$this->fixed = ee()->TMPL->fetch_param('fixed');
	}

	/**
	 * Categoree Single Tag
	 *
	 * @return String
	 */
	public function single()
	{
		// Allowed field parameters
		$allowed_fields = array(
			'category_name',
			'category_url_title',
			'category_description',
			'category_parent_id'
		);

		if (empty($this->cat_ids) or ! in_array($this->field, $allowed_fields)) {
			return;
		}

		// Add option to the model_options array

		$this->model_options['cat_ids'] = $this->cat_ids;

		$this->model_options['limit'] = 1;

		// Get cat data from the model
		$cat_data = ee()->categoree_model->get_cat_data($this->model_options);

		if (! empty($cat_data)) {
			return $cat_data[0][$this->field];
		}
	}

	/**
	 * Categoree Tag Pair
	 *
	 * @return Array
	 */
	public function pair()
	{
		// Build options to send to the model

		if (! empty($this->cat_ids)) {
			$this->model_options['cat_ids'] = $this->cat_ids;
		}

		if (! empty($this->entry_id)) {
			$this->model_options['entry_id'] = $this->entry_id;
		}

		if (! empty($this->group)) {
			$this->model_options['group_ids'] = $this->group;
		}

		if (! empty($this->parent_only)) {
			$this->model_options['parent_only'] = $this->parent_only;
		}

		if (! empty($this->parent_id)) {
			$this->model_options['parent_id'] = $this->parent_id;
		}

		// Get cat data from the model
		$cat_data = ee()->categoree_model->get_cat_data($this->model_options);

		// Nest items if nest parameter is set
		if (! empty($this->nest) and empty($this->parent_only)) {
			$cat_data = $this->_nest_cats($cat_data, $this->nest);
		} else if ($this->fixed === 'yes' and ! empty($this->cat_ids)) {
			$cat_data = $this->_fixed_order(
				$cat_data, explode('|', $this->cat_ids)
			);
		}

		// Namespace tags if parameter is set
		if ($this->namespace != false) {
			$cat_data = $this->_namespace_items($cat_data, $this->namespace);
		}

		if (! empty($cat_data)) {
			return ee()->TMPL->parse_variables(ee()->TMPL->tagdata, $cat_data);
		}
	}

	/**
	 * Categoree namespacing
	 *
	 * @param array $cat_data - The tag data array to set namespace on
	 * @param string $namespace The string to use for namespacing
	 *
	 * @return Array - Namespaced keys on the tag data input array
	 */
	private function _namespace_items($cat_data, $namespace)
	{
		$result = array();

		foreach ($cat_data as $cat_key => $cat_value) {
			foreach ($cat_value as $key => $value) {
				$key = $namespace . ':' . $key;
				$result[$cat_key][$key] = $value;
			}
		}

		return $result;
	}

	/**
	 * Add nest variables
	 *
	 * @return Array
	 */
	private function _nest_cats($cat_data, $nest)
	{
		$nested_cats = array();

		$level_count = 0;

		// Set the parent categories
		foreach ($cat_data as $cat_key => $cat_value) {
			if ($cat_value['category_parent_id'] == 0) {
				$level_count++;

				$cat_value['level'] = 1;

				$cat_value['level_count'] = $level_count;

				$nested_cats[] = $cat_value;

				unset($cat_data[$cat_key]);
			}
		}

		// Add total level results variable
		foreach ($nested_cats as $key => &$value) {
			$value['level_start'] = false;

			$value['level_end'] = false;

			if ($key === 0 and $key === ($level_count - 1)) {
				$value['level_start'] = true;
				$value['level_end'] = true;
			} else if ($key === 0) {
				$value['level_start'] = true;
			} else if ($key === ($level_count - 1)) {
				$value['level_end'] = true;
			}

			$value['level_total_results'] = $level_count;
		}

		// Go to the recursive nesting function to finish build out
		if ($nest > 1) {
			$nested_cats = $this->_recursive_nesting(
				$nested_cats,
				$cat_data,
				1,
				$nest
			);
		}

		return $nested_cats;
	}

	/**
	 * Recursive nesting
	 *
	 * @return Array
	 */
	private function _recursive_nesting($nested_cats, $cat_data, $level, $nest)
	{
		$level_build = array();

		$level++;

		$level_count = 0;

		$previous_level_count = 0;

		// Go through each of the categories already in the array
		foreach ($nested_cats as $nested_key => $nested_value) {
			// Go through each of the categories left in the category array
			foreach ($cat_data as $cat_key => $cat_value) {
				// If it matches a parent, build it into the array
				if ($cat_value['category_parent_id']
						== $nested_value['category_id']) {
					$level_count++;

					$cat_value['level'] = $level;

					$cat_value['level_count'] = $level_count;

					$level_build[] = $cat_value;

					unset($cat_data[$cat_key]);
				}
			}

			// Add total results variable
			foreach ($level_build as $key => &$value) {
				$value['level_start'] = false;

				$value['level_end'] = false;

				if ($key === 0 and $key === ($level_count - 1)) {
					$value['level_start'] = true;
					$value['level_end'] = true;
				} else if ($key === 0) {
					$value['level_start'] = true;
				} else if ($key === ($level_count - 1)) {
					$value['level_end'] = true;
				}

				$value['level_total_results'] = $level_count;
			}

			// Insert the built level into the nested cats array
			$nested_cats = $this->_insert_array_index(
				$nested_cats,
				$level_build,
				($nested_key + 1) + ($previous_level_count)
			);

			// Remember how many were in the previous level so we know where to
			// insert the next level
			$previous_level_count = $level_count + $previous_level_count;

			// Reset variables

			$level_count = 0;

			$level_build = array();
		}

		// If the categories are not empty, and nest param is great than current
		// level, recursions is
		if (! empty($cat_data) and $nest > $level) {
			$nested_cats = $this->_recursive_nesting(
				$nested_cats, $cat_data, $level, $nest
			);
		}

		return $nested_cats;
	}

	/**
	 * Insert array into another array at a specific point
	 *
	 * @return Array
	 */
	private function _insert_array_index($array, $new_element, $index)
	{
		// Get the start of the array
		$start = array_slice($array, 0, $index);

		// Get the end of the array
		$end = array_slice($array, $index);

		// Add the new element to the array
		foreach ($new_element as $key => $value) {
			$start[] = $value;
		}

		// Glue them back together and return
		return array_merge($start, $end);
	}

	/**
	 * Order categories based on the order specified
	 *
	 * @param array $cat_data - The tag data array
	 * @param array $cat_ids - Array of category ids order
	 *
	 * @return Array
	 */
	private function _fixed_order($cat_data, $cat_ids)
	{
		$return_data = array();

		foreach ($cat_ids as $cat_id) {
			foreach ($cat_data as $cat_key => $cat_value) {
				if ($cat_id === $cat_value['category_id']) {
					$return_data[] = $cat_value;

					unset($cat_data[$cat_key]);

					break;
				}
			}
		}

		return $return_data;
	}

	function usage()
	{
		ob_start();
?>
See docs and examples on GitHub:
https://github.com/caddis/categoree
<?php
		$buffer = ob_get_contents();

		ob_end_clean();

		return $buffer;
	}
}