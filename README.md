# Categoree 2.0.0 for ExpressionEngine

Retrieve category data in a sane manner.

## Purpose

ExpressionEngine is good, but sometimes that channel categories tag can be a bit limiting. There's not a lot of controlling the markup when outputting nested categories from the native tag. And you can't get children without parents.

This plugin offers you a way to get categories on your terms, with your markup, in a way that suites you best.

## Single Tag

	{exp:categoree:single show="1"}

### Use

Get the category_name, category_url_title, category_description, or category_parent_id of a single category by ID.

### Parameters

	show=""

Required. This is the category ID of the category you wish to get data from.

	field=""

Optional: default is category_name. This allows you to choose which category field data to retrieve.

Possible values: category_name, category_url_title, category_description, or category_parent_id.

## Tag Pair

	{exp:categoree:pair show="1|2|3" backspace="2"}{category_name}, {/exp:categoree:pair}

### Use

Loop through categories and display category_id, category_name, category_url_title, category_description, or category_parent_id.

Note when using the show parameter without nesting, this will get the specified category ID REGARDLESS of the category hierarchy!

### Parameters

	show=""

Optional: default is all categories.

Value: Category ID or IDs of the category or categories you wish to get data from.

	fixed="yes"

Use in conjunction with the "show" parameter. This will display catetories in exactly the order you have specified the category IDs in the show parameter. Default sorting is by group and category order.

	group=""

Optional: default is all groups.

Value: Pipe separated list of group IDs to get categories from.

	parent_only="yes"

Optional: default is false

Display only parent categories. Cannot be used with the nesting parameter since nesting would not apply.

	parent_id=""

Optional: default is false.

Display only the children of a specified parent ID. Cannot be used with the parent_only parameter, or the nest parameter.

	nest="3"

Optional: Integer. Default is false.

Makes additional tags available for category nesting and set the level of nesting (see below).

	namespace="my_namespace"

Namespace all tags to prevent conflicts with parent tags. So {category_name} becomes {my_namespace:category_name}

	backspace=""

As with any tag pair, this allows you to remove your separator(s) (such as a comma) from the last result.

### Variables available inside the tag pair (not nested)

	{category_id}
	{category_name}
	{category_url_title}
	{category_description}
	{category_parent_id}

### Additional variables when using nesting

	{level} (integer)
	{level_count} (integer)
	{level_start} (boolean)
	{level_end} (boolean)
	{level_total_results} (integer)

## Examples

Some of these examples may be a bit simple, but should demonstrate how powerful the tags are and how easy it is to control the markup.

### Get the name of a category by ID with the single tag

	{exp:categoree:single show="82"}

### Loop through the names of a category group

	{exp:categoree:pair group="21" parent_only="yes" backspace="2"}{category_name}, {/exp:categoree:pair}

### Simple nesting with namespacing

	<ul>
		{exp:categoree:pair group="21" nest="10" namespace="categoree"}
			{if categoree:level != 1 AND categoree:level_start}
			<li>
			<ul class="level-{categoree:level}">
			{/if}
				<li>{categoree:category_name}</li>
			{if categoree:level != 1 AND categoree:level_end}
			</ul>
			</li>
			{/if}
		{/exp:categoree:pair}
	</ul>

### 2 Level Nesting with top level as a heading

	{exp:categoree:pair group="21" nest="2"}
		{if level == 1}
			<h3>{category_name}</h3>
		{/if}
		{if level == 2}
			{if level_count == 1}<ul>{/if}
				<li><a href="/downloads/{category_url_title}">{category_name}</a></li>
			{if level_count == level_total_results}</ul>{/if}
		{/if}
	{/exp:categoree:pair}

## License

Copyright 2014 Caddis Interactive, LLC

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

	http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.