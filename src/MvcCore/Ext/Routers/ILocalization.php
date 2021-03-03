<?php

/**
 * MvcCore
 *
 * This source file is subject to the BSD 3 License
 * For the full copyright and license information, please view
 * the LICENSE.md file that are distributed with this source code.
 *
 * @copyright	Copyright (c) 2016 Tom Flidr (https://github.com/mvccore)
 * @license		https://mvccore.github.io/docs/mvccore/5.0.0/LICENSE.md
 */

namespace MvcCore\Ext\Routers;

/**
 * Responsibility - recognize localization from URL or from http header or 
 *					session and set up request object, complete automatically 
 *					rewritten URL with remembered localization version. Redirect 
 *					to proper localization by configuration. Than route request 
 *					like parent class does. Generate URL addresses with prefixed 
 *					localization for localized routes or add only localization 
 *					into query string where necessary.
 */
interface ILocalization {

	/**
	 * Key name for language or/and locale in second argument `$params` in 
	 * `$router->Url();` method. To tell to the method to generate URL in 
	 * different localization.
	 */
	const URL_PARAM_LOCALIZATION = 'localization';

	/**
	 * Special `$_GET` param name for session strict mode, how to change 
	 * site localization version.
	 */
	const URL_PARAM_SWITCH_LOCALIZATION = 'switch_localization';

	/**
	 * Source URL param name, when first request is redirected to default 
	 * localization by configuration.
	 */
	const URL_PARAM_REDIRECTED_SOURCE = 'source_url';

	/**
	 * International language and locale code separator used in URL address.
	 */
	const LANG_AND_LOCALE_SEPARATOR = '-';


	/**
	 * Get default language and locale. Language is always defined as two lower case 
	 * characters - international language code and locale is always defined as
	 * two or three upper case characters or digits - international locale code.
	 * Default localization is used in cases, when is not possible to detect 
	 * language and locale from URL or when is not possible to detect language 
	 * and locale from `Accept-Language` http header or not possible to get 
	 * previous localization from session.
	 * @param bool $asString `FALSE` by default to get array with lang and locale, 
	 *						 `TRUE` to get lang and locale as string.
	 * @return string|\string[]
	 */
	public function GetDefaultLocalization ($asString = FALSE);

	/**
	 * Set default language and locale. Language has to be defined as two lower case 
	 * characters - international language code and locale has to be defined as
	 * two or three upper case characters or digits - international locale code.
	 * Default localization is used in cases, when is not possible to detect 
	 * language and locale from URL or when is not possible to detect language 
	 * and locale from `Accept-Language` http header or not possible to get 
	 * previous localization from session.
	 * @var string $defaultLocalizationOrLanguage It could be `en` or `en-US`, `en-GB`...
	 * @var string $defaultLocale It could be `US`, `GB`...
	 * @return \MvcCore\Ext\Routers\Localization
	 */
	public function SetDefaultLocalization ($defaultLocalizationOrLanguage, $defaultLocale = NULL);

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
	 * @return \MvcCore\Ext\Routers\Localization
	 */
	public function SetLocalization ($lang, $locale = NULL);

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
	 * @return \MvcCore\Ext\Routers\Localization
	 */
	public function SetRedirectFirstRequestToDefault ($redirectFirstRequestToDefault = TRUE);

	/**
	 * `TRUE` by default to allow routing with non-localized routes.
	 * If `FALSE` non-localized routes are ignored and there is thrown an 
	 * exception in development environment.
	 * @return bool
	 */
	public function GetAllowNonLocalizedRoutes ();

