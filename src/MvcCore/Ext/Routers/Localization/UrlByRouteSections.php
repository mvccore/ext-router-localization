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
	 * TODO: neaktuální
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
	 * @return array `string $urlBaseSection, string $urlPathWithQuerySection, array $systemParams`
	 */
	protected function urlByRouteSections (\MvcCore\IRoute & $route, array & $params = [], $urlParamRouteName = NULL) {
		/** @var $route \MvcCore\Route */
		$defaultParams = array_merge([], $this->GetDefaultParams() ?: []);
		if ($urlParamRouteName == 'self') 
			$params = array_merge($this->requestedParams, $params);
		$routeMethod = $route->GetMethod();


		$multipleLocalizationsConfigured = count($this->allowedLocalizations) > 1;
		$localizationParamName = $localizationStr = NULL;
		if ($multipleLocalizationsConfigured) 
			list($localizationParamName, $localizationStr) = $this->urlByRouteSectionsLocalization(
				$route, $params, $routeMethod
			);


		// complete by given route base url address part and part with path and query string
		list($urlBaseSection, $urlPathWithQuerySection) = $route->Url(
			$this->request, $params, $defaultParams, $this->getQueryStringParamsSepatator(), TRUE
		);


		$systemParams = [];
		if ($multipleLocalizationsConfigured && $localizationStr !== NULL) 
			$systemParams[$localizationParamName] = $localizationStr;


		return [
			$urlBaseSection, 
			$urlPathWithQuerySection, 
			$systemParams
		];
	}
}
