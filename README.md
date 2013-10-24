TranslationPlugin
=================

A Translation plugin for CakePHP


Introduction
------------
This plugin provides support for multiple translation saving at once.
It includes validating those entries with the given validation method in model file and also supports "order" and "conditions" automatic replacement of translated fields.


How to use
----------


### Edit/Save multiple translations


#### Model
Because this plugin is an extension to TranslateBehavior of CakePHP it has to be included in a very special way.
This is why TranslationBehavior includes TranslateBehavior itself, so you do not have attach the two behaviors separately.

	public $actsAs = array(
		'Translation.Translation' => array(
			'label',
			'description',
		),
	);

Every config variable will also be passed on to the TranslateBehavior.

If you want validation for the fields you specified for translation you can just define them like you would if they were not translated.


#### View
In the view you need to use the TranslationHelper to define new formular fields:

	echo $this->Translation->input(
		'News.label',
		array(
			'legend' => __('Label'),
		)
	);

As for some internal reasons you need to specify the model you are using with the field this formular input is for.
If you forget this an exception will be thrown to remind you of it.

In the above case a field for every language is created and they will be output in a fieldset with the legend "Label". The input generated for each language will have their specific locales as labels.
You can also specify a label for every language available. See the TranslationHelper file for information about that until the wiki is ready.

Every config parameter is parsed to the FormHelper::input() method, except for "fieldset" and "legend".


#### Controller


##### TranslationComponent
Include the TranslationComponent:

	public $components = array(
		// Maybe other components here...
		'Translation.Translation',
		// Maybe more other components here...
	);

By default the TranslationComponent would then autodetect available languages from your "[AppRoot]/Locales" folder.
Other configuration is available. You can find them in the TranslationComponent file until wiki is ready.


###### TranslationHelper
You also need to add the TranslationHelper:

	public $helpers = array(
		// Maybe other helpers here
		'Translation.Translation',
		// Maybe more other helpers here
	);

I am expecting default scaffolding actions here.
In the add action you do not need to do anything special if you included the above.

In the edit action you need to specify the locales to get from the database to fill all the formular inputs in the view:

	// Translation plugin stuff
	$this->News->locale = $this->Translation->getLanguages();
	// Default cakephp stuff
	$options = array('conditions' => array('News.' . $this->News->primaryKey => $id));
	$this->request->data = $this->News->find('first', $options);

You only need to include this directly before the find. It is not necessary to be there for the saving process.


Configuration
-------------
If you want to configure the languages and not read them automatically from the "[AppRoot]/Locales" folder, you can do that via the Configure class.
See the example config in "Translation/Config/".


Contribution
------------
Please feel free to report any issues you find or make pull request.

The current state of this plugin is maybe not the best of all, but this plugin grew with it needs and this is what came out of it.
Feel free to contribute changes to make it more flexible.


License
-------

Punchcard is licensed under GPLv2.

Every tool or library included in it may be licensed under its own license.


Warranty
--------

Absolutly no warranty for this app or its used tools and libraries is given by me.