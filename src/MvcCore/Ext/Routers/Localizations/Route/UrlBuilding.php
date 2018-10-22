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

namespace MvcCore\Ext\Routers\Localizations\Route;

trait UrlBuilding
{
	/**
	 * Complete route url by given params array and route
	 * internal reverse replacements pattern string.
	 * If there are more given params in first argument
	 * than count of replacement places in reverse pattern,
	 * then create url with query string params after reverse
	 * pattern, containing that extra record(s) value(s).
	 *
	 * Example:
	 *	Input (`$params`):
	 *		`array(
	 *			"name"		=> "cool-product-name",
	 *			"color"		=> "blue",
	 *			"variants"	=> array("L", "XL"),
	 *		);`
	 *	Input (`\MvcCore\Route::$reverse`):
	 *		`"/products-list/<name>/<color*>"`
	 *	Output:
	 *		`"/products-list/cool-product-name/blue?variant[]=L&amp;variant[]=XL"`
	 * @param \MvcCore\Request $request Currently requested request object.
	 * @param array $params URL params from application point completed by developer.
	 * @param array $defaultUrlParams Requested url route prams nad query string params without escaped HTML special chars: `< > & " ' &`.
	 * @param string $queryStringParamsSepatator Query params separator, `&` by default. Always automaticly completed by router instance.
	 * @return \string[] Result URL addres in two parts - domain part with base path and path part with query string.
	 */
	public function Url (\MvcCore\IRequest & $request, array & $params = [], array & $defaultUrlParams = [], $queryStringParamsSepatator = '&') {
		// initialize localization param and route localization key
		$router = & $this->router;
		$localizationParamName = $router::URL_PARAM_LOCALIZATION;
		if (isset($params[$localizationParamName])) {
			$localizationStr = $params[$localizationParamName];
			unset($params[$localizationParamName]);
		} else if (isset($defaultUrlParams[$localizationParamName])) {
			$localizationStr = $defaultUrlParams[$localizationParamName];
			unset($defaultUrlParams[$localizationParamName]);
		} else {
			$localizationStr = implode($router::LANG_AND_LOCALE_SEPARATOR, $router->GetLocalization());
		}
		$localization = explode($router::LANG_AND_LOCALE_SEPARATOR, $localizationStr);
		$routesLocalization = $router->GetRouteRecordsByLanguageAndLocale()
			? $localizationStr
			: $localization[0];

		// check reverse initialization
		if (!array_key_exists($routesLocalization, $this->reverseParamsLocalized) || !array_key_exists($routesLocalization, $this->reverseLocalized)) 
			$this->initReverse($routesLocalization);
		
		// complete and filter all params to build reverse pattern
		$reverseParams = $this->reverseParamsLocalized[$routesLocalization];
		if (count($reverseParams) === 0) {
			$allParamsClone = array_merge([], $params);
		} else {
			$emptyReverseParams = array_fill_keys(array_keys($reverseParams), '');
			$allMergedParams = array_merge($this->GetDefaults($routesLocalization), $defaultUrlParams, $params);
			$allParamsClone = array_merge($emptyReverseParams, array_intersect_key($allMergedParams, $emptyReverseParams), $params);
		}

		// filter params
		$localizationContained = array_key_exists($localizationParamName, $allParamsClone);
		$allParamsClone[$localizationParamName] = $localizationStr;
		list(,$filteredParams) = $this->Filter($allParamsClone, $defaultUrlParams, \MvcCore\IRoute::CONFIG_FILTER_OUT);
		if (!$localizationContained) unset($filteredParams[$localizationParamName]);
		
		// split params into domain params array and into path and query params array
		$domainParams = $this->urlGetAndRemoveDomainParams($filteredParams);

		// build reverse pattern
		$result = $this->urlComposeByReverseSectionsAndParams(
			$this->reverseLocalized[$routesLocalization], 
			$this->reverseSectionsLocalized[$routesLocalization], 
			$this->reverseParamsLocalized[$routesLocalization], 
			$filteredParams, 
			$this->GetDefaults($routesLocalization)
		);

		// add all remaining params to query string
		if ($filteredParams) {
			// `http_build_query()` automaticly converts all XSS chars to entities (`< > & " ' &`):
			$result .= (mb_strpos($result, '?') !== FALSE ? $queryStringParamsSepatator : '?')
				. str_replace('%2F', '/', http_build_query($filteredParams, '', $queryStringParamsSepatator, PHP_QUERY_RFC3986));
		}

		return $this->urlSplitResultToBaseAndPathWithQuery($request, $result, $domainParams);
	}
}
