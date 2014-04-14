# SilverStripe restful server module (V2 API documentation)

## Requirements

 * SilverStripe 3.0 or newer

## Installation

Add this entry to your composer.json's repositories section:

	{
		"type": "vcs",
		"url": "https://github.com/govtnz/silverstripe-restfulserver"
	}

Run:

	$ composer require silverstripe/restfulserver:dev-develop

## Configuration

To enable API access to a DataObject:

	class MyDataObject extends DataObject {

		private static $db = array(
			'Title' => 'Text',
			'Description' => 'Text',
			'AnotherField' => 'Boolean'
		);

		private static $has_many = array(
			'OtherObjects' => 'MyDataObject'
		);

		private static $api_access = array(
			'end_point_alias' => 'my-data-objects',
			'singular_name' => 'myDataObject',
			'plural_name' => 'myDataObjects',
			'field_aliases' => array(
				'id' => 'ID',
				'title' => 'Title',
				'description' => 'Description'
			),
			'relation_aliases' => array(
				'other-objects' => 'OtherObjects'
			)
		);

	}

This configuration would allow access to a list of MyDataObjects via:

	/api/v2/my-data-objects

Detail about a specific MyDataObject:

	/api/v2/my-data-objects/1

A list of MyDataObjects related to by the OtherObjects has many relation:

	/api/v2/my-data-objects/1/other-objects

### Defining dynamic relationships

If you need to make a relationship accessible via the API that isn't a typical has_many or many_many, the following
example is how to achieve this:

	class MyDataObject extends DataObject {

		private static $db = array(
			'Title' => 'Text',
			'Description' => 'Text',
			'AnotherField' => 'Boolean'
		);

		private static $api_access = array(
			'end_point_alias' => 'my-data-objects',
			'relation_aliases' => array(
				'another-relation' => 'DynamicRelation'
			)
		);

		public function DynamicRelation() {
			return MyDataObject::get()->filter('AnotherField', true);
		}

	}

The method that is referenced in relation_aliases (i.e. DynamicRelation) must return a DataList.
If it does not, any requests to that end point will fail.

### Using the V2 API via /api/v1

Add the following code to your mysite/_config.php file:

	RestfulServer\ControllerV2::use_as_version_one_api();
