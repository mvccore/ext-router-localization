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
	 * Try to parse first word from request path to get proper routes group.
	 * If there is no first word in request path, get default routes group. 
	 * 
	 * If there is any configured pre-routing handler, execute the handler to
	 * for example load only specific routes from database or anything else.
	 * 
	 * Go through all chosen routes and check if route is possible to use for 
	 * current request. Then try to match route by given request. If route doesn't 
	 * match the request, continue to another route and try to complete current
	 * route object. If route matches the request, set up default and request 
	 * params and try to process route filtering in. If it is successful, set 
	 * up current route object and end route matching process.
	 * @param string|NULL $requestCtrlName		Possible controller name value or `NULL` assigned directly 
	 *											from request object in `\MvcCore\router::routeDetectStrategy();`
	 * @param string|NULL $requestActionName	Possible action name value or `NULL` assigned directly 
	 *											from request object in `\MvcCore\router::routeDetectStrategy();`
	 * @throws \LogicException Route configuration property is missing.
	 * @throws \InvalidArgumentException Wrong route pattern format.
	 * @return void
	 */
	protected function rewriteRouting ($requestCtrlName, $requestActionName) {
		$request = $this->request;

		$localizationInRequest = is_array($this->requestLocalization) && count($this->requestLocalization) > 0;
		$localization = $this->localization ?: $this->defaultLocalization;
		$localizationStr = implode(static::LANG_AND_LOCALE_SEPARATOR, $localization);
		$routesLocalizationStr = NULL;
		if (count($localization) > 0) 
			$routesLocalizationStr = $this->routeRecordsByLanguageAndLocale
				? $localizationStr
				: $localization[0];

		/** @var $route \MvcCore\Route */
		$requestMethod = $request->GetMethod();
		$requestedPathFirstWord = $this->rewriteRoutingGetReqPathFirstWord();
		$this->rewriteRoutingProcessPreHandler($requestedPathFirstWord);

		$routes = & $this->rewriteRoutingGetRoutesToMatch($requestedPathFirstWord, $routesLocalizationStr);
		$noSkipLocalRoutesForNonLocalRequests = !($this->routeGetRequestsOnly && $requestMethod !== \MvcCore\IRequest::METHOD_GET);
		
		foreach ($routes as $route) {
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

	/**
	 * Get specific routes group by first parsed word from request path if any.
	 * If first path word is an empty string, there is returned routes with no 
	 * group word defined. If still there are no such routes in default group, 
	 * returned is an empty array.
	 * @param string $firstPathWord 
	 * @param string|NULL $routesLocalizationStr 
	 * @return array|\MvcCore\IRoute[]|\MvcCore\Ext\Routers\Localizations\Route[]
	 */
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
