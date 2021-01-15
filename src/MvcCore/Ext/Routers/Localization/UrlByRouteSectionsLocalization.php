<?php

/**
 * MvcCore
 *
 * This source file is subject to the BSD 3 License
 * For the full copyright and license information, please view
 * the LICENSE.md file that are distributed with this source code.
 *
 * @copyright	Copyright (c) 2016 Tom Flidr (https://github.com/mvccore)
 * @license		https://mvccore.github.io/docs/mvccore/5.0.0/LICENCE.md
 */

namespace MvcCore\Ext\Routers\Localization;

trait UrlByRouteSectionsLocalization {

	/**
	 * Return localization for result URL as localization param name string and 
	 * localization param value string. If localization is specified in given 
	 * params array, return this localization. If there is not any specific 
	 * localization in params array and route is defined as localized, add 
	 * localization from current request (which could be from session or from 
	 * request). Change params array and add special localization switch param 
	 * when router is configured to hold localization strictly in session. But 
	 * do not return any localization for not allowed route methods and do not 
	 * return any not allowed localizations.
	 * @param \MvcCore\Ext\Routers\Localizations\Route $route 
	 * @param array $params 
	 * @param string|NULL $routeMethod 
	 * @return array `[string $localizationParamName, string $localizationStr]`
	 */
	protected function urlByRouteSectionsLocalization (\MvcCore\IRoute $route, array & $params = [], $routeMethod = NULL) {
		// get `$localizationStr` from `$params` to work with the version more specifically
		// in route object to choose proper reverse pattern and to complete url prefix
		$localizedRoute = $route instanceof \MvcCore\Ext\Routers\Localizations\Route;
		$localizationParamName = static::URL_PARAM_LOCALIZATION;
		if (isset($params[$localizationParamName])) {
			$localizationStr = $params[$localizationParamName];
			//if (!$localizedRoute) unset($params[$localizationParamName]);
		} else {
			$localizationStr = implode(
				static::LANG_AND_LOCALE_SEPARATOR, $this->localization ?: $this->defaultLocalization
			);
			if ($localizedRoute) $params[$localizationParamName] = $localizationStr;
		}
		// check if localization value is valid
		if ($this->routeGetRequestsOnly && $routeMethod !== NULL && $routeMethod !== \MvcCore\IRequest::METHOD_GET) {
			$localizationStr = NULL;
		} else if (!isset($this->allowedLocalizations[$localizationStr])) {
			// check if localization value is valid
			if (isset($this->localizationEquivalents[$localizationStr])) 
				$localizationStr = $this->localizationEquivalents[$localizationStr];
			if (!isset($this->allowedLocalizations[$localizationStr])) {
				$localizationStr = NULL;
				trigger_error(
					'['.get_class().'] Not allowed localization used to generate url: `'
					.$localizationStr.'`. Allowed values: `'
					.implode('`, `', array_keys($this->allowedLocalizations)) . '`.',
					E_USER_ERROR
				);
			}
		}
		// add special switching param to global get, if strict 
		// session mode and target version is different
		if (
			$this->stricModeBySession && 
			$localizationStr !== implode(static::LANG_AND_LOCALE_SEPARATOR, $this->localization ?: $this->defaultLocalization)
		) 
			$params[static::URL_PARAM_SWITCH_LOCALIZATION] = $localizationStr;

		return [$localizationParamName, $localizationStr];
	}
}
