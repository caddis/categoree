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

	public function pair()
	{
		$cat_ids = ee()->TMPL->fetch_param('show');

		if (empty($cat_ids)) {
			return;
		}

		// Get cat data from the model
		$cat_data = ee()->categoree_model->get_cat_data($cat_ids);

		if (! empty($cat_data)) {
			return ee()->TMPL->parse_variables(ee()->TMPL->tagdata, $cat_data);
		}
	}

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

		// Get cat data from the model
		$cat_data = ee()->categoree_model->get_cat_data($cat_id);

		if (! empty($cat_data)) {
			return $cat_data[0][$field];
		}
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