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

trait Routing
{
	/**
	 * Detect localization by configured rules,
	 * set up detected localization to current context,
	 * into request and into session and redirect if necessary.
	 * Return always `TRUE` and return `FALSE` if request is redirected.
	 * @return bool
	 */
	protected function preRouteLocalization () {
		if (
			(($this->isGet && $this->routeGetRequestsOnly) || !$this->routeGetRequestsOnly) &&
			$this->switchUriParamLocalization !== NULL
		) {
			// if there is detected in requested url media site version switching param,
			// store switching param value in session, remove param from `$_GET` 
			// and redirect to the same page with new media site version:
			if (!$this->manageLocalizationSwitchingAndRedirect()) return FALSE;

		} else if (
			(($this->isGet && $this->routeGetRequestsOnly) || !$this->routeGetRequestsOnly) && 
			$this->sessionLocalization === NULL
		) {
			// if there is no session record about media site version:
			$this->manageLocalizationDetectionAndStoreInSession();
			// check if media site version is the same as local media site version:
			if (!$this->checkLocalizationWithUrlAndRedirectIfNecessary()) return FALSE;

		} else {
			// if there is media site version in session already:
			$this->localization = $this->sessionLocalization;
			// check if media site version is the same as local media site version:
			if (!$this->checkLocalizationWithUrlAndRedirectIfNecessary()) return FALSE;
		}

		// set up stored/detected localization into request:
		if ($this->localization) {
			$this->request->SetLang($this->localization[0]);
			if (count($this->localization) > 1) 
				$this->request->SetLocale($this->localization[1]);
		}

		// return `TRUE` or `FALSE` to break or not the routing process
		return TRUE;
	}

	/**
	 * Prepare localizations processing:
	 * - Check if any default localization configured.
	 * - Complete allowed localizations into array keyed by it's values to check by `isset()`.
	 * - Complete default localization string for comparations later.
	 * - Put default localization into allowed localizations for sure.
	 * - Complete localization equivalents with language codes only by router configuration.
	 * - Try to complete switching param from request object global `$_GET` collection.
	 * - Try to complete session language and locale.
	 * - Store request path before next request localization detection request path manipulation.
	 * - Try to complete request language and locale from request query string or path.
	 * @return bool
	 */
	protected function preRoutePrepareLocalization () {
		// check all necessary properties configured
		if (!$this->defaultLocalization)
			throw new \RuntimeException("[".__CLASS__."] No default localization configured.");

		// prepare possibly modified allowed localizations
		$this->allowedLocalizations = array_combine($this->allowedLocalizations, $this->allowedLocalizations);
		// add default localization into allowed langs for sure
		$this->defaultLocalizationStr = implode(static::LANG_AND_LOCALE_SEPARATOR, $this->defaultLocalization);
		$this->allowedLocalizations[$this->defaultLocalizationStr] = $this->defaultLocalizationStr;

		// add automaticly into equivalents also all langs parsed from localizations if necessary
		if ($this->detectLocalizationOnlyByLang) {
			foreach ($this->allowedLocalizations as $allowedLocalization => $allowedLocalizationValue) {
				$separatorPos = strpos($allowedLocalization, static::LANG_AND_LOCALE_SEPARATOR);
				if ($separatorPos !== FALSE) {
					$lang = substr($allowedLocalization, 0, $separatorPos);
					if (!isset($this->localizationEquivalents[$lang]))
						$this->localizationEquivalents[$lang] = $allowedLocalization;
				}
			}
		}
		
		// check it with any strict session configuration to have more flexible navigations
		//if ($this->stricModeBySession) {
			$sessStrictModeSwitchUrlParam = static::SWITCH_LOCALIZATION_URL_PARAM;
			if (isset($this->requestGlobalGet[$sessStrictModeSwitchUrlParam])) {
				$globalGetValue = strtolower($this->requestGlobalGet[$sessStrictModeSwitchUrlParam]);
				$separatorPos = strpos($globalGetValue, static::LANG_AND_LOCALE_SEPARATOR);
				if ($separatorPos !== FALSE)
					$globalGetValue = substr($globalGetValue, 0, $separatorPos + 1)
						. strtoupper(substr($globalGetValue, $separatorPos + 1));
				if (isset($this->allowedLocalizations[$globalGetValue]))
					$this->switchUriParamLocalization = $globalGetValue;
			}
		//}
		
		// look into session object if there are or not
		// any record about lang from previous request
		if (isset($this->session->{static::LOCALIZATION_URL_PARAM})) 
			$this->sessionLocalization = $this->session->{static::LOCALIZATION_URL_PARAM};
		
		// store path info localy for routing process
		$this->originalRequestPath = $this->request->GetPath();

		// get current localization from requested url string
		return $this->setUpRequestLocalizationFromUrl();
	}

