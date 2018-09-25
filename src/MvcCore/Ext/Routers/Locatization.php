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

namespace MvcCore\Ext\Routers;

class		Locatization 
extends		\MvcCore\Router
implements	\MvcCore\Ext\Routers\ILocalization,
			\MvcCore\Ext\Routers\IExtended
{
	use \MvcCore\Ext\Routers\Extended;
	use \MvcCore\Ext\Routers\Localization\PropsGettersSetters;
	use \MvcCore\Ext\Routers\Localization\Routing;
	use \MvcCore\Ext\Routers\Localization\UrlCompletion;
	
	/**
	 * Route current application request by configured routes list or by query string data.
	 * - If there is strictly defined `controller` and `action` value in query string,
	 *   route request by given values, add new route and complete new empty
	 *   `\MvcCore\Router::$currentRoute` route with `controller` and `action` values from query string.
	 * - If there is no strictly defined `controller` and `action` value in query string,
	 *   go throught all configured routes and try to find matching route:
	 *   - If there is catched any matching route:
	 *	 - Set up `\MvcCore\Router::$currentRoute`.
	 *	 - Reset `\MvcCore\Request::$params` again with with default route params,
	 *	   with request params itself and with params parsed from matching process.
	 * - If there is no route matching the request and also if the request is targeting homepage
	 *   or there is no route matching the request and also if the request is targeting something
	 *   else and also router is configured to route to default controller and action if no route
	 *   founded, complete `\MvcCore\Router::$currentRoute` with new empty automaticly created route
	 *   targeting default controller and action by configuration in application instance (`Index:Index`)
	 *   and route type create by configured `\MvcCore\Application::$routeClass` class name.
	 * - Return completed `\MvcCore\Router::$currentRoute` or `FALSE` for redirection or `NULL` for not matched.
	 *
	 * This method is always called from core routing by:
	 * - `\MvcCore\Application::Run();` => `\MvcCore\Application::routeRequest();`.
	 * @return \MvcCore\Route|bool|NULL
	 */
	public function & Route () {
		$result = FALSE;
		if (!$this->redirectToProperTrailingSlashIfNecessary()) return $result;
		$request = & $this->request;
		$requestCtrlName = $request->GetControllerName();
		$requestActionName = $request->GetActionName();
		$this->anyRoutesConfigured = count($this->routes) > 0;
		if ($requestCtrlName && $requestActionName) {
			if ($this->preRouteLocalization() === FALSE) return $result;
			$this->routeByControllerAndActionQueryString(
				$requestCtrlName, $requestActionName
			);
		} else {
			if ($this->preRouteLocalization() === FALSE) return $result;
			$this->routeByRewriteRoutes($requestCtrlName, $requestActionName);
			if ($this->currentRoute === NULL && !$this->requestLocatization) {
				$this->allowNonLocalizedRoutes = FALSE;
				if (!$this->checkLocalizationWithUrlAndRedirectIfNecessary()) 
					return $result;
			}
		}
		if ($this->currentRoute === NULL && (
			($request->GetPath() == '/' || $request->GetPath() == $request->GetScriptName()) ||
			$this->routeToDefaultIfNotMatch
		)) {
			list($dfltCtrl, $dftlAction) = $this->application->GetDefaultControllerAndActionNames();
			$this->SetOrCreateDefaultRouteAsCurrent(
				\MvcCore\Interfaces\IRouter::DEFAULT_ROUTE_NAME, $dfltCtrl, $dftlAction
			);
		}
		return $this->currentRoute;
	}
}
