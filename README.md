# Categoree 1.1.0 for ExpressionEngine

A lightweight plugin to retrieve category data in a sane manner.

## Single Tag

	{exp:categoree:single show="1"}

### Use

Get the name, url_title, or description of a single category by ID.

### Parameters

	show=""

Required. This is the category ID of the category you wish to get data from.

	field=""

Optional: default is category_name.

Value: category_name, category_url_title, or category_description.

This allows you to choose which category field data to retrieve.

## Tag Pair

	{exp:categoree:pair show="1|2|3" backspace="2"}{category_name}, {/exp:categoree:pair}

### Use

Loop through categories and display name, url_title, or description of the category. This will get the specified category ID REGARDLESS of the category hierarchy!

### Parameters

	show=""

Optional: default is all categories.

Value: Category ID or IDs of the category or categories you wish to get data from.

	group=""

Optional: default is all categories.

Value: Pipe separated list of group IDs to get categories from.

	parent_only="yes"

Optional: default is false

Display only parent categories.

	parent_id=""

Optional: default is false. The parent_only="yes" parameter will override this one.

Display only the children of a specified parent ID.

	backspace=""

As with any tag pair, this allows you to remove your separator(s) (such as a comma) from the last result.

### Variables available inside the tag pair

	{category_id}
	{category_name}
	{category_url_title}
	{category_description}
	{category_parent_id}

## Nested Tag Pair

	{exp:categoree:nested group="21"}
		<ul>
			{category_name}
			{children}
				{if child:count == 1}</ul>{/if}
					<li>{child:category_name}</li>
				{if child:count == child:total_results}
			{/children}
		</ul>
	{/exp:category:nested}

### Use

Loop through categories and the their direct children to display name, url_title, or description in a nested hierarchy where you are in FULL control of the markup!

### Parameters

	show=""

Optional: default is all categories.

Value: Category ID or IDs of the category or categories you wish to get data from.

	group=""

Optional: default is all categories.

Value: Pipe separated list of group IDs to get categories from.

	backspace=""

As with any tag pair, this allows you to remove your separator(s) (such as a comma) from the last result.

### Variables available inside the tag pair

	{category_id}
	{category_name}
	{category_url_title}
	{category_description}
	{category_parent_id}
	{child:total_results}

### Variable pairs available inside the tag pair

	{children}
		{child:count}
		{child:total_results}
		{child:category_id}
		{child:category_name}
		{child:category_url_title}
		{child:category_description}
		{child:category_parent_id}
		{child:child:total_results}
	{/children}

## Prefixing

All tag pairs can use the prefixing parameter:

	prefix="my_prefix"

This can prevent running into conflicts with parent tags and other unfortunate things. Here's an example:

	{exp:categoree:pair group="21" prefix="categoree"}
		{categoree:category_name}<br>
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