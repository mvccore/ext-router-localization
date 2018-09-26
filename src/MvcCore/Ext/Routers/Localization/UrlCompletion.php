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

trait UrlCompletion
{
	/**
	 * Complete non-absolute, non-localized url by route instance reverse info.
	 * Example:
	 *	Input (`\MvcCore\Route::$reverse`):
	 *		`"/products-list/<name>/<color>"`
	 *	Input ($params):
	 *		`array(
	 *			"name"		=> "cool-product-name",
	 *			"color"		=> "red",
	 *			"variant"	=> array("L", "XL"),
	 *		);`
	 *	Output:
	 *		`/application/base-bath/products-list/cool-product-name/blue?variant[]=L&amp;variant[]=XL"`
	 * @param \MvcCore\Route &$route
	 * @param array $params
	 * @return string
	 */
	public function UrlByRoute (\MvcCore\Interfaces\IRoute & $route, & $params = []) {
		$requestedUrlParams = & $this->GetRequestedUrlParams();
		$localizationParamName = (string) static::LOCALIZATION_URL_PARAM;
		$localizedRoute = $route instanceof \MvcCore\Ext\Routers\Localizations\Route;
		$localization = (
			isset($params[$localizationParamName])
				? $params[$localizationParamName]
				: (isset($requestedUrlParams[$localizationParamName])
					? $requestedUrlParams[$localizationParamName]
					: $this->defaultLocalizationStr)
		);
		unset($params[$localizationParamName]);
		$result = $route->Url(
			$params, $requestedUrlParams, $this->getQueryStringParamsSepatator()
		);
		if ($localizedRoute) 
			$result = '/' . $localization . $result;
		$questionMarkPos = mb_strpos($result, '?');
		$anyQueryString = $questionMarkPos !== FALSE;
		$resultPath = $anyQueryString 
			? mb_substr($result, 0, $questionMarkPos)
			: $result;
		if (trim($resultPath, '/') === $this->defaultLocalizationStr) 
			$result = '/' . ($anyQueryString ? mb_substr($result, $questionMarkPos) : '');
		return $this->request->GetBasePath() . $result;
	}

	/**
	 * Get all request params - params parsed by route and query string params.
	 * Be carefull, it could contain XSS chars. Use always `htmlspecialchars()`.
	 * @return array
	 */
	public function & GetRequestedUrlParams () {
		if ($this->requestedUrlParams === NULL) {
			// create global `$_GET` array clone:
			$this->requestedUrlParams = array_merge([], $this->request->GetGlobalCollection('get'));
			$requestLocalization = [$this->request->GetLang()];
			$requestLocale = $this->request->GetLocale();
			if ($requestLocale !== NULL)
				$requestLocalization[] = $requestLocale;
			$this->requestedUrlParams[static::LOCALIZATION_URL_PARAM] = implode(
				static::LANG_AND_LOCALE_SEPARATOR, $requestLocalization
			);
		}
		return $this->requestedUrlParams;
	}
}