	/**
	 * Try to set up lang (or lang and locale) from request query string and if 
	 * there i no localization query param, try to set up localization from
	 * request path if any routes defined. If there ss any lang detected (or 
	 * lang and locale detected), set up these values into request object.
	 * @return bool
	 */
	protected function setUpRequestLocalizationFromUrl () {
		if ($this->setUpRequestLocalizationFromUrlQueryString() === FALSE) 
			return FALSE;
		if ($this->requestLocalization === NULL && $this->anyRoutesConfigured) 
			return $this->setUpRequestLocalizationFromUrlPath();
		return TRUE;
	}

	/**
	 * Try to set up lang (or lang and locale) from request query string.
	 * @return bool
	 */
	protected function setUpRequestLocalizationFromUrlQueryString () {
		$this->requestLocalization = NULL;
		$localizationUrlParam = static::LOCALIZATION_URL_PARAM;
		$langAndLocaleSeparator = static::LANG_AND_LOCALE_SEPARATOR;
		// try ty set up request localization by query string first - query string is always stronger value
		$requestLocalization = $this->request->GetParam(
			$localizationUrlParam, 
			$langAndLocaleSeparator . 'a-zA-Z0-9'
		);
		$requestLocalizationValidStr = $requestLocalization && strlen($requestLocalization) > 0;
		if ($requestLocalizationValidStr) {
			$requestLocalization = strtolower($requestLocalization);
			$separatorPos = strpos($requestLocalization, static::LANG_AND_LOCALE_SEPARATOR);
			if ($separatorPos !== FALSE) 
				$requestLocalization = substr($requestLocalization, 0, $separatorPos + 1)
					. strtoupper(substr($requestLocalization, $separatorPos + 1));
		}
		if ($requestLocalizationValidStr && isset($this->allowedLocalizations[$requestLocalization])) {
			$this->requestLocalization = explode($langAndLocaleSeparator, $requestLocalization);
			$this->request->SetLang($this->requestLocalization[0]);
			if ($this->requestLocalization[1]) $this->request->SetLocale($this->requestLocalization[1]);
		} else if (isset($this->localizationEquivalents[$requestLocalization])) {
			$targetLocalization = explode($langAndLocaleSeparator, $this->localizationEquivalents[$requestLocalization]);
			if ($this->stricModeBySession && $this->sessionLocalization) {
				return $this->redirectToTargetLocalization(
					$this->setUpLocalizationToContextAndSession($this->sessionLocalization)
				);
			} else {
				return $this->redirectToTargetLocalization(
					$this->setUpLocalizationToContextAndSession($targetLocalization)
				);
			}
		}
		return TRUE;
	}
	
	/**
	 * Try to set up lang (or lang and locale) from request path.
	 * If there is any request path detected, remove localization from request 
	 * path and store detected localization in local context.
	 * @return bool
	 */
	protected function setUpRequestLocalizationFromUrlPath () {
		// if there is no localization in query string - try to detect localization from path
		$requestPath = $this->request->GetPath(TRUE);
		/**
			* $requestPath = '/'					=> $secondSlashPos = FALSE	=> $firstPathElm = ''
			* $requestPath = '/en'					=> $secondSlashPos = FALSE	=> $firstPathElm = 'en'
			* $requestPath = '/en/'				=> $secondSlashPos = 3		=> $firstPathElm = 'en'
			* $requestPath = '/en/move...'			=> $secondSlashPos = 3		=> $firstPathElm = 'en'
			* $requestPath = '/any-thing'			=> $secondSlashPos = FALSE	=> $firstPathElm = 'any-thing'
			* $requestPath = '/any-thing/'			=> $secondSlashPos = 10		=> $firstPathElm = 'any-thing'
			* $requestPath = '/any-thing/more...'	=> $secondSlashPos = 10		=> $firstPathElm = 'any-thing'
			*/
		$secondSlashPos = mb_strlen($requestPath) > 1 
			? mb_strpos($requestPath, '/', 1) 
			: FALSE;
		if ($secondSlashPos === FALSE) {
			$firstPathElm = $requestPath !== '/' ? mb_substr($requestPath, 1) : '';
		} else {
			$firstPathElm = mb_substr($requestPath, 1, $secondSlashPos - 1);
		}
		$localizationPart = preg_replace(
			"#[^" . static::LANG_AND_LOCALE_SEPARATOR . "a-z0-9]#", 
			'', strtolower($firstPathElm)
		);
		$separatorPos = strpos($localizationPart, static::LANG_AND_LOCALE_SEPARATOR);
		if ($separatorPos !== FALSE) 
			$localizationPart = substr($localizationPart, 0, $separatorPos + 1)
				. strtoupper(substr($localizationPart, $separatorPos + 1));
		if (isset($this->allowedLocalizations[$localizationPart])) {
			$langAndLocale = explode(static::LANG_AND_LOCALE_SEPARATOR, $localizationPart);
			if (strlen($langAndLocale[0]) > 0) {
				$this->requestLocalization = $langAndLocale;
				$this->request
					->SetLang($langAndLocale[0])
					->SetPath(
						mb_substr($requestPath, strlen($localizationPart) + 1)
					);
				if (count($langAndLocale) > 1 && strlen($langAndLocale[1]) > 0) 
					$this->request->SetLocale($langAndLocale[1]);
			}
		} else if (isset($this->localizationEquivalents[$localizationPart])) {
			$targetLocalization = explode(static::LANG_AND_LOCALE_SEPARATOR, $this->localizationEquivalents[$localizationPart]);
			$this->request->SetPath(
				mb_substr($requestPath, strlen($localizationPart) + 1)
			);
			if ($this->stricModeBySession && $this->sessionLocalization) {
				return $this->redirectToTargetLocalization(
					$this->setUpLocalizationToContextAndSession($this->sessionLocalization)
				);
			} else {
				return $this->redirectToTargetLocalization(
					$this->setUpLocalizationToContextAndSession($targetLocalization)
				);
			}
		}
		if (
			$this->requestLocalization === NULL && 
			(trim($requestPath, '/') === '' && $requestPath !== $this->request->GetScriptName())
		) {
			$this->requestLocalization = $this->defaultLocalization;
			$this->request->SetLang($this->requestLocalization[0]);
			if ($this->requestLocalization[1]) 
				$this->request->SetLocale($this->requestLocalization[1]);
		}
		return TRUE;
	}

