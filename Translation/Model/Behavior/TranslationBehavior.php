<?php
/**
 * Translation behavior
 *
 * PHP 5
 *
 * Deliverd as is!
 *
 * @copyright   Lars Lenecke (func0der) <funcoder@live.com>
 * @link        http://twitter.com/func0d3r
 * @package     Cake.Model.Behavior
 * @license     Use it, but do not steal it. ;)
 */

class TranslationBehavior extends ModelBehavior {

/**
 * Used for runtime configuration of model
 *
 * @var array
 */
	public $runtime = array();

/**
 * Settings
 *
 * TASKS:
 *	- Setting priority for CakeEventManager down to keep this Behavior first
 *	  in line.
 * 
 * @var array
 */
	public $_defaultConfig = array(
		'priority' => 10
	);

/**
 * Save language and language ids
 *
 * @var array
 */
	private $__languages = null;

/**
 * Callback
 *
 * $config for TranslateBehavior should be
 * array('fields' => array('field_one',
 * 'field_two' => 'FieldAssoc', 'field_three'))
 *
 * With above example only one permanent hasMany will be joined (for field_two
 * as FieldAssoc)
 *
 * $config could be empty - and translations configured dynamically by
 * bindTranslation() method
 *
 * @param Model $model Model the behavior is being attached to.
 * @param array $config Array of configuration information.
 * @return mixed
 */
	public function setup(Model $model, $config = array()) {
		if($model->Behaviors->attached('Translate')){
			throw new CakeException(__('TranslationBehaviour needs to be included before TranslateBehavior due callback problems while saving.'));
		}

		$db = ConnectionManager::getDataSource($model->useDbConfig);
		if (!$db->connected) {
			trigger_error(
				__d('cake_dev', 'Datasource %s for TranslationBehavior of model %s is not connected', $model->useDbConfig, $model->alias),
				E_USER_ERROR
			);
			return false;
		}

		$this->settings[$model->alias] = array();
		$this->runtime[$model->alias] = array('fields' => $config);
		
		$config = $this->_defaultConfig + $config;
		
		// Set higher priority for TranslateBehavior callbacks to avoid callback problems
		$config['priority']++;

		// Attach translate behavior here to avoid problems by having it attached manually.
		// It has exactly the same options as this behavior so we can passed config to it.
		$model->Behaviors->load('Translate', $config);

		return true;
	}

/**
 * Cleanup Callback unbinds bound translations and deletes setting information.
 *
 * @param Model $model Model being detached.
 * @return void
 */
	public function cleanup(Model $model) {
		unset($this->settings[$model->alias]);
		unset($this->runtime[$model->alias]);
	}

/**
 * beforeValidate Callback
 * Tasks:
 *	- VALIDATEFIELDS: In here we validate all translated field by it self to make sure we have valid input everywhere
 *
 * @param Model $model Model invalidFields was called on.
 * @param array $options Validation options
 * @return boolean
 */
	public function beforeValidate(Model $model, $options = array()) {
		$errorWhileValidation = false;

		// VALIDATEFIELDS		
		if((!isset($options['callbacks']) || $options['callbacks'] === true) && isset($model->data[$model->alias])){
			$cModel = clone $model;
			$cModel->validator(new ModelValidator($cModel));

			$validationErrors = array();
			$originalData = $model->data;
			$data = $model->data[$model->alias];
			foreach($this->runtime[$model->alias]['fields'] as $field){
				if(isset($data[$field])){
					$values = $data[$field];
					if(is_array($values)){
							$cModel->data = array(
								$cModel->alias => array(
									$field => $value
								)
							);
							$valid = $cModel->validates(
								array(
									'fieldList' => array($field),
									'callbacks' => false
								)
							);
							

							if(!$valid){
								if(
									!isset($validationErrors[$field]) ||
									!is_array($validationErrors[$field])
								){
									$validationErrors[$field] = array();
								}
								
								$validationErrors[$field][$locale] = $cModel->validationErrors[$field];
								unset($cModel->validationErrors[$field]);
								$errorWhileValidation = true;
							}
						}
					}
					else{
						// @TODO: Maybe instead of skipping the field, we should throw and exception or process it as it was the default language.
						continue;
					}
				}
			}
			if($errorWhileValidation){
				$model->validationErrors = $validationErrors;
			}
			// Buggy TranslateBehavior is killing the data with every validation call.
			// This one sets the data back to the initial post state.
			// $model->data = $originalData;
			// And this one deletes all saved data while validation from above loop.
			unset(
				$model->Behaviors->Translate->runtime[$model->alias]['beforeSave'],
				$model->Behaviors->Translate->runtime[$model->alias]['beforeValidate']
			);
		}
		
		$valid = !$errorWhileValidation;
		return $valid;
	}

/**
 * beforeFind callback
 *	TASKS:
 *		- CONDITIONSFORMATTING: Make normally used model fields compatible with TranslateBehavior
 *		- ORDERFORMATTING: Make normally used model fields compatible with TranslateBehavior
 *
 * @param Model $Model Model find was run on
 * @param array $queryData The query data
 * @return mixed
 */
	public function beforeFind(Model $Model, $queryData = array()){
		// CONDITIONSFORMATTING
		if(isset($queryData['conditions']) && is_array($queryData['conditions']) && !empty($queryData['conditions'])){
			$queryData['conditions'] = $this->_prepareConditions($Model, $queryData['conditions']);
		}

		// ORDERFORMATTING
		if(isset($queryData['order']) && !empty($queryData['order'])){
			$queryData['order'] = $this->_prepareOrder($Model, $queryData['order']);
		}
		elseif(isset($this->order)){
			$queryData['order']	= $this->_prepareOrder($Model, $this->order);
		}

		return $queryData;
	}

