<?php
/**
 * This is language Schema file
 *
 * Use it to configure database for Translation plugin
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @package       Plugin.Translation.Config.Schema
 * @license       @TOOD: ADD proper license here.
 */

/**
 *
 * Using the Schema command line utility
 *
 * Use it to configure database for Translation plugin
 *
 * cake schema run create language
 */
class LanguageSchema extends CakeSchema {

	public $name = 'language';

	public function before($event = array()) {
		return true;
	}

	public function after($event = array()) {
	}

	public $languages = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'locale' => array('type' => 'string', 'null' => false, 'length' => 5),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1))
	);

}