	/**
	 * Store new localization from url in session namespace, remove localization
	 * switching param from request object global collection `$_GET` and 
	 * redirect to the same page without switching param.
	 * @return bool
	 */
	protected function manageLocalizationSwitchingAndRedirect () {
		$targetLocalization = explode(static::LANG_AND_LOCALE_SEPARATOR, $this->switchUriParamLocalization);
		// unset site key switch param
		unset($this->requestGlobalGet[static::SWITCH_LOCALIZATION_URL_PARAM]);
		// redirect to no switch param uri version
		return $this->redirectToTargetLocalization(
			$this->setUpLocalizationToContextAndSession($targetLocalization)
		);
	}

	/**
	 * Detect language and locale by sended `Accept-Language` http header string 
	 * and store detected result in session namespace for next requests.
	 * Also store boolean property `$this->firstRequestLocalizationDetection` if there was
	 * matched the very first accepted language and locale or not.
	 * @return void
	 */
	protected function manageLocalizationDetectionAndStoreInSession () {
		$firstRequestLocalizationDetection = FALSE;
		$requestClass = $this->application->GetRequestClass();
		$requestGlobalServer = $this->request->GetGlobalCollection('server');
		$allLanguagesAndLocales = $requestClass::ParseHttpAcceptLang($requestGlobalServer['HTTP_ACCEPT_LANGUAGE']);
		$counter = 1;
		foreach ($allLanguagesAndLocales as /*$priority =>*/ $languagesAndLocales) {
			foreach ($languagesAndLocales as $languageAndLocale) {
				$languageAndLocaleCount = count($languageAndLocale);
				if (!$languageAndLocaleCount || $languageAndLocale[0] === NULL) {
					$counter++;
					continue;
				}
				$localizationStr = strtolower($languageAndLocale[0]);
				if ($languageAndLocaleCount > 1 && $languageAndLocale[1] !== NULL) 
					$localizationStr .= static::LANG_AND_LOCALE_SEPARATOR . strtoupper($languageAndLocale[1]);
				if (isset($this->allowedLocalizations[$localizationStr])) {
					$this->localization = $languageAndLocale;
					if ($counter === 1) $firstRequestLocalizationDetection = TRUE;
					break 2;
				}
				if (isset($this->localizationEquivalents[$localizationStr])) {
					$this->localization = explode(static::LANG_AND_LOCALE_SEPARATOR, $this->localizationEquivalents[$localizationStr]);
					if ($counter === 1) $firstRequestLocalizationDetection = TRUE;
					break 2;
				}
				$counter++;
			}
		}
		if (!$this->localization) 
			$this->localization = $this->defaultLocalization;
		$localizationUrlParam = static::LOCALIZATION_URL_PARAM;
		$this->session->{$localizationUrlParam} = $this->localization;
		$this->firstRequestLocalizationDetection = $firstRequestLocalizationDetection;
	}

