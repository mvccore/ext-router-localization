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
	 * Key name for language or/and locale in second argument $params in $router->Url();  method,
	 * to tell $router->Url() method to generate url in different locale.
	 */
	const LOCALIZATION_URL_PARAM = 'localization';

	/**
	 * Special $_GET param name for session strict mode, how to change site locale version.
	 */
	const SWITCH_LOCALIZATION_URL_PARAM = 'switch_localization';

	/**
	 * International language and locale code separator used in url address.
	 */
	const LANG_AND_LOCALE_SEPARATOR = '-';

	/**
	 * Source url param name, when first request is redirected to default localization by configuration.
	 */
	const REDIRECTED_SOURCE_URL_PARAM = 'source_url';
}
