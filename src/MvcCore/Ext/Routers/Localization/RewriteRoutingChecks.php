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

trait RewriteRoutingChecks
{
	/**
	 * Return `TRUE` if there is possible (or not) by additional info array 
	 * records to route incoming request by given route as first argument. 
	 * Check, if route object has defined any http method and if the request has 
	 * the same method or not and check if route object is localized route 
	 * instance and if there is also any localization found in request. If there 
	 * is a conflict, return `FALSE`, if there is everything OK, return `TRUE`.
	 * @param \MvcCore\IRoute $route 
	 * @param array $additionalInfo 
	 *				Array with request method as string, localization found in 
	 *				request as bool, route is localized as bool and boolean 
	 *				about not skip localized routes matching when request has no 
	 *				localization in path and other conditions described inside 
	 *				this function.
	 * @return bool
	 */
	protected function rewriteRoutingCheckRoute (\MvcCore\IRoute & $route, array $additionalInfo) {
		list ($requestMethod, $localizationInRequest, $routeIsLocalized, $noSkipLocalRoutesForNonLocalRequests) = $additionalInfo;

		$routeMethod = $route->GetMethod();
		if ($routeMethod !== NULL && $routeMethod !== $requestMethod) return TRUE;

		// skip localized routes matching when request has no localization in path
		if ($routeIsLocalized && !$localizationInRequest) {
			// but do not skip localized routes matching when request has no localization in path and:
			// - when method is post and router has not allowed to process other methods than GET
			// - or when method is anything and router has allowed to process other methods than GET
			if ($noSkipLocalRoutesForNonLocalRequests) return TRUE;
		}

		if (!$routeIsLocalized && !$this->allowNonLocalizedRoutes) return TRUE;

		return FALSE;
	}
}
