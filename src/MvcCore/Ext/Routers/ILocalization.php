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

/**
 * Responsibility - recognize localizationn from URL or from http header or session and set 
 *					up request object, complete automatically rewritten URL with remembered 
 *					localization version. Redirect to proper localization by configuration.
 *					Than route request like parent class does.
 */
interface ILocalization
{
	/**
	 * Key name for language or/and locale in second argument $params in $router->Url();  method,
	 * to tell $router->Url() method to generate URL in different locale.
	 */
	const URL_PARAM_LOCALIZATION = 'localization';

	/**
	 * Special $_GET param name for session strict mode, how to change site locale version.
	 */
	const URL_PARAM_SWITCH_LOCALIZATION = 'switch_localization';

	/**
	 * Source URL param name, when first request is redirected to default localization by configuration.
	 */
	const URL_PARAM_REDIRECTED_SOURCE = 'source_url';

	/**
	 * International language and locale code separator used in URL address.
	 */
	const LANG_AND_LOCALE_SEPARATOR = '-';


	/**
	 * Get default language and locale. Language is always defined as two lower case 
	 * characters - internaltional language code and locale is always defined as
	 * two or three upper case characters or digits - international locale code.
	 * Default localization is used in cases, when is not possible to detect 
	 * language and locale from URL or when is not possible to detect language 
	 * and locale from `Accept-Language` http header or not possible to get 
	 * previous localization from session.
	 * @return \string[]
	 */
	public function GetDefaultLocalization ();

	/**
	 * Set default language and locale. Language has to be defined as two lower case 
	 * characters - internaltional language code and locale has to be defined as
	 * two or three upper case characters or digits - international locale code.
	 * Default localization is used in cases, when is not possible to detect 
	 * language and locale from URL or when is not possible to detect language 
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
	 * language code will be enough to choose final target application 
	 * localization. It will be chosen first localization in allowed list with 
	 * detected language. `TRUE` by default. If `FALSE`, then there is necessary 
	 * to send into application in `Accept-Language` http header international 
	 * language code together with international locale code with the only same 
	 * combination which application has configured in allowed localizations only.
	 * @return bool
	 */
	public function GetDetectLocalizationOnlyByLang ();

	/**
	 * Set detect localization only by language record from `Accept-Language` http 
	 * header record, not together with locale code. Parsed international 
	 * language code will be enough to choose final target application 
	 * localization. It will be chosen first localization in allowed list with 
	 * detected language. `TRUE` by default. If `FALSE`, then there is necessary 
	 * to send into application in `Accept-Language` http header international 
	 * language code together with international locale code with the only same 
	 * combination which application has configured in allowed localizations only.
	 * @param bool $detectLocalizationOnlyByLang
	 * @return \MvcCore\Ext\Routers\ILocalization
	 */
	public function & SetDetectLocalizationOnlyByLang ($detectLocalizationOnlyByLang = TRUE);

	/**
	 * Get list of allowed localization strings in your application, default 
	 * localization will be allowed automatically. List is returned as array of 
	 * strings. Every item has to be international language code or it has to be
	 * international language code and international locale code separated by
	 * dash.
	 * @return array
	 */
	public function GetAllowedLocalizations ();

	/**
	 * Set list of allowed localization strings in your application, default 
	 * localization will be allowed automatically. List has to be defined as array 
	 * of strings. Every item has to be international language code or it has to be
	 * international language code and international locale code separated by
	 * dash. All previously defined allowed localizations will be replaced.
	 * Default localization is always allowed automatically.
	 * @var string $allowedLocalizations..., International lower case language code(s) (+ optionally dash character + upper case international locale code(s))
	 * @return \MvcCore\Ext\Routers\ILocalization
	 */
	public function & SetAllowedLocalizations (/* ...$allowedLocalizations */);

	/**
	 * Add list of allowed localization strings in your application, default 
	 * localization will be allowed automatically. List has to be defined as array 
	 * of strings. Every item has to be international language code or it has to be
	 * international language code and international locale code separated by
	 * dash. 
	 * Default localization is always allowed automatically.
	 * @var string $allowedLocalizations..., International lower case language code(s) (+ optionally dash character + upper case international locale code(s))
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
	 * for URL strings.
	 * @return bool
	 */
	public function GetRouteRecordsByLanguageAndLocale ();

	/**
	 * If `TRUE` (default `FALSE`), route records like `pattern`, `match`, 
	 * `reverse` or `defaults` has to be defined by international language code 
	 * and international locale code, not only by language code by default.
	 * This option is very rare, if different locales have different naming 
	 * for URL strings.
	 * @param bool $routeRecordsByLanguageAndLocale
	 * @return \MvcCore\Ext\Routers\ILocalization
	 */
	public function & SetRouteRecordsByLanguageAndLocale ($routeRecordsByLanguageAndLocale = TRUE);
}
