# Categoree 1.0.0 for ExpressionEngine

A lightweight plugin to retrieve category data in a sane manner.

## Single Tag

	{exp:categoree:single show="1"}

### Use

Get the name, url_title, or description of a single category by ID.

### Parameters

	show=""

This is the category ID of the category you wish to get data from.

	field=""

Options: category_name, category_url_title, category_description

This allows you to choose which category field data to retrieve. Default is category_name if none is provided.

## Tag Pair

	{exp:categoree:pair show="1|2|3" backspace="2"}{category_name}, {/exp:categoree:pair}

### Use

Loop through categories and display name, url_title, or description of the category. This will get the specified category ID REGARDLESS of the category hierarchy!

### Parameters

	show=""

This is the category ID or IDs of the category or categories you wish to get data from.

	backspace=""

As with any tag pair, this allows you to remove your seperator(s) (such as a comma) from the last result.

### Variables available inside the tag pair

	{category_name}
	{category_url_title}
	{category_description}

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