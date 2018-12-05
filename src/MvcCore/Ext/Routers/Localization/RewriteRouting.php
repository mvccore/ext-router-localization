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

trait RewriteRouting
{
	/**
	 * Complete `\MvcCore\Router::$currentRoute` and request params by defined routes.
	 * Go through all configured routes and try to find matching route.
	 * If there is caught any matching route - reset `\MvcCore\Request::$params`
	 * with default route params, with params itself and with params parsed from 
	 * matching process.
	 * @param string $controllerName
	 * @param string $actionName
	 * @return void
	 */
	protected function rewriteRouting ($requestCtrlName, $requestActionName) {
		$request = & $this->request;

		$localizationInRequest = is_array($this->requestLocalization) && count($this->requestLocalization) > 0;
		$localizationStr = implode(static::LANG_AND_LOCALE_SEPARATOR, $this->localization);
		$routesLocalizationStr = NULL;
		if (count($this->localization) > 0) {
			$routesLocalizationStr = $this->routeRecordsByLanguageAndLocale
				? $localizationStr
				: $this->localization[0];
		}

		/** @var $route \MvcCore\Route */
		$requestMethod = $request->GetMethod();
		$requestedPathFirstWord = $this->rewriteRoutingGetReqPathFirstWord();
		$this->rewriteRoutingProcessPreHandler($requestedPathFirstWord);

		$routes = & $this->rewriteRoutingGetRoutesToMatch($requestedPathFirstWord, $routesLocalizationStr);
		$noSkipLocalRoutesForNonLocalRequests = !($this->routeGetRequestsOnly && $requestMethod !== \MvcCore\IRequest::METHOD_GET);

		foreach ($routes as & $route) {
			$routeIsLocalized = $route instanceof \MvcCore\Ext\Routers\Localizations\Route;
			
			if ($this->rewriteRoutingCheckRoute($route, [
				$requestMethod, $localizationInRequest, $routeIsLocalized, $noSkipLocalRoutesForNonLocalRequests
			])) continue;

			if ($routeIsLocalized) {
				$allMatchedParams = $route->Matches($request, $routesLocalizationStr);
			} else {
				$allMatchedParams = $route->Matches($request);
			}

			if ($allMatchedParams !== NULL) {
				$this->currentRoute = clone $route;
				$this->currentRoute->SetMatchedParams($allMatchedParams);
				
				$localizationUrlParamName = static::URL_PARAM_LOCALIZATION;
				
				$this->rewriteRoutingSetRequestedAndDefaultParams(
					$allMatchedParams, $requestCtrlName, $requestActionName
				);

				$this->defaultParams[$localizationUrlParamName] = $localizationStr;
				$localizationContained = isset($requestParams[$localizationUrlParamName]);
				$requestParams[$localizationUrlParamName] = $localizationStr;
				
				if ($this->rewriteRoutingSetRequestParams($allMatchedParams)) continue;

				if (!$localizationContained) 
					$this->request->RemoveParam($localizationUrlParamName);
				
				$this->rewriteRoutingSetUpCurrentRouteByRequest();
				break;
			}
		}
	}

	protected function & rewriteRoutingGetRoutesToMatch ($firstPathWord, $routesLocalizationStr = NULL) {
		$routesGroupsKey = $firstPathWord;
		if ($routesLocalizationStr !== NULL) 
			$routesGroupsKey = $routesLocalizationStr . '/' . $firstPathWord;
		if (array_key_exists($routesGroupsKey, $this->routesGroups)) {
			$routes = & $this->routesGroups[$routesGroupsKey];
		} else {
			$routes = & $this->routesGroups[''];
		}
		reset($routes);
		return $routes;
	}
}
