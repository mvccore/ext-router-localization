<?php

/**
 * MvcCore
 *
 * This source file is subject to the BSD 3 License
 * For the full copyright and license information, please view
 * the LICENSE.md file that are distributed with this source code.
 *
 * @copyright	Copyright (c) 2016 Tom Flídr (https://github.com/mvccore/mvccore)
 * @license		https://mvccore.github.io/docs/mvccore/4.0.0/LICENCE.md
 */

namespace MvcCore\Ext\Routers;

interface ILocalization
{
	/**
	 * MvcCore Extension - Router Lang - version:
	 * Comparation by PHP function version_compare();
	 * @see http://php.net/manual/en/function.version-compare.php
	 */
	const VERSION = '5.0.0-alpha';

	/**
	 * Key name for language or/and locale in second argument $params in $router->Url();  method,
	 * to tell $router->Url() method to generate url in different locale.
	 */
	const LOCATIZATION_URL_PARAM = 'locatization';

	/**
	 * Special $_GET param name for session strict mode, how to change site locale version.
	 */
	const SWITCH_LOCATIZATION_URL_PARAM = 'switch_locatization';

	/**
	 * International language and locale code separator used in url address.
	 */
	const LANG_AND_LOCALE_SEPARATOR = '-';
}
