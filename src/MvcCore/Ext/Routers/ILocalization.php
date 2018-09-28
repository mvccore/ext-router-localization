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


	/**
	 * Get default language and locale. Language is always defined as two lowercase 
	 * characters - internaltional language code and locale is always defined as
	 * two or three uppercase characters or digits - international locale code.
	 * Default localization is used in cases, when is not possible to detect 
	 * language and locale from url or when is not possible to detect language 
	 * and locale from `Accept-Language` http header or not possible to get 
	 * previous localization from session.
	 * @return \string[]
	 */
	public function GetDefaultLocalization ();

	/**
	 * Set default language and locale. Language has to be defined as two lowercase 
	 * characters - internaltional language code and locale has to be defined as
	 * two or three uppercase characters or digits - international locale code.
	 * Default localization is used in cases, when is not possible to detect 
	 * language and locale from url or when is not possible to detect language 
	 * and locale from `Accept-Language` http header or not possible to get 
	 * previous localization from session.
	 * @var string $defaultLocalizationOrLanguage It could be `en` or `en-US`, `en-GB`...
	 * @var string $defaultLocale It could be `US`, `GB`...
	 * @return \MvcCore\Ext\Routers\ILocalization
	 */
	public function & SetDefaultLocalization ($defaultLocalizationOrLanguage, $defaultLocale = NULL);

	/**
	 * Get current router context localization value. It could contain in first 
	 * index international language code string and nothing more or the language 
	 * under first index and international locale code under second index.
	 * If there are no language and locale detected, returned array is empty.
	 * @param bool $asString `FALSE` by default to get array with lang and locale, 
	 *						 `TRUE` to get lang and locale as string.
	 * @return string|array
	 */
	public function GetLocalization ($asString = FALSE);

	/**
	 * Set current router context localization value. It could contain in first 
	 * index international language code string and nothing more or the language 
	 * under first index and international locale code under second index.
	 * @param string $lang 
	 * @param string $locale 
	 * @throws \InvalidArgumentException Localization must be defined at least by the language.
	 * @return \MvcCore\Ext\Routers\ILocalization
	 */
	public function & SetLocalization ($lang, $locale = NULL);

	/**
	 * If `TRUE`, redirect first request by session to default localization 
	 * version if localization in request is not allowed.
	 * If not configured, `FALSE` by default to not redirect in first request to
	 * default localization version but to route requested localization version.
	 * @return boolean
	 */
	public function GetRedirectFirstRequestToDefault ();

	/**
	 * If `TRUE`, redirect first request by session to default localization 
	 * version if localization in request is not allowed.
	 * If not configured, `FALSE` by default to not redirect in first request to
	 * default localization version but to route requested localization version.
	 * @param bool $redirectFirstRequestToDefault
	 * @return \MvcCore\Ext\Routers\ILocalization
	 */
	public function & SetRedirectFirstRequestToDefault ($redirectFirstRequestToDefault = TRUE);

	/**
	 * `TRUE` by default to allow routing with non-localized routes.
	 * If `FALSE` non-localized routes are ingored and there is thrown an 
	 * exception in development environment.
	 * @return bool
	 */
	public function GetAllowNonLocalizedRoutes ();

	/**
	 * `TRUE` by default to allow routing with non-localized routes.
	 * If `FALSE` non-localized routes are ingored and there is thrown an 
	 * exception in development environment.
	 * @param bool $allowNonLocalizedRoutes
	 * @return \MvcCore\Ext\Routers\ILocalization
	 */
	public function & SetAllowNonLocalizedRoutes ($allowNonLocalizedRoutes = TRUE);

	/**
	 * Get detect localization only by language record from `Accept-Language` http 
	 * header record, not together with locale code. Parsed international 
	 * language code will be enough to choose final target aplication 
	 * localization. It will be choosed first localization in allowed list with 
	 * detected language. `TRUE` by default. If `FALSE`, then there is necessary 
	 * to send into application in `Accept-Language` http header international 
	 * language code together with international locale code with the only same 
	 * combination which application has configured in allowed localizations only.
	 * @return bool
	 */
	public function GetDetectAcceptLanguageOnlyByLang ();

	/**
	 * Set detect localization only by language record from `Accept-Language` http 
	 * header record, not together with locale code. Parsed international 
	 * language code will be enough to choose final target aplication 
	 * localization. It will be choosed first localization in allowed list with 
	 * detected language. `TRUE` by default. If `FALSE`, then there is necessary 
	 * to send into application in `Accept-Language` http header international 
	 * language code together with international locale code with the only same 
	 * combination which application has configured in allowed localizations only.
	 * @param bool $detectAcceptLanguageOnlyByLang
	 * @return \MvcCore\Ext\Routers\ILocalization
	 */
	public function & SetDetectAcceptLanguageOnlyByLang ($detectAcceptLanguageOnlyByLang = TRUE);

	/**
	 * Get list of allowed localization strings in your application, default 
	 * localization will be allowed automaticly. List is returned as array of 
	 * strings. Every item has to be international language code or it has to be
	 * international language code and international locale code separated by
	 * dash.
	 * @return array
	 */
	public function & GetAllowedLocalizations ();

	/**
	 * Set list of allowed localization strings in your application, default 
	 * localization will be allowed automaticly. List has to be defined as array 
	 * of strings. Every item has to be international language code or it has to be
	 * international language code and international locale code separated by
	 * dash. All previously defined allowed localizations will be replaced.
	 * Default localization is always allowed automaticly.
	 * @var string $allowedLocalizations..., International lowercase language code(s) (+ optinally dash character + uppercase international locale code(s))
	 * @return \MvcCore\Ext\Routers\ILocalization
	 */
	public function & SetAllowedLocalizations (/* ...$allowedLocalizations */);

	/**
	 * Add list of allowed localization strings in your application, default 
	 * localization will be allowed automaticly. List has to be defined as array 
	 * of strings. Every item has to be international language code or it has to be
	 * international language code and international locale code separated by
	 * dash. 
	 * Default localization is always allowed automaticly.
	 * @var string $allowedLocalizations..., International lowercase language code(s) (+ optinally dash character + uppercase international locale code(s))
	 * @return \MvcCore\Ext\Routers\ILocalization
	 */
	public function & AddAllowedLocalizations (/* ...$allowedLocalizations */);
	
	/**
	 * Get list of localization equivalents used in localization detection by http
	 * header `Accept-Language` parsed in first request. It could be used for 
	 * language very similar countries like Ukraine & Rusia, Czech & Slovakia ...
	 * Keys in this array is target localization, value is an array with target 
	 * localization equivalents.
	 * @return array
	 */
	public function & GetLocalizationEquivalents ();

	/**
	 * Set list of localization equivalents used in localization detection by http
	 * header `Accept-Language` parsed in first request. It could be used for 
	 * language very similar countries like Ukraine & Rusia, Czech & Slovakia ...
	 * Keys in this array is target localization, value is an array with target 
	 * localization equivalents. All previously configured localization equivalents
	 * will be replaced with given configuration.
	 * @param array $localizationEquivalents Keys in this array is target localization, value is an array with target localization equivalents.
	 * @return \MvcCore\Ext\Routers\ILocalization
	 */
	public function & SetLocalizationEquivalents (array $localizationEquivalents = []);
	
	/**
	 * Add or merge items in list with localization equivalents used in localization 
	 * detection by http header `Accept-Language` parsed in first request. It could 
	 * be used for language very similar countries like Ukraine & Rusia, Czech & Slovakia ...
	 * Keys in this array is target localization, value is an array with target 
	 * localization equivalents. All previously configured localization equivalents
	 * will be merged with given configuration.
	 * @param array $localizationEquivalents Keys in this array is target localization, value is an array with target localization equivalents.
	 * @return \MvcCore\Ext\Routers\ILocalization
	 */
	public function & AddLocalizationEquivalents (array $localizationEquivalents = []);

	/**
	 * If `TRUE` (default `FALSE`), route records like `pattern`, `match`, 
	 * `reverse` or `defaults` has to be defined by international language code 
	 * and international locale code, not only by language code by default.
	 * This option is very rare, if different locales have different naming 
	 * for url strings.
	 * @return bool
	 */
	public function GetRouteRecordsByLanguageAndLocale ();

	/**
	 * If `TRUE` (default `FALSE`), route records like `pattern`, `match`, 
	 * `reverse` or `defaults` has to be defined by international language code 
	 * and international locale code, not only by language code by default.
	 * This option is very rare, if different locales have different naming 
	 * for url strings.
	 * @param bool $routeRecordsByLanguageAndLocale
	 * @return \MvcCore\Ext\Routers\ILocalization
	 */
	public function & SetRouteRecordsByLanguageAndLocale ($routeRecordsByLanguageAndLocale = TRUE);

	/**
	 * Complete non-absolute, non-localized or localized url by route instance reverse info.
	 * Example:
	 *	Input (`\MvcCore\Route::$reverse`):
	 *	`[
	 *		"en"	=> "/products-list/<name>/<color>"`,
	 *		"de"	=> "/produkt-liste/<name>/<color>"`,
	 *	]`
	 *	Input ($params):
	 *		`array(
	 *			"name"			=> "cool-product-name",
	 *			"color"			=> "red",
	 *			"variant"		=> ["L", "XL"],
	 *			"localization"	=> "en-US",
	 *		);`
	 *	Output:
	 *		`/application/base-bath/en-US/products-list/cool-product-name/blue?variant[]=L&amp;variant[]=XL"`
	 * @param \MvcCore\IRoute &$route
	 * @param array $params
	 * @return string
	 */
	public function UrlByRoute (\MvcCore\IRoute & $route, & $params = []);

	/**
	 * Route current application request by configured routes list or by query string data.
	 * - Complete before every request from requested path requested localization string
	 *   (language and locale codes) and compare it with session by configuration. If there
	 *   is nothing from previous requests, recognize browser language by `Accept-Language`
	 *   http header, store it in session if anything parsed and continue or redirect by configuration.
	 * - If there is strictly defined `controller` and `action` value in query string,
	 *   route request by given values, add new route and complete new empty
	 *   `\MvcCore\Router::$currentRoute` route with `controller` and `action` values from query string.
	 * - If there is no strictly defined `controller` and `action` value in query string,
	 *   go throught all configured routes and try to find matching route:
	 *   - If there is catched any matching route:
	 *	 - Set up `\MvcCore\Router::$currentRoute`.
	 *	 - Reset `\MvcCore\Request::$params` again with with default route params,
	 *	   with request params itself and with params parsed from matching process.
	 * - If there is no route matching the request and also if the request is targeting homepage
	 *   or there is no route matching the request and also if the request is targeting something
	 *   else and also router is configured to route to default controller and action if no route
	 *   founded, complete `\MvcCore\Router::$currentRoute` with new empty automaticly created route
	 *   targeting default controller and action by configuration in application instance (`Index:Index`)
	 *   and route type create by configured `\MvcCore\Application::$routeClass` class name.
	 * - Return `TRUE` if `\MvcCore\Router::$currentRoute` is route instance or `FALSE` for redirection.
	 *
	 * This method is always called from core routing by:
	 * - `\MvcCore\Application::Run();` => `\MvcCore\Application::routeRequest();`.
	 * @return bool
	 */
	public function Route ();
}
