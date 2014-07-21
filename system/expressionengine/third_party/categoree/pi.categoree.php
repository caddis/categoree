<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array (
	'pi_name' => 'Categoree',
	'pi_version' => '1.0.0',
	'pi_author' => 'Caddis',
	'pi_author_url' => 'http://www.caddis.co',
	'pi_description' => 'A lightweight plugin to retrieve category data in a sane manner.',
	'pi_usage' => Categoree::usage()
);

class Categoree {

	public function __construct()
	{
		ee()->load->model('categoree_model');
	}

	/**
	 * Categoree Single Tag
	 *
	 * @return Tagdata for the single tag
	 */
	public function single()
	{
		$cat_id = ee()->TMPL->fetch_param('show');
		$field = ee()->TMPL->fetch_param('field', 'category_name');

		// Allowed field parameters
		$allowed_fields = array(
			'category_name',
			'category_url_title',
			'category_description'
		);

		if (empty($cat_id) or ! in_array($field, $allowed_fields)) {
			return;
		}

		// Build options to send to the model
		$model_options = array(
			'cat_ids' => $cat_id
		);

		// Get cat data from the model
		$cat_data = ee()->categoree_model->get_cat_data($model_options);

		if (! empty($cat_data)) {
			return $cat_data[0][$field];
		}
	}

	/**
	 * Categoree Tag Pair
	 *
	 * @return Tagdata for the tag pair
	 */
	public function pair()
	{
		$cat_ids = ee()->TMPL->fetch_param('show');
		$group = ee()->TMPL->fetch_param('group');
		$parent_only = ee()->TMPL->fetch_param('parent_only');
		$parent_id = ee()->TMPL->fetch_param('parent_id');
		$prefix = ee()->TMPL->fetch_param('prefix');

		// Build options to send to the model
		$model_options = array();

		if (! empty($cat_ids)) {
			$model_options['cat_ids'] = $cat_ids;
		}

		if (! empty($group)) {
			$model_options['group_ids'] = $group;
		}

		if (! empty($parent_only)) {
			$model_options['parent_only'] = $parent_only;
		} else if (! empty($parent_id)) {
			$model_options['parent_id'] = $parent_id;
		}

		// Get cat data from the model
		$cat_data = ee()->categoree_model->get_cat_data($model_options);

		if ($prefix != false) {
			$cat_data = $this->prefix($cat_data, $prefix);
		}

		if (! empty($cat_data)) {
			return ee()->TMPL->parse_variables(ee()->TMPL->tagdata, $cat_data);
		}
	}

	/**
	 * Categoree Nested Tag Pair
	 *
	 * @return Tagdata for the nested tag pair
	 */
	public function nested()
	{
		$cat_ids = ee()->TMPL->fetch_param('show');
		$group = ee()->TMPL->fetch_param('group');
		$prefix = ee()->TMPL->fetch_param('prefix');

		// Build options to send to the model
		$model_options = array();

		if (! empty($cat_ids)) {
			$model_options['cat_ids'] = $cat_ids;
		}

		if (! empty($group)) {
			$model_options['group_ids'] = $group;
		}

		// Get cat data from the model
		$cat_data = ee()->categoree_model->get_nested_cat_data($model_options);

		if ($prefix != false) {
			$cat_data = $this->prefix($cat_data, $prefix);
		}

		if (! empty($cat_data)) {
			return ee()->TMPL->parse_variables(ee()->TMPL->tagdata, $cat_data);
		}
	}

	/**
	 * Categoree prefixing
	 *
	 * @param array $cat_data The tag data array to set prefixes on
	 * @param string $prefix The string to prefix the tag data array keys with
	 *
	 * @return Prefixed keys on the tag data input array
	 */
	private function prefix($cat_data, $prefix)
	{
		$result = array();

		foreach ($cat_data as $cat_key => $cat_value) {
			foreach ($cat_value as $key => $value) {
				$key = $prefix . ':' . $key;
				$result[$cat_key][$key] = $value;
			}

			// Nest children if they exist
			if (array_key_exists('children', $cat_value)) {
				foreach ($cat_value['children'] as $child_key => $child_value) {
					foreach ($child_value as $key => $value) {
						$new_key = $prefix . ':' . $key;
						$result[$cat_key][$prefix . ':children'][$child_key][$new_key] = $value;
						unset($result[$cat_key][$prefix . ':children'][$child_key][$key]);
					}
				}
			}
		}

		return $result;
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