	/**
	 * `TRUE` by default to allow routing with non-localized routes.
	 * If `FALSE` non-localized routes are ignored and there is thrown an 
	 * exception in development environment.
	 * @param bool $allowNonLocalizedRoutes
	 * @return \MvcCore\Ext\Routers\Localization
	 */
	public function SetAllowNonLocalizedRoutes ($allowNonLocalizedRoutes = TRUE);

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
	 * Set detect localization only by language from `Accept-Language` http 
	 * header record, not together with locale code. Parsed international 
	 * language code will be enough to choose final target application 
	 * localization. It will be chosen first localization in allowed list with 
	 * detected language. `TRUE` by default. If `FALSE`, then there is necessary 
	 * to send into application in `Accept-Language` http header international 
	 * language code together with international locale code with the only same 
	 * combination which application has configured in allowed localizations only.
	 * @param bool $detectLocalizationOnlyByLang
	 * @return \MvcCore\Ext\Routers\Localization
	 */
	public function SetDetectLocalizationOnlyByLang ($detectLocalizationOnlyByLang = TRUE);

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
	 * @var string $allowedLocalizations...,	International lower case language 
	 *											code(s) (+ optionally dash character 
	 *											+ upper case international locale 
	 *											code(s)).
	 * @return \MvcCore\Ext\Routers\Localization
	 */
	public function SetAllowedLocalizations ($allowedLocalizations);

	/**
	 * Add list of allowed localization strings in your application, default 
	 * localization will be allowed automatically. List has to be defined as array 
	 * of strings. Every item has to be international language code or it has to be
	 * international language code and international locale code separated by
	 * dash. 
	 * Default localization is always allowed automatically.
	 * @var string $allowedLocalizations...,	International lower case language 
	 *											code(s) (+ optionally dash character 
	 *											+ upper case international locale 
	 *											code(s)).
	 * @return \MvcCore\Ext\Routers\Localization
	 */
	public function AddAllowedLocalizations ($allowedLocalizations);
	
	/**
	 * Get list of localization equivalents used in localization detection by http
	 * header `Accept-Language` parsed in first request. It could be used for 
	 * language very similar countries like Ukraine & Russia, Czech & Slovakia ...
	 * Keys in this array is target localization, value is an array with target 
	 * localization equivalents.
	 * @return array
	 */
	public function GetLocalizationEquivalents ();

	/**
	 * Set list of localization equivalents used in localization detection by http
	 * header `Accept-Language` parsed in first request. It could be used for 
	 * language very similar countries like Ukraine & Russia, Czech & Slovakia ...
	 * Keys in this array is target localization, value is an array with target 
	 * localization equivalents. All previously configured localization equivalents
	 * will be replaced with given configuration.
	 * @param array $localizationEquivalents	Keys in this array is target 
	 *											localization, value is an array 
	 *											with target localization equivalents.
	 * @return \MvcCore\Ext\Routers\Localization
	 */
	public function SetLocalizationEquivalents (array $localizationEquivalents = []);
	
	/**
	 * Add or merge items in list with localization equivalents used in localization 
	 * detection by http header `Accept-Language` parsed in first request. It could 
	 * be used for language very similar countries like Ukraine & Russia, Czech & Slovakia ...
	 * Keys in this array is target localization, value is an array with target 
	 * localization equivalents. All previously configured localization equivalents
	 * will be merged with given configuration.
	 * @param array $localizationEquivalents	Keys in this array is target 
	 *											localization, value is an array 
	 *											with target localization equivalents.
	 * @return \MvcCore\Ext\Routers\Localization
	 */
	public function AddLocalizationEquivalents (array $localizationEquivalents = []);

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
	 * @return \MvcCore\Ext\Routers\Localization
	 */
	public function SetRouteRecordsByLanguageAndLocale ($routeRecordsByLanguageAndLocale = TRUE);

