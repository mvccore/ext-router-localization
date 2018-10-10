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
	 * @param string $givenRouteName
	 * @return string
	 */
	public function UrlByRoute (\MvcCore\IRoute & $route, array & $params = [], $givenRouteName = NULL) {
		/** @var $route \MvcCore\Route */
		$defaultParams = array_merge([], $this->GetDefaultParams() ?: []);
		if ($givenRouteName == 'self') 
			$params = array_merge($this->requestedParams, $params);

		$localizationParamName = static::LOCALIZATION_URL_PARAM;
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

		if (
			$this->stricModeBySession && 
			$localizationStr !== implode(static::LANG_AND_LOCALE_SEPARATOR, $this->localization)
		) 
			$params[static::SWITCH_LOCALIZATION_URL_PARAM] = $localizationStr;
		
		list($resultBase, $resultPathWithQuery) = $route->Url(
			$this->request, $params, $defaultParams, $this->getQueryStringParamsSepatator()
		);

		$localizationUrlPrefix = '';
		$questionMarkPos = mb_strpos($resultPathWithQuery, '?');
		$resultPath = $questionMarkPos !== FALSE 
			? mb_substr($resultPathWithQuery, 0, $questionMarkPos)
			: $resultPathWithQuery;
		$resultPathTrimmed = trim($resultPath, '/');
		if (
			$localizedRoute && !(
				$resultPathTrimmed === '' && 
				$localizationStr === $this->defaultLocalizationStr
			)
		) 
			$localizationUrlPrefix = '/' . $localizationStr;
		if ($this->routeGetRequestsOnly) {
			$routeMethod = $route->GetMethod();
			if ($routeMethod !== NULL && $routeMethod !== \MvcCore\IRequest::METHOD_GET) 
				$localizationUrlPrefix = '';
		}

		if (
			$resultPathTrimmed === '' &&
			$this->trailingSlashBehaviour === \MvcCore\IRouter::TRAILING_SLASH_REMOVE &&
			$localizationUrlPrefix !== ''
		) $resultPathWithQuery = ltrim($resultPathWithQuery, '/');
		
		return $resultBase
			. $localizationUrlPrefix
			. $resultPathWithQuery;
	}
}
