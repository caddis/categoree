<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array (
	'pi_name' => 'Categoree',
	'pi_version' => '2.0.0',
	'pi_author' => 'Caddis (TJ Draper)',
	'pi_author_url' => 'http://www.caddis.co',
	'pi_description' => 'A lightweight plugin to retrieve category data in a sane manner.',
	'pi_usage' => Categoree::usage()
);

class Categoree {

	// Default get_cat_data Options
	public $model_options = array(
		'cat_ids' => false,
		'group_ids' => false,
		'parent_only' => false,
		'parent_id' => false,
		'limit' => false
	);

	public function __construct()
	{
		ee()->load->model('categoree_model');

		$this->cat_ids = ee()->TMPL->fetch_param('show');
		$this->field = ee()->TMPL->fetch_param('field', 'category_name');
		$this->group = ee()->TMPL->fetch_param('group');
		$this->parent_only = ee()->TMPL->fetch_param('parent_only');
		$this->parent_id = ee()->TMPL->fetch_param('parent_id');
		$this->namespace = ee()->TMPL->fetch_param('namespace');
		$this->nest = ee()->TMPL->fetch_param('nest');
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
			'parent_id'
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
		if (! empty($this->nest) && empty($this->parent_only)) {
			$cat_data = $this->nest_cats($cat_data, $this->nest);
		}

		// Namespace tags if parameter is set
		if ($this->namespace != false) {
			$cat_data = $this->namespace_items($cat_data, $this->namespace);
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
	private function namespace_items($cat_data, $namespace)
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
	private function nest_cats($cat_data, $nest)
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

			if ($key === 0 && $key === ($level_count - 1)) {
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
			$nested_cats = $this->recursive_nesting(
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
	private function recursive_nesting($nested_cats, $cat_data, $level, $nest)
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
				if ($cat_value['category_parent_id'] == $nested_value['category_id']) {
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

				if ($key === 0 && $key === ($level_count - 1)) {
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
			$nested_cats = $this->insert_array_index(
				$nested_cats,
				$level_build,
				($nested_key + 1) + ($previous_level_count)
			);

			// Remember how many were in the previous level so we know where to insert the next level
			$previous_level_count = $level_count + $previous_level_count;

			// Reset variables

			$level_count = 0;

			$level_build = array();
		}

		// If the categories are not empty, and nest param is great than current
		// level, recursions is
		if (! empty($cat_data) && $nest > $level) {
			$nested_cats = $this->recursive_nesting($nested_cats, $cat_data, $level, $nest);
		}

		return $nested_cats;
	}

	/**
	 * Insert array into another array at a specific point
	 *
	 * @return Array
	 */
	private function insert_array_index($array, $new_element, $index)
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

	function usage()
	{
		ob_start();
?>
SINGLE TAG:
{exp:categoree:single show="1"}

Use:
Get the name, url_title, or description of a single category by ID.

Parameters:

show=""
This is the category ID of the category you wish to get data from.

field=""
Options: category_name, category_url_title, category_description
This allows you to choose which category field data to retrieve. Default is category_name if none is provided.

TAG PAIR:
{exp:categoree:pair show="1|2|3" backspace="2"}{category_name}, {/exp:categoree:pair}

Use:
Loop through categories and display name, url_title, or description of the category. This will get the specified category ID REGARDLESS of the category hierarchy!

Parameters:
show=""
This is the category ID or IDs of the category or categories you wish to get data from.

backspace=""
As with any tag pair, this allows you to remove your seperator(s) (such as a comma) from the last result.

Variables available inside the tag pair:
{category_name}
{category_url_title}
{category_description}
<?php
		$buffer = ob_get_contents();

		ob_end_clean();

		return $buffer;
	}
}