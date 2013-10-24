TranslationPlugin
=================

A Translation plugin for CakePHP

Introduction
------------
This branch has user support for language collection.
It has the same functionality as the plugin from the "master" branch, but includes support for language collection with a given id from the logged in user.

Please read the [README](https://github.com/func0der/TranslationPlugin/blob/master/README.md) of master branch to get to know the whole plugin.

Some things to keep in mind
---------------------------
For this plugin version there is a `languages` table needed, which is included in a schema file in the __Config/Schema__ folder.
You need to execute it via shell or use the sql file also to be found in the above folder.

Also the TranslationComponent relies on a Controller having a "getCurrentUser" method which should return an array in the following format.

	array(
		'User' => array(
			// ... user data ...
			'language_id' => $languageIdFromLanguagesTable,
			'is_super' => (TRUE|FALSE),
		)
	)

If **language_id** is set the user only gets the language/locale from the `languages` table.

If **is_super** is set the user gets every language specified in the config.

Contribution
------------
Please feel free to report any issues you find or make pull request.

The current state of this plugin is maybe not the best of all, but this plugin grew with it needs and this is what came out of it.
Feel free to contribute changes to make it more flexible.