/**
 * Prepare conditions
 *
 * @param Model $Model
 * @param array $conditions
 * @return array
 */
	protected function _prepareConditions(Model $Model, $conditions){
		if(!empty($conditions)){
			if(!is_array($conditions)){
				$conditions = array($conditions);
			}

			$bool = array('and', 'or', 'not', 'and not', 'or not', 'xor', '||', '&&');

			foreach($conditions as $key => $cond){
				if(in_array(strtolower($key), $bool)){
					$conditions[$key] = $this->_prepareConditions($Model, $cond);
				}
				else{
					foreach($this->runtime[$Model->alias]['fields'] as $field){
						$possibleFields = array(
							$field,
							$Model->alias . '.' . $field,
							$Model->escapeField($field),
						);

						if(in_array($key, $possibleFields)){
							unset($conditions[$key]);
							$alias = "I18n__{$field}.content";
							$conditions[$alias] = $cond;
						}
					}
				}
			}
		}

		return $conditions;
	}

/**
 * Prepare order
 *
 * @param Model $Model
 * @param array $order
 * @return array
 */
	protected function _prepareOrder(Model $Model, $order){
		if(!empty($order)){
			if(!is_array($order)){
				$order = array($order);
			}

			foreach($order as &$orderBunch){
				if(is_array($orderBunch)){
					foreach($orderBunch as $key => $or){
						foreach($this->runtime[$Model->alias]['fields'] as $field){
							$possibleFields = array(
								$Model->escapeField($field),
								$Model->alias . '.' . $field,
								$field,
							);

							foreach($possibleFields as $posField){
								if(preg_match('/^' . $posField . ' /i', $or) !== 0){
									$alias = "I18n__{$field}.content";
									$orderBunch[$key] = str_replace($posField, $alias, $or);
									break;
								}
							}
						}
					}
				}
				elseif(is_string($orderBunch)){
					foreach($this->runtime[$Model->alias]['fields'] as $field){
						$possibleFields = array(
							$Model->escapeField($field),
							$Model->alias . '.' . $field,
							$field,
						);

						foreach($possibleFields as $posField){
							if(preg_match('/^' . $posField . ' /i', $orderBunch) !== 0){
								$alias = "I18n__{$field}.content";
								$orderBunch = str_replace($posField, $alias, $orderBunch);
								break;
							}
						}
					}
				}
			}
		}

		return $order;
	}
	
/**
 * afterFind Callback (modified)
 * As copied from TranslateBehavior (CakePHP 2.3.1 commit 433dd09ec4)
 *
 * @param Model $Model Model find was run on
 * @param array $results Array of model results.
 * @param boolean $primary Did the find originate on $model.
 * @return array Modified results
 */
	public function afterFind(Model $Model, $results, $primary) {
		$locale = $this->_getLocale($Model);

		if (empty($locale) || empty($results) || empty($Model->Behaviors->Translate->runtime[$Model->alias]['beforeFind'])) {
			return $results;
		}
		$beforeFind = $Model->Behaviors->Translate->runtime[$Model->alias]['beforeFind'];

		foreach ($results as $key => &$row) {
			// @HACK:ED AWAY --> $results[$key][$Model->alias]['locale'] = (is_array($locale)) ? current($locale) : $locale; */
			foreach ($beforeFind as $_f => $field) {
				$aliasField = is_numeric($_f) ? $field : $_f;
				$aliasVirtual = "i18n_{$field}";
				if (is_array($locale)) {
					// @HACK: Fixing the problems caused by the form helper if the $aliasField stays a string. (Showing just the first letter in case there is not even one translation present and this translation helper is included later)
					$row[$Model->alias][$aliasField] = array();
					// Instead of just getting ONE available translation we get all available that was wanted by setting the locale.
					foreach ($locale as $_locale) {
						$aliasVirtualLocale = "{$aliasVirtual}_{$_locale}";
						/* @HACK:ED AWAY --> if (!isset($row[$Model->alias][$aliasField]) && !empty($row[$Model->alias][$aliasVirtualLocale])) { */
						if(!empty($row[$Model->alias][$aliasVirtualLocale])){
							$row[$Model->alias][$aliasField][$_locale] = $row[$Model->alias][$aliasVirtualLocale];
							/* @HACK:ED AWAY --> $row[$Model->alias]['locale'] = $_locale; */
						}
						unset($row[$Model->alias][$aliasVirtualLocale]);
					}

					if (!isset($row[$Model->alias][$aliasField])) {
						$row[$Model->alias][$aliasField] = '';
					}
				} else {
					$value = '';
					if (!empty($row[$Model->alias][$aliasVirtual])) {
						$value = $row[$Model->alias][$aliasVirtual];
					}
					$row[$Model->alias][$aliasField] = $value;
					unset($row[$Model->alias][$aliasVirtual]);
				}
			}
		}
		
		/**
		 * Unset beforeFind runtime saving of TranslateBehavior to prevent TranslateBehavior::afterFind() from killing content from
		 * single locale findings.
		 */
		unset($Model->Behaviors->Translate->runtime[$Model->alias]['beforeFind']);
		
		return $results;
	}
	
/**
 * Get selected locale for model
 * As copied from TranslateBehavior (CakePHP 2.3.1 commit 433dd09ec4)
 *
 * @param Model $Model Model the locale needs to be set/get on.
 * @return mixed string or false
 */
	protected function _getLocale(Model $Model) {
		if (!isset($Model->locale) || is_null($Model->locale)) {
			$I18n = I18n::getInstance();
			$I18n->l10n->get(Configure::read('Config.language'));
			$Model->locale = $I18n->l10n->locale;
		}

		return $Model->locale;
	}
}
?>
