<?php

namespace RestfulServer;

use SapphireTest;

class BaseRestfulServerTest extends SapphireTest {

	private static $current_test_class = null;

	public function setUp() {
		parent::setUp();

		self::$current_test_class = get_class($this);
	}

	public function tearDown() {
		parent::tearDown();

		self::$current_test_class = null;
	}

	public static function get_current_test_class() {
		return self::$current_test_class;
	}

	public function getExtraDataObjects() {
		return $this->extraDataObjects;
	}

}
