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

namespace MvcCore\Ext\Routers\Localization;

trait UrlByRouteSections
{
	/**
	 * Complete non-absolute, non-localized or localized url by route instance reverse info.
	 * If there is key `localization` in `$params`, unset this param before
	 * route url completing and place this param as url prefix to prepend 
	 * completed url string.
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
	 * @param \MvcCore\Route|\MvcCore\IRoute &$route
	 * @param array $params
	 * @param string $urlParamRouteName
	 * @return string
	 */
	protected function urlByRouteSections (\MvcCore\IRoute & $route, array & $params = [], $urlParamRouteName = NULL) {
		/** @var $route \MvcCore\Route */
		$defaultParams = array_merge([], $this->GetDefaultParams() ?: []);
		if ($urlParamRouteName == 'self') 
			$params = array_merge($this->requestedParams, $params);

		// get `$localizationStr` from `$params` to work with the version more specificly
		// in route object to choose proper reverse pattern and to complete url prefix
		$localizationParamName = static::URL_PARAM_LOCALIZATION;
		$localizedRoute = $route instanceof \MvcCore\Ext\Routers\Localizations\Route;
		if (isset($params[$localizationParamName])) {
			$localizationStr = $params[$localizationParamName];
			//if (!$localizedRoute) unset($params[$localizationParamName]);
		} else {
			$localizationStr = implode(
				static::LANG_AND_LOCALE_SEPARATOR, $this->localization
			);
			if ($localizedRoute) $params[$localizationParamName] = $localizationStr;
		}
		// check if localization value is valid
		if (!isset($this->allowedLocalizations[$localizationStr])) {
			if (isset($this->localizationEquivalents[$localizationStr])) 
				$localizationStr = $this->localizationEquivalents[$localizationStr];
			if (!isset($this->allowedLocalizations[$localizationStr])) {
				$localizationStr = '';
				trigger_error(
					'['.__CLASS__.'] Not allowed localization used to generate url: `'
					.$localizationStr.'`. Allowed values: `'
					.implode('`, `', array_keys($this->allowedLocalizations)) . '`.',
					E_USER_ERROR
				);
			}
		}
		// add special switching param to global get, if strict session mode and target version is different
		if (
			$this->stricModeBySession && 
			$localizationStr !== implode(static::LANG_AND_LOCALE_SEPARATOR, $this->localization)
		) 
			$params[static::URL_PARAM_SWITCH_LOCALIZATION] = $localizationStr;
		
		// complete by given route base url address part and part with path and query string
		list($resultBase, $resultPathWithQuery) = $route->Url(
			$this->request, $params, $defaultParams, $this->getQueryStringParamsSepatator()
		);

		// create localization prefix for all localized routes
		// and for all url addresses except default language homepage
		$localizationUrlPrefix = '';
		$questionMarkPos = mb_strpos($resultPathWithQuery, '?');
		$resultPath = $questionMarkPos !== FALSE 
			? mb_substr($resultPathWithQuery, 0, $questionMarkPos)
			: $resultPathWithQuery;
		$resultPathTrimmed = trim($resultPath, '/');
		if ($localizedRoute && !(
			$resultPathTrimmed === '' && 
			$localizationStr === $this->defaultLocalizationStr
		)) $localizationUrlPrefix = '/' . $localizationStr;

		// check route method and do not create any prefixes for POST (non GET) routes,
		// if there is not allowed to route POST (non GET) requests
		if ($this->routeGetRequestsOnly) {
			$routeMethod = $route->GetMethod();
			if ($routeMethod !== NULL && $routeMethod !== \MvcCore\IRequest::METHOD_GET) 
				$localizationUrlPrefix = '';
		}
		
		// finalizing possible trailing slash after prefix
		if (
			$resultPathTrimmed === '' &&
			$this->trailingSlashBehaviour === \MvcCore\IRouter::TRAILING_SLASH_REMOVE &&
			$localizationUrlPrefix !== ''
		) $resultPathWithQuery = ltrim($resultPathWithQuery, '/');
		
		return [$resultBase, $localizationUrlPrefix, $resultPathWithQuery];
	}
}