	/**
	 * Check request localization, session localization and possible detected
	 * localization in local context by `Accept-Language` http header, check
	 * if there ware any detection in first request and than do everything by 
	 * configuration. This function is optimized to not process too much 
	 * conditions or to not process anything twice. It's still do the logic 
	 * very well. Do not change anything if you don't know all router options.
	 * @return bool
	 */
	protected function checkLocalizationWithUrlAndRedirectIfNecessary() {
		// if there is no localization in request and non-localized routes like 
		// `/admin` are allowed, do not redirect to anywhere, do nothing and return
		if (!$this->requestLocalization && $this->allowNonLocalizedRoutes) 
			return TRUE;
		if ($this->routeGetRequestsOnly && $this->request->GetMethod() !== \MvcCore\IRequest::METHOD_GET)
			return TRUE;
		// if there was first detection not with very precise result, 
		// decide if to redirect to global or if to stay where we are
		if (
			$this->firstRequestLocalizationDetection === FALSE || (
				$this->firstRequestLocalizationDetection === TRUE && 
				$this->requestLocalization !== NULL && 
				$this->requestLocalization !== $this->localization
			)
		) {
			if ($this->redirectFirstRequestToDefault) {
				$targetLocalization = $this->defaultLocalization;
				/** @var $request \MvcCore\Request */
				$request = & $this->request;
				$this->requestGlobalGet[static::REDIRECTED_SOURCE_URL_PARAM] = rawurlencode(
					$request->GetBaseUrl() 
					. $request->GetOriginalPath()
					. $request->GetQuery(TRUE, TRUE) 
					. $request->GetFragment(TRUE, TRUE)
				);
				$request->SetPath('');
			} else if ($this->requestLocalization !== NULL) {
				$targetLocalization = $this->requestLocalization;
			} else {
				$targetLocalization = $this->localization;
			}
		} else if (($this->stricModeBySession && !$this->adminRequest) || $this->requestLocalization === NULL) {
			$targetLocalization = $this->localization;
		} else {
			$targetLocalization = $this->setUpLocalizationToContextAndSession($this->requestLocalization);
		}
		$originalRequestPath = trim($this->originalRequestPath, '/');
		if ($originalRequestPath === $this->defaultLocalizationStr) 
			return $this->redirectToTargetLocalization(
				$this->setUpLocalizationToContextAndSession($this->requestLocalization)	
			);
		if ($targetLocalization === $this->requestLocalization) 
			return TRUE;
		return $this->redirectToTargetLocalization(
			$this->setUpLocalizationToContextAndSession($targetLocalization)	
		);
	}

	/**
	 * Set up localization array into current context and into session and return it.
	 * @param \string[] $targetLocalization 
	 * @return \string[]
	 */
	protected function setUpLocalizationToContextAndSession ($targetLocalization) {
		$this->session->{static::LOCALIZATION_URL_PARAM} = $targetLocalization;
		$this->localization = $targetLocalization;
		return $targetLocalization;
	}

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
		if ($localizationInRequest) {
			$requestPath = $request->GetPath();
		} else {
			$requestPath = $this->originalRequestPath;
		}
		if ($requestPath === '') {
			$requestPath = '/';
		}
		$requestMethod = $request->GetMethod();
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
		foreach ($this->routes as & $route) {
			// skip non localized routes by configuration
			$routeIsLocalized = $route instanceof \MvcCore\Ext\Routers\Localizations\Route;
			// skip localized routes matching when request has no localization in path
			if (!$localizationInRequest && $routeIsLocalized) {
				// but do not skip localized routes matching when request has no localization in path and:
				// - when method is post and router has not allowed to process other methods than GET
				// - or when method is anything and router has allowed to process other methods than GET
				if (!($this->routeGetRequestsOnly && $requestMethod !== \MvcCore\IRequest::METHOD_GET)) 
					continue;
			}
			if (!$this->allowNonLocalizedRoutes && !$routeIsLocalized) continue;
			if ($matchedParams = $route->Matches($requestPath, $requestMethod, $routesLocalizationStr)) {
				$this->currentRoute = & $route;
				$routeDefaultParams = $route->GetDefaults($routesLocalizationStr) ?: [];
				$newParams = array_merge($routeDefaultParams, $matchedParams, $request->GetParams('.*'));
				$request->SetParams($newParams);
				$matchedParamsClone = array_merge([], $matchedParams);
				unset($matchedParamsClone['controller'], $matchedParamsClone['action']);
				if ($matchedParamsClone) {
					if ($this->requestedUrlParams) {
						$requestedUrlParamsToMerge = $this->requestedUrlParams;
					} else {
						$requestedUrlParamsToMerge = [];
					}
					$this->requestedUrlParams = array_merge(
						$requestedUrlParamsToMerge, $matchedParamsClone
					);
					$this->requestedUrlParams[static::LOCALIZATION_URL_PARAM] = $localizationStr;
				}
				break;
			}
		}
		if ($this->currentRoute !== NULL) 
			$this->routeByRewriteRoutesSetUpRequestByCurrentRoute(
				$requestCtrlName, $requestActionName
			);
	}
}
