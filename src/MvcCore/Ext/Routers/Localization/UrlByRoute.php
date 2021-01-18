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

namespace MvcCore\Ext\Routers\Localization;

trait UrlByRoute {

	/**
	 * Complete non-absolute, localized or non-localized URL by route instance 
	 * reverse info. If there is key `localization` in `$params`, unset this 
	 * param before route URL completing and place this param as URL prefix to 
	 * prepend completed URL string and to prepend media site version prefix.
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
	public function UrlByRoute (\MvcCore\IRoute $route, array & $params = [], $urlParamRouteName = NULL) {
		// get domain with base path url section, 
		// path with query string url section 
		// and system params for url prefixes
		list($urlBaseSection, $urlPathWithQuerySection, $systemParams) = $this->urlByRouteSections(
			$route, $params, $urlParamRouteName
		);

		// remove localization prefix for non localized routes or
		// remove localization prefix if url targets top homepage `/` on default language version
		$localizedRoute = $route instanceof \MvcCore\Ext\Routers\Localizations\Route;
		$localizationParamName = static::URL_PARAM_LOCALIZATION;
		$urlPathWithQueryIsHome = NULL;
		if (isset($systemParams[$localizationParamName])) {
			if (!$localizedRoute) {
				unset($systemParams[$localizationParamName]);
			} else if ($systemParams[$localizationParamName] === $this->defaultLocalizationStr) {
				// Get `TRUE` if path with query string target homepage - `/` (or `/index.php` - request script name)
				$urlPathWithQueryIsHome = $this->urlIsHomePath($urlPathWithQuerySection);
				if ($urlPathWithQueryIsHome)
					unset($systemParams[$localizationParamName]);
			}
		}
		
		// create prefixed url
		return $this->urlByRoutePrefixSystemParams(
			$urlBaseSection, $urlPathWithQuerySection, $systemParams, $urlPathWithQueryIsHome
		);
	}
}
