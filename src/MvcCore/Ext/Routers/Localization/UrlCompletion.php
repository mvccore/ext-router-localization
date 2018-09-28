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
	public function UrlByRoute (\MvcCore\IRoute & $route, & $params = []) {
		$requestedUrlParams = $this->GetRequestedUrlParams();
		$localizedRoute = $route instanceof \MvcCore\Ext\Routers\Localizations\Route;
		
		$localizationParamName = static::LOCALIZATION_URL_PARAM;
		
		if (isset($params[$localizationParamName])) {
			$localizationStr = $params[$localizationParamName];
			if ($localizedRoute) unset($params[$localizationParamName]);
		} else if (isset($requestedUrlParams[$localizationParamName])) {
			$localizationStr = $requestedUrlParams[$localizationParamName];
			if ($localizedRoute) unset($requestedUrlParams[$localizationParamName]);
		} else {
			$localizationStr = implode(
				static::LANG_AND_LOCALE_SEPARATOR, $this->localization
			);
		}
		if (!isset($this->allowedLocalizations[$localizationStr])) {
			if (isset($this->localizationEquivalents[$localizationStr])) 
				$localizationStr = $this->localizationEquivalents[$localizationStr];
			if (!isset($this->allowedLocalizations[$localizationStr]))
				throw new \InvalidArgumentException(
					'['.__CLASS__.'] Not allowed localization used to generate url: `'
					.$localizationStr.'`. Allowed values: `'
					.implode('`, `', array_keys($this->allowedLocalizations)) . '`.'
				);
		}

		if (
			$this->stricModeBySession && 
			$localizationStr !== implode(static::LANG_AND_LOCALE_SEPARATOR, $this->localization)
		) 
			$params[static::SWITCH_LOCALIZATION_URL_PARAM] = $localizationStr;
		
		$result = $route->Url(
			$params, $requestedUrlParams, $this->getQueryStringParamsSepatator()
		);

		$localizationUrlPrefix = '';
		$questionMarkPos = mb_strpos($result, '?');
		$resultPath = $questionMarkPos !== FALSE 
			? mb_substr($result, 0, $questionMarkPos)
			: $result;
		if (
			$localizedRoute && !(
				trim($resultPath, '/') === '' && 
				$localizationStr === $this->defaultLocalizationStr
			)
		) 
			$localizationUrlPrefix = '/' . $localizationStr;
		
		return $this->request->GetBasePath() 
			. $localizationUrlPrefix
			. $result;
	}
}
