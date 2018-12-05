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

trait UrlByRouteSectionsLocalization
{
	protected function urlByRouteSectionsLocalization (\MvcCore\IRoute & $route, array & $params = [], $routeMethod = NULL) {
		// get `$localizationStr` from `$params` to work with the version more specifically
		// in route object to choose proper reverse pattern and to complete url prefix
		$localizedRoute = $route instanceof \MvcCore\Ext\Routers\Localizations\Route;
		$localizationParamName = static::URL_PARAM_LOCALIZATION;
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
		if ($this->routeGetRequestsOnly && $routeMethod !== NULL && $routeMethod !== \MvcCore\IRequest::METHOD_GET) {
			$localizationStr = NULL;
		} else if (!isset($this->allowedLocalizations[$localizationStr])) {
			// check if localization value is valid
			if (isset($this->localizationEquivalents[$localizationStr])) 
				$localizationStr = $this->localizationEquivalents[$localizationStr];
			if (!isset($this->allowedLocalizations[$localizationStr])) {
				$localizationStr = NULL;
				$selfClass = version_compare(PHP_VERSION, '5.5', '>') ? self::class : __CLASS__;
				trigger_error(
					'['.$selfClass.'] Not allowed localization used to generate url: `'
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

		return [$localizationParamName, $localizationStr];
	}
}
