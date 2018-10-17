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

trait RoutingByRoutes
{
	/**
	 * Complete `\MvcCore\Router::$currentRoute` and request params by defined routes.
	 * Go throught all configured routes and try to find matching route.
	 * If there is catched any matching route - reset `\MvcCore\Request::$params`
	 * with default route params, with params itself and with params parsed from 
	 * matching process.
	 * @param string $controllerName
	 * @param string $actionName
	 * @return void
	 */
	protected function routeByRewriteRoutes ($requestCtrlName, $requestActionName) {
		$request = & $this->request;
		$localizationInRequest = is_array($this->requestLocalization) && count($this->requestLocalization) > 0;
		$localizationStr = implode(static::LANG_AND_LOCALE_SEPARATOR, $this->localization);
		if ($this->routeRecordsByLanguageAndLocale) {
			$routesLocalizationStr = $localizationStr;
		} else if (count($this->localization) > 0) {
			$routesLocalizationStr = $this->localization[0];
		} else {
			$routesLocalizationStr = NULL;
		}
		/** @var $route \MvcCore\Route */
		reset($this->routes);
		$allMatchedParams = [];
		$requestMethod = $request->GetMethod();
		$localizationRoutesSkipping = !($this->routeGetRequestsOnly && $requestMethod !== \MvcCore\IRequest::METHOD_GET);
		foreach ($this->routes as & $route) {
			$routeMethod = $route->GetMethod();
			if ($routeMethod !== NULL && $routeMethod !== $requestMethod) continue;
			// skip non localized routes by configuration
			$routeIsLocalized = $route instanceof \MvcCore\Ext\Routers\Localizations\Route;
			// skip localized routes matching when request has no localization in path
			if (!$localizationInRequest && $routeIsLocalized) {
				// but do not skip localized routes matching when request has no localization in path and:
				// - when method is post and router has not allowed to process other methods than GET
				// - or when method is anything and router has allowed to process other methods than GET
				if ($localizationRoutesSkipping) continue;
			}
			if (!$this->allowNonLocalizedRoutes && !$routeIsLocalized) continue;
			if ($routeIsLocalized) {
				$allMatchedParams = $route->Matches($request, $routesLocalizationStr);
			} else {
				$allMatchedParams = $route->Matches($request);
			}
			if ($allMatchedParams) {
				$this->currentRoute = clone $route;
				$this->currentRoute->SetMatchedParams($allMatchedParams);
				$localizationUrlParamName = static::URL_PARAM_LOCALIZATION;
				$requestParams = $this->routeByRRSetRequestedAndDefaultParams(
					$allMatchedParams
				);
				$this->defaultParams[$localizationUrlParamName] = $localizationStr;
				$localizationContained = isset($requestParams[$localizationUrlParamName]);
				$requestParams[$localizationUrlParamName] = $localizationStr;
				$break = $this->routeByRRSetRequestParams($allMatchedParams, $requestParams);
				if (!$localizationContained) 
					$this->request->RemoveParam($localizationUrlParamName);
				if ($break) break;
			}
		}
		if ($this->currentRoute !== NULL) 
			$this->routeByRRSetUpRequestByCurrentRoute(
				$allMatchedParams['controller'], $allMatchedParams['action']
			);
	}
}