	/**
	 * Append or prepend new request routes.
	 * If there is no name configured in route array configuration,
	 * set route name by given `$routes` array key, if key is not numeric.
	 *
	 * Routes could be defined in various forms:
	 * Example:
	 *	`\MvcCore\Router::GetInstance()->AddRoutes([
	 *		"Products:List"	=> "/products-list/<name>/<color>",
	 *	], "eshop");`
	 * or:
	 *	`\MvcCore\Router::GetInstance()->AddRoutes([
	 *		'products_list'	=> [
	 *			"pattern"			=>  [
	 *				"en"				=> "/products-list/<name>/<color>",
	 *				"de"				=> "/produkt-liste/<name>/<color>"
	 *			],
	 *			"controllerAction"	=> "Products:List",
	 *			"defaults"			=> ["name" => "default-name",	"color" => "red"],
	 *			"constraints"		=> ["name" => "[^/]*",			"color" => "[a-z]*"]
	 *		]
	 *	], ["en" => "eshop", "de" => "einkaufen"]);`
	 * or:
	 *	`\MvcCore\Router::GetInstance()->AddRoutes([
	 *		new Route(
	 *			"/products-list/<name>/<color>",
	 *			"Products:List",
	 *			["name" => "default-name",	"color" => "red"],
	 *			["name" => "[^/]*",			"color" => "[a-z]*"]
	 *		)
	 *	]);`
	 * or:
	 *	`\MvcCore\Router::GetInstance()->AddRoutes([
	 *		new Route(
	 *			"name"			=> "products_list",
	 *			"pattern"		=> "#^/products\-list/(?<name>[^/]*)/(?<color>[a-z]*)(?=/$|$)#",
	 *			"reverse"		=> "/products-list/<name>/<color>",
	 *			"controller"	=> "Products",
	 *			"action"		=> "List",
	 *			"defaults"		=> ["name" => "default-name",	"color" => "red"],
	 *		)
	 *	]);`
	 * @param \MvcCore\Ext\Routers\Localizations\Route[]|array $routes 
	 *				Keyed array with routes, keys are route names or route
	 *				`Controller::Action` definitions.
	 * @param string|array|NULL $groupNames 
	 *				Group name or names is first matched/parsed word(s) in 
	 *				requested path to group routes by to try to match only routes 
	 *				you really need, not all of them. If `NULL` by default, routes 
	 *				are inserted into default group. If argument is an array, it 
	 *				must contain localization keys and localized group names.
	 * @param bool $prepend	
	 *				Optional, if `TRUE`, all given routes will be prepended from 
	 *				the last to the first in given list, not appended.
	 * @param bool $throwExceptionForDuplication 
	 *				`TRUE` by default. Throw an exception, if route `name` or 
	 *				route `Controller:Action` has been defined already. If 
	 *				`FALSE` old route is overwritten by new one.
	 * @return \MvcCore\Ext\Routers\Localization
	 */
	public function AddRoutes (array $routes = [], $groupNames = NULL, $prepend = FALSE, $throwExceptionForDuplication = TRUE);

	/**
	 * Clear all possible previously configured routes
	 * and set new given request routes again.
	 * If there is no name configured in route array configuration,
	 * set route name by given `$routes` array key, if key is not numeric.
	 *
	 * Routes could be defined in various forms:
	 * Example:
	 *	`\MvcCore\Router::GetInstance()->SetRoutes([
	 *		"Products:List"	=> "/products-list/<name>/<color>",
	 *	], "eshop");`
	 * or:
	 *	`\MvcCore\Router::GetInstance()->SetRoutes([
	 *		'products_list'	=> [
	 *			"pattern"			=>  [
	 *				"en"				=> "/products-list/<name>/<color>",
	 *				"de"				=> "/produkt-liste/<name>/<color>"
	 *			],
	 *			"controllerAction"	=> "Products:List",
	 *			"defaults"			=> ["name" => "default-name",	"color" => "red"],
	 *			"constraints"		=> ["name" => "[^/]*",			"color" => "[a-z]*"]
	 *		]
	 *	], ["en" => "eshop", "de" => "einkaufen"]);`
	 * or:
	 *	`\MvcCore\Router::GetInstance()->SetRoutes([
	 *		new Route(
	 *			"/products-list/<name>/<color>",
	 *			"Products:List",
	 *			["name" => "default-name",	"color" => "red"],
	 *			["name" => "[^/]*",			"color" => "[a-z]*"]
	 *		)
	 *	]);`
	 * or:
	 *	`\MvcCore\Router::GetInstance()->SetRoutes([
	 *		new Route(
	 *			"name"			=> "products_list",
	 *			"pattern"		=> "#^/products\-list/(?<name>[^/]*)/(?<color>[a-z]*)(?=/$|$)#",
	 *			"reverse"		=> "/products-list/<name>/<color>",
	 *			"controller"	=> "Products",
	 *			"action"		=> "List",
	 *			"defaults"		=> ["name" => "default-name",	"color" => "red"],
	 *		)
	 *	]);`
	 * @param \MvcCore\Route[]|\MvcCore\Ext\Routers\Localizations\Route[]|array $routes 
	 *				Keyed array with routes, keys are route names or route
	 *				 `Controller::Action` definitions.
	 * @param string|array|NULL $groupNames 
	 *				Group name or names is first matched/parsed word(s) in 
	 *				requested path to group routes by to try to match only routes 
	 *				you really need, not all of them. If `NULL` by default, routes 
	 *				are inserted into default group. If argument is an array, it 
	 *				must contain localization keys and localized group names.
	 * @param bool $autoInitialize 
	 *				If `TRUE`, locale routes array is cleaned and then all 
	 *				routes (or configuration arrays) are sent into method 
	 *				`$router->AddRoutes();`, where are routes auto initialized 
	 *				for missing route names or route controller or route action
	 *				records, completed always from array keys. You can you 
	 *				`FALSE` to set routes without any change or auto-init, it 
	 *				could be useful to restore cached routes etc.
	 * @return \MvcCore\Ext\Routers\Localization
	 */
	public function SetRoutes ($routes = [], $groupNames = NULL, $autoInitialize = TRUE);

