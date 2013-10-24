<?php
/**
 * Translation Component
 *
 * PHP 5
 *
 * @copyright   Lars Lenecke (func0der) <funcoder@live.com>
 * @link        http://twitter.com/func0d3r
 * @package     Cake.Controller.Component
 * @license     Use it, but do not steal it. ;)
 */

class TranslationComponent extends Component{

/**
 * Default configuration
 *
 * @var array
 */
	protected $_defaults = array(
		'languages' => 'auto',
		'languagesConfigKey' => 'App.Languages',
		'languagesStoreKey' => 'Plugins.Translation.Languages',
	);

/**
 * Config store key holder.
 * It helps to access the store key from TranslationHelper.
 *
 * @var string
 */
	public static $languagesStoreKey;
	
/**
 * Constructor of the component.
 * This here is used to get the languages.
 *
 * @see Component::__construct()
 * @return void
 */
	public function __construct($collection, $settings = array()){
		parent::__construct($collection, $settings);

		// Merge settings
		$this->settings += $this->_defaults;

		// Set language config store key
		self::$languagesStoreKey = $this->settings['languagesStoreKey'];

		// Collect needed languages
		$this->collectLanguages();
	}

/**
 * Collect the languages from whatever source
 *
 * @return void
 */
	public function collectLanguages(){
		// If we have no languages provided, get them from app "Locales" folders
		if(
			!isset($this->settings['languages']) || 
			$this->settings['languages'] == 'auto'
		){
			App::uses('Folder', 'Utility');
			$localesPath = App::path('locales');
			$locales = new Folder($localesPath[0]);
			// Read content of dir
			$languageFolders = $locales->read();
			$temp = array();
			// We only need the folders
			foreach($languageFolders[0] as $folder){
				if(preg_match('/^[a-z-_]+$/i', $folder, $match) === 1){
					$temp[] = $match[0];
				}
			}
			$languages = $temp;
		}
		// Read languages from config file
		elseif($this->settings['languages'] == 'config'){
			$languages = Configure::read($this->settings['languagesConfigKey']);
		}
		// Use provided languages
		else{
			$languages = $this->settings['languages'];
		}

		// Set the choosen language
		$this->setLanguages($languages);
	}

/**
 * Wrapper function for setting the given languages.
 *
 * @param array $languages
 * @return array
 */
	public static function setLanguages($languages){
		$locales = array();

		if(!empty($languages)){
			// Get locales for languages, because cake sucks dicks
			App::uses('I18n', 'I18n');
			$l10n = I18n::getInstance()->l10n;
			foreach($languages as $lang){
				$temp = $l10n->catalog($lang);
				$locales[] = $temp['locale'];
			}
		}

		return Configure::write(self::$languagesStoreKey, $locales);
	}

/**
 * Wrapper function for getting the current collected languages.
 *
 * @return array
 */
	public static function getLanguages(){
		return Configure::read(self::$languagesStoreKey);
	}
}

?>
