<?php
App::uses('Language', 'Translation.Model');

/**
 * Language Test Case
 *
 */
class LanguageTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.translation.language'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Language = ClassRegistry::init('Translation.Language');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Language);

		parent::tearDown();
	}

}