	/**
	 * Route current app request by configured routes lists or by query string.
	 * 1. Check if request is targeting any internal action in internal ctrl.
	 * 2. Choose route strategy by request path and existing query string 
	 *    controller and/or action values - strategy by query string or by 
	 *    rewrite routes.
	 * 3. If request is not internal, redirect to possible better URL form by
	 *    configured trailing slash strategy and return `FALSE` for redirection.
	 * 4. Prepare localization properties and redirect if necessary.
	 * 5. Try to complete current route object by chosen strategy.
	 * 6. If there was not found any rewrite route in rewrite routes strategy, 
	 *    also if there is no localization in request, disallow non localized
	 *    route and re-call localization preparing method and redirect if 
	 *    necessary. It means any request path will be redirected into default 
	 *    localization.
	 * 7. If any current route found and if route contains redirection, do it.
	 * 8. If there is no current route and request is targeting homepage, create
	 *    new empty route by default values if ctrl configuration allows it.
	 * 9. If there is any current route completed, complete self route name by 
	 *    it to generate `self` routes and canonical URL later.
	 * 10.If there is necessary, try to complete canonical URL and if canonical 
	 *    URL is shorter than requested URL, redirect user to shorter version.
	 * If there was necessary to redirect user in routing process, return 
	 * immediately `FALSE` and return from this method. Else continue to next 
	 * step and return `TRUE`. This method is always called from core routing by:
	 * `\MvcCore\Application::Run();` => `\MvcCore\Application::routeRequest();`.
	 * @throws \LogicException Route configuration property is missing.
	 * @throws \InvalidArgumentException Wrong route pattern format.
	 * @return bool
	 */
	public function Route ();

	/**
	 * Complete non-absolute, non-localized url by route instance reverse info.
	 * If there is key `media_version` in `$params`, unset this param before
	 * route url completing and choose by this param url prefix to prepend 
	 * completed url string.
	 * If there is key `localization` in `$params`, unset this param before
	 * route url completing and place this param as url prefix to prepend 
	 * completed url string and to prepend media site version prefix.
	 * Example:
	 *	Input (`\MvcCore\Route::$reverse`):
	 *		`"/products-list/<name>/<color>"`
	 *	Input ($params):
	 *		`array(
	 *			"name"			=> "cool-product-name",
	 *			"color"			=> "red",
	 *			"variant"		=> ["L", "XL"],
	 *			"localization"	=> "en-US",
	 *		);`
	 *	Output:
	 *		`/application/base-bath/en-US/products-list/cool-product-name/blue?variant[]=L&amp;variant[]=XL"`
	 * @param \MvcCore\Route &$route
	 * @param array $params
	 * @param string $urlParamRouteName
	 * @return string
	 */
	public function UrlByRoute (\MvcCore\IRoute $route, array & $params = [], $urlParamRouteName = NULL);
}
