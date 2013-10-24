<?php
/**
 * Automatic generation of Translation FORMs from given data.
 *
 * PHP 5
 *
 * Deliverd as is!
 *
 * @copyright   Lars Lenecke (func0der) <funcoder@live.com>
 * @link        http://twitter.com/func0d3r
 * @package     Cake.View.Helper
 * @license     Use it, but do not steal it. ;)
 */

App::uses('AppHelper', 'View/helper');

class TranslationHelper extends AppHelper{
/**
 * Helpers used by this class
 *
 * @var array
 */
	public $helpers = array(
		'Form'
	);

/**
 * Input function to created 
 *
 * @var array
 */
	public function input($fieldName, $options = array()){
		// Preserve original fieldname
		$orgFieldname = $fieldName;
		// Fields to create
		$fields = array();
		// Collect languages...
		// ...by parameter
		if(isset($options['languages'])){
			$languages = $options['languages'];
			unset($options['languages']);
		}
		// ...by TranslationComponent (TranslationComponent needed)
		else{
			$languages = Configure::read(TranslationComponent::$languagesStoreKey);
		}

		// If $languages is empty, just output the default input
		if(empty($languages)){
			return $this->Form->input($fieldName, $options);
		}

		// Collect additional information for merging after the loop
		$postOptions = array();
		// Fieldset options
		if(!isset($options['fieldset'])){
			$postOptions['fieldset'] = true;
		}
		else{
			$postOptions['fieldset'] = $options['fieldset'];
			unset($options['fieldset']);
		}
		// Legend options
		if(isset($options['legend'])){
			$postOptions['legend'] = $options['legend'];
			unset($options['legend']);
		}
		else{
			// We do not use a legend if we do not have a fieldset
			if($postOptions['fieldset'] !== false){
				$postOptions['legend'] = true;
			}
			else{
				$postOptions['legend'] = false;
			}
		}

		// If we have at least a "Model.field" field name given
		if(substr_count($fieldName, '.') >= 1){			
			$fieldName = explode('.', $fieldName);
			// Save last index
			$lastIndex = (count($fieldName) - 1);
			// Set baseLabel
			$baseLabel = $fieldName[$lastIndex];
			// Resort fieldName array (create space for 2 new indexes)
			$fieldNameIndex = count($fieldName);
			$languageIndex = $fieldNameIndex;
			//$fieldName[$fieldNameIndex] = $fieldName[$lastIndex];
			//$fieldName[$lastIndex] = 'translations';

			// Run through the languages
			foreach($languages as $lang){
				// Get temp options for the loop
				$loopOptions = $options;
				// Insert language key as last but not least value
				$fieldName[$languageIndex] = $lang;
				// Sort array by indexes
				ksort($fieldName);
				// Generate new fieldname
				$field = implode('.', $fieldName);
				// Label for the field
				if(!isset($loopOptions['label'])){
					// If we use a legend we are using languages as label
					if($postOptions['legend']){
						$label = ucfirst($lang);
					}
				}
				else{
					if(is_array($loopOptions['label']) && isset($loopOptions['label'][$lang])){
						$label = $loopOptions['label'][$lang];
					}
					elseif(!is_array($loopOptions['label'][$lang])){
						$label = $loopOptions['label'];
					}
				}

				// If label was not set, set it to default "Fieldname (Language)"
				if(empty($label)){
					$label = Inflector::humanize(Inflector::underscore($baseLabel));
					$label = $label . ' (%s)';
				}

				// Translation would be like "FIELDNAME (%s)" here
				$loopOptions['label'] = __($label, ucfirst($lang));

				// Get the value if set
				if(isset($options['value'])){
					if(is_array($options['value']) && isset($options['value'][$lang])){
						$loopOptions['value'] = $options['value'][$lang];
					}
				}
				
				$fields[$field] = $loopOptions;
			}
			// Set legend for fieldset if not already done
			if(!$postOptions['legend']){
				$postOptions['legend'] = __(Inflector::humanize(Inflector::underscore($baseLabel)));
			}
		}
		// TODO: Find a way to do this with none dot present which means no model and/or field given
		// Or this is just used with model name
		else{
			throw new Exception(__('DOT syntax is needed with the translation helper'));
		}

		// Append post options
		$fields = array_merge_recursive($fields, $postOptions);

		return $this->Form->inputs($fields);
	}
}

?>
