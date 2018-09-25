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
		$result = TRUE;
		$this->preRoutePrepare();
		if (!$this->preRoutePrepareLocalization()) return FALSE;
		
		if (
			(($this->isGet && $this->routeGetRequestsOnly) || !$this->routeGetRequestsOnly) &&
			$this->switchUriParamLocalization !== NULL
		) {
			// if there is detected in requested url media site version switching param,
			// store switching param value in session, remove param from `$_GET` 
			// and redirect to the same page with new media site version:
			$result = $this->manageLocalizationSwitchingAndRedirect();

		} else if (
			(($this->isGet && $this->routeGetRequestsOnly) || !$this->routeGetRequestsOnly) && 
			$this->sessionLocalization === NULL
		) {
			// if there is no session record about media site version:
			$this->manageLocalizationDetectionAndStoreInSession();
			// check if media site version is the same as local media site version:
			$result = $this->checkLocalizationWithUrlAndRedirectIfNecessary();

		} else {
			// if there is media site version in session already:
			$this->localization = $this->sessionLocalization;
			// check if media site version is the same as local media site version:
			$result = $this->checkLocalizationWithUrlAndRedirectIfNecessary();
		}

		// set up stored/detected localization into request:
		if ($this->localization) {
			$this->request->SetLang($this->localization[0]);
			if (count($this->localization) > 1) 
				$this->request->SetLocale($this->localization[1]);
		}
		// return `TRUE` or `FALSE` to break or not the routing process
		return $result;
	}

	/**
	 * Prepare localizations processing:
	 * - Check if any default localization configured.
	 * - Put default localization into allowed localizations for sure.
	 * - Try to complete switching param from request object global `$_GET` collection.
	 * - Try to complete session language and locale.
	 * - Try to complete request language and locale from request query string or path.
	 * @return bool
	 */
	protected function preRoutePrepareLocalization () {
		// check all necessary properties configured
		if (!$this->defaultLocatization)
			throw new \RuntimeException("[".__CLASS__."] No default localization configured.");

		// prepare possibly modified allowed localizations
		$this->allowedLocalizations = array_combine($this->allowedLocalizations, $this->allowedLocalizations);
		// add default localization into allowed langs for sure
		$this->defaultLocatizationStr = implode(static::LANG_AND_LOCALE_SEPARATOR, $this->defaultLocatization);
		$this->allowedLocalizations[$this->defaultLocatizationStr] = $this->defaultLocatizationStr;

		// add automaticly into equivalents also all langs parsed from localizations if necessary
		if ($this->detectAcceptLanguageOnlyByLang) {
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
			$sessStrictModeSwitchUrlParam = static::SWITCH_LOCATIZATION_URL_PARAM;
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
		$this->stricModeBySession = (
			$this->stricModeBySession && 
			(($this->isGet && $this->routeGetRequestsOnly) || !$this->routeGetRequestsOnly)
		);
		if (isset($this->session->{static::LOCATIZATION_URL_PARAM})) 
			$this->sessionLocalization = $this->session->{static::LOCATIZATION_URL_PARAM};
		
		// get current lang version from url string
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
		if ($this->requestLocatization === NULL && $this->anyRoutesConfigured) 
			return $this->setUpRequestLocalizationFromUrlPath();
		return TRUE;
	}

	/**
	 * Try to set up lang (or lang and locale) from request query string.
	 * @return bool
	 */
	protected function setUpRequestLocalizationFromUrlQueryString () {
		$this->requestLocatization = NULL;
		$localizationUrlParam = static::LOCATIZATION_URL_PARAM;
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
			$this->requestLocatization = explode($langAndLocaleSeparator, $requestLocalization);
			$this->request->SetLang($this->requestLocatization[0]);
			if ($this->requestLocatization[1]) $this->request->SetLocale($this->requestLocatization[1]);
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
		$secondSlashPos = mb_strpos($requestPath, '/', 1);
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
				$this->requestLocatization = $langAndLocale;
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
		if ($this->requestLocatization === NULL && $requestPath == '/') {
			$this->requestLocatization = $this->defaultLocatization;
			$this->request->SetLang($this->requestLocatization[0]);
			if ($this->requestLocatization[1]) 
				$this->request->SetLocale($this->requestLocatization[1]);
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
		unset($this->requestGlobalGet[static::SWITCH_LOCATIZATION_URL_PARAM]);
		// redirect to no switch param uri version
		return $this->redirectToTargetLocalization(
			$this->setUpLocalizationToContextAndSession($targetLocalization)
		);
	}

	/**
	 * Detect language and locale by sended `Accept-Language` http header string 
	 * and store detected result in session namespace for next requests.
	 * Also store boolean property `$this->firstRequestDetection` if there was
	 * matched the very first accepted language and locale or not.
	 * @return void
	 */
	protected function manageLocalizationDetectionAndStoreInSession () {
		$firstRequestDetection = FALSE;
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
					if ($counter === 1) $firstRequestDetection = TRUE;
					break 2;
				}
				if (isset($this->localizationEquivalents[$localizationStr])) {
					$this->localization = explode(static::LANG_AND_LOCALE_SEPARATOR, $this->localizationEquivalents[$localizationStr]);
					if ($counter === 1) $firstRequestDetection = TRUE;
					break 2;
				}
				$counter++;
			}
		}
		if (!$this->localization) 
			$this->localization = $this->defaultLocatization;
		$localizationUrlParam = static::LOCATIZATION_URL_PARAM;
		$this->session->{$localizationUrlParam} = $this->localization;
		$this->firstRequestDetection = $firstRequestDetection;
	}

	/**
	 * If there was first request and if there was no matched the very fist 
	 * `Accept-Language` value, target localization will be changed by config 
	 * boolean if `$this->redirectFirstRequestToDefault`. If `TRUE`, set target
	 * localization to default language, else to detected localization. 
	 * 
	 * If there was not first request, set target localization to detected 
	 * localization value from session.
	 * 
	 * If target localization is the same as requested localization - do not 
	 * process any redirections. 
	 * 
	 * If target localization is different than localization in requested url 
	 * then  - if strict mode by session is configured as `TRUE` - redirect to 
	 * local context localization, which is always defined by session, in first 
	 * request by default lang. If it's configured as `FALSE`, redirect to 
	 * requested localization by client.
	 * @return bool
	 */
	protected function checkLocalizationWithUrlAndRedirectIfNecessary() {
		// if there is no localization in request and non-localized routes like 
		// `/admin` are allowed, do not redirect to anywhere, do nothing and return
		if (!$this->requestLocatization && $this->allowNonLocalizedRoutes) 
			return TRUE;
		// if there was first detection not with very precise result, 
		// decide if to redirect to global or if to stay where we are
		if (
			$this->firstRequestDetection === FALSE || (
				$this->firstRequestDetection === TRUE && 
				$this->requestLocatization !== NULL && 
				$this->requestLocatization !== $this->localization
			)
		) {
			$targetLocalization = $this->redirectFirstRequestToDefault
				? $this->defaultLocatization
				: ($this->requestLocatization !== NULL
					? $this->requestLocatization
					: $this->localization);
		} else if ($this->stricModeBySession || $this->requestLocatization === NULL) {
			$targetLocalization = $this->localization;
		} else {
			$targetLocalization = $this->setUpLocalizationToContextAndSession($this->requestLocatization);
		}
		$originalRequestPath = trim($this->request->GetOriginalPath(), '/');
		if ($originalRequestPath === $this->defaultLocatizationStr) 
			return $this->redirectToTargetLocalization(
				$this->setUpLocalizationToContextAndSession($this->requestLocatization)	
			);
		if ($targetLocalization === $this->requestLocatization) 
			return TRUE;
		return $this->redirectToTargetLocalization(
			$this->setUpLocalizationToContextAndSession($targetLocalization)	
		);
	}

	/**
	 * Redirect to target localization version with path and uery string.
	 * @param \string[] $targetLocalization 
	 * @return boolean
	 */
	protected function redirectToTargetLocalization ($targetLocalization) {
		// prepare for uri manipulation
		$sessStrictModeSwitchUrlParam = static::SWITCH_LOCATIZATION_URL_PARAM;
		unset($this->requestGlobalGet[$sessStrictModeSwitchUrlParam]);
		// unset site key switch param and redirect to no switch param uri version
		$request = & $this->request;
		$localizationUrlParam = static::LANG_AND_LOCALE_SEPARATOR;
		$targetLocalizationStr = implode($localizationUrlParam, $targetLocalization);
		$targetIsTheSameAsDefault = $targetLocalizationStr === $this->defaultLocatizationStr;
		if ($this->anyRoutesConfigured) {
			$path = $request->GetPath(TRUE);
			$targetLocalizationStr = ($targetIsTheSameAsDefault && ($path == '/' || $path == ''))
				? ''
				: '/' . $targetLocalizationStr;
			$targetUrl = $request->GetBaseUrl() 
				. $targetLocalizationStr
				. $path;
		} else {
			$targetUrl = $request->GetBaseUrl();
			if ($targetIsTheSameAsDefault) {
				if (isset($this->requestGlobalGet[$localizationUrlParam]))
					unset($this->requestGlobalGet[$localizationUrlParam]);
			} else {
				$this->requestGlobalGet[$localizationUrlParam] = $targetLocalizationStr;
			}
			$this->removeDefaultCtrlActionFromGlobalGet();
			if ($this->requestGlobalGet)
				$targetUrl .= $request->GetScriptName();
		}
		if ($this->requestGlobalGet) {
			$amp = $this->getQueryStringParamsSepatator();
			$targetUrl .= '?' . str_replace('%2F', '/', http_build_query($this->requestGlobalGet, '', $amp));
		}
		$this->redirect($targetUrl, \MvcCore\Interfaces\IResponse::SEE_OTHER);
		return FALSE;
	}

	/**
	 * Set up localization array into current context and into session and return it.
	 * @param \string[] $targetLocalization 
	 * @return \string[]
	 */
	protected function setUpLocalizationToContextAndSession ($targetLocalization) {
		$this->session->{static::LOCATIZATION_URL_PARAM} = $targetLocalization;
		$this->localization = $targetLocalization;
		return $targetLocalization;
	}

	/**
	 * Complete `\MvcCore\Router::$currentRoute` and request params by defined routes.
	 * Go throught all configured routes and try to find matching route.
	 * If there is catched any matching route - reset `\MvcCore\Request::$params`
	 * with default route params, with params itself and with params parsed from matching process.
	 * @param string $controllerName
	 * @param string $actionName
	 * @return void
	 */
	protected function routeByRewriteRoutes ($requestCtrlName, $requestActionName) {
		$request = & $this->request;
		$localizationInRequest = count($this->requestLocatization) > 0;
		$requestPath = $localizationInRequest
			? $request->GetPath()
			: $request->GetOriginalPath();
		if ($requestPath === '') 
			$requestPath = '/';
		$requestMethod = $request->GetMethod();
		$localizationStr = implode(static::LANG_AND_LOCALE_SEPARATOR, $this->localization);
		$routesLocalizationStr = $this->routeRecordsByLanguageAndLocale
			? $localizationStr
			: $this->localization[0];
		/** @var $route \MvcCore\Route */
		reset($this->routes);
		foreach ($this->routes as & $route) {
			// skip non localized routes by configuration
			$routeIsLocalized = $route instanceof \MvcCore\Ext\Routers\Localizations\Route;
			if (!$localizationInRequest && $routeIsLocalized) continue;
			if (!$this->allowNonLocalizedRoutes && !$routeIsLocalized) continue;
			if ($matchedParams = $route->Matches($requestPath, $requestMethod, $routesLocalizationStr)) {
				$this->currentRoute = & $route;
				$routeDefaultParams = $route->GetDefaults($routesLocalizationStr) ?: [];
				$newParams = array_merge($routeDefaultParams, $request->GetParams('.*'), $matchedParams);
				$request->SetParams($newParams);
				$matchedParamsClone = array_merge([], $matchedParams);
				unset($matchedParamsClone['controller'], $matchedParamsClone['action']);
				if ($matchedParamsClone) {
					$this->requestedUrlParams = array_merge(
						$this->requestedUrlParams ? $this->requestedUrlParams : [],
						$matchedParamsClone
					);
					$this->requestedUrlParams[static::LOCATIZATION_URL_PARAM] = $localizationStr;
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
