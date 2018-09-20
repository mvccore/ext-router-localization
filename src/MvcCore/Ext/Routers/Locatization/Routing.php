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
	 * Detect language version by configured rules,
	 * set up detected version to current context,
	 * into request and into session and redirect if necessary.
	 * @return void
	 */
	protected function preRouteLocalization () {
		$result = TRUE;
		$this->preRoutePrepare();
		$this->preRoutePrepareLocalization();

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
			$result = $this->checkLocalizationWithRequestVersionAndRedirectIfDifferent();

		} else {
			// if there is media site version in session already:
			$this->localization = $this->sessionLocalization;
			// check if media site version is the same as local media site version:
			$result = $this->checkLocalizationWithRequestVersionAndRedirectIfDifferent();
		}

		// set up stored/detected localization into request:
		$langAndLocale = explode(static::LANG_AND_LOCALE_SEPARATOR, $this->localization);

		$this->lang = strtolower($langAndLocale[0]);
		$this->request->SetLang($this->lang);
		if (count($langAndLocale) > 1) {
			$this->locale = strtoupper($langAndLocale[1]);
			$this->request->SetLocale($this->locale);
		}
		
		// return `TRUE` or `FALSE` to break or not the routing process
		return $result;
	}

	/**
	 * Prepare language processing:
	 * - store request object reference
	 * - store request path into request original path
	 * - try to complete switching param from $_GET
	 * - try to complete request lang
	 * - try to complete session lang
	 * @return void
	 */
	protected function preRoutePrepareLocalization () {
		// add default localization into allowed langs for sure
		$this->allowedLocalizations[$this->defaultLocatization] = 1;
		
		//if ($this->stricModeBySession) { // check it with any strict session configuration to have more flexible navigations
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
		
		// get current lang version from url string
		$this->setUpRequestLocalizationFromUrl();
		
		// look into session object if there are or not
		// any record about lang from previous request
		if (isset($this->session->{static::LOCATIZATION_URL_PARAM})) {
			$this->sessionLang = $this->session->{static::LOCATIZATION_URL_PARAM};
		}
	}

	/**
	 * Try to set up lang (or lang and locale) from request, 
	 * if there is any lang or locale, correct request path,
	 * if thre is no request language or locale, set into request object 
	 * default lang (or default lang and default locale).
	 * @return void
	 */
	protected function setUpRequestLocalizationFromUrl () {
		$routesDefined = count($this->routes) > 0;
		$localizationCatchedInPath = FALSE;
		if ($routesDefined) {
			$requestPath = $this->request->GetPath(TRUE);
			/**
			 * $path = '/'				=> $secondSlashPos = FALSE	=> $firstPathElm = ''
			 * $path = '/en'			=> $secondSlashPos = FALSE	=> $firstPathElm = 'en'
			 * $path = '/en/'			=> $secondSlashPos = 3		=> $firstPathElm = 'en'
			 * $path = '/en/anything...'=> $secondSlashPos = 3		=> $firstPathElm = 'en'
			 * $path = '/baaad'			=> $secondSlashPos = FALSE	=> $firstPathElm = 'baaad'
			 * $path = '/baaad/'		=> $secondSlashPos = 3		=> $firstPathElm = 'baaad'
			 * $path = '/baaad/any...'	=> $secondSlashPos = 3		=> $firstPathElm = 'baaad'
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
					$localizationCatchedInPath = TRUE;
					$this->request
						->SetLang($langAndLocale[0])
						->SetPath(
							mb_substr($requestPath, strlen($localizationPart) + 1)
						);
					if (count($langAndLocale) > 1 && strlen($langAndLocale[1]) > 0) 
						$this->request->SetLocale($langAndLocale[1]);
				}
			}
		}
		if (!$routesDefined || !$localizationCatchedInPath) {
			$requestLocalization = $this->request->GetParam(
				static::LOCATIZATION_URL_PARAM, 
				static::LANG_AND_LOCALE_SEPARATOR.'a-zA-Z0-9'
			);
			$requestLocalizationValidStr = $requestLocalization && strlen($requestLocalization) > 0;
			if ($requestLocalizationValidStr) {
				$requestLocalization = strtolower($requestLocalization);
				$separatorPos = strpos($requestLocalization, static::LANG_AND_LOCALE_SEPARATOR);
				if ($separatorPos !== FALSE) 
					$requestLocalization = substr($requestLocalization, 0, $separatorPos + 1)
						. strtoupper(substr($requestLocalization, $separatorPos + 1));
			}
			if ($requestLocalizationValidStr && isset($this->allowedSiteKeysAndUrlPrefixes[$requestLocalization])) {
				$locatization = explode(static::LANG_AND_LOCALE_SEPARATOR, $requestLocalization);
			} else {
				$locatization = $this->defaultLocatization;
			}
			$this->request->SetLang($locatization[0]);
			if ($locatization[1]) $this->request->SetLocale($locatization[1]);
		}
	}

	/**
	 * Store new localization from url in session namespace, remove localization
	 * switching param from request object global collection `$_GET` and 
	 * redirect to the same page without switching param.
	 * @return bool
	 */
	protected function manageLocalizationSwitchingAndRedirect () {
		$localization = $this->switchUriParamLocalization;
		// store switched site key into session
		$localizationUrlParam = static::LOCATIZATION_URL_PARAM;
		$this->session->{$localizationUrlParam} = $localization;
		$sessStrictModeSwitchUrlParam = static::SWITCH_LOCATIZATION_URL_PARAM;
		unset($this->requestGlobalGet[$sessStrictModeSwitchUrlParam]);
		// unset site key switch param and redirect to no switch param uri version
		$request = & $this->request;
		if ($this->anyRoutesConfigured) {
			$targetUrl = $request->GetBaseUrl()
				. $this->allowedLocalizations[$localization] 
				. $request->GetPath();
		} else {
			$targetUrl = $request->GetBaseUrl();
			if ($localization === static::MEDIA_VERSION_FULL) {
				if (isset($this->requestGlobalGet[$localizationUrlParam]))
					unset($this->requestGlobalGet[$localizationUrlParam]);
			} else {
				$this->requestGlobalGet[$localizationUrlParam] = $localization;
			}
			$this->removeDefaultCtrlActionFromGlobalGet();
			if ($this->requestGlobalGet)
				$targetUrl .= $request->GetScriptName();
		}
		if ($this->requestGlobalGet) {
			$amp = $this->getQueryStringParamsSepatator();
			$targetUrl .= '?' . http_build_query($this->requestGlobalGet, '', $amp);
		}
		$this->redirect($targetUrl, \MvcCore\Interfaces\IResponse::SEE_OTHER);
		return FALSE;
	}





	/**
	 * Complete current route and request params by defined routes
	 * @return void
	 */
	protected function routeByRewriteRoutes () {
		$requestPath = $this->request->Path;
		foreach ($this->routes as & $route) {
			$routePattern = $this->getRouteLocalizedRecord($route, 'Pattern');
			preg_match_all($routePattern, $requestPath, $patternMatches);
			if (count($patternMatches) > 0 && count($patternMatches[0]) > 0) {
				$this->currentRoute = $route;
				$controllerName = isset($route->Controller)? $route->Controller: '';
				$routeParams = [
					'controller'	=>	\MvcCore\Tool::GetDashedFromPascalCase(str_replace(['_', '\\'], '/', $controllerName)),
					'action'		=>	\MvcCore\Tool::GetDashedFromPascalCase(isset($route->Action)	? $route->Action	: ''),
				];
				$routeReverse = $this->getRouteLocalizedRecord($route, 'Reverse');
				preg_match_all("#{%([a-zA-Z0-9]*)}#", $routeReverse, $reverseMatches);
				if (isset($reverseMatches[1]) && $reverseMatches[1]) {
					$reverseMatchesNames = $reverseMatches[1];
					array_shift($patternMatches);
					foreach ($reverseMatchesNames as $key => $reverseKey) {
						if (isset($patternMatches[$key]) && count($patternMatches[$key])) {
							// 1 line bellow is only for route debug panel, only for cases when you
							// forget to define current rewrite param, this defines null value by default
							if (!isset($route->Params[$reverseKey])) $route->Params[$reverseKey] = NULL;
							$routeParams[$reverseKey] = $patternMatches[$key][0];
						} else {
							break;
						}
					}
				}
				$routeDefaultParams = isset($route->Params) ? $route->Params : [];
				$this->request->Params = array_merge($routeDefaultParams, $routeParams, $this->request->Params);
				break;
			}
		}
	}

	/**
	 * Get route non-localized or localized record - 'Pattern' and 'Reverse'
	 * @param \MvcCore\Route $route
	 * @param string $routeRecordKey
	 * @return string
	 */
	protected function getRouteLocalizedRecord (\MvcCore\Route & $route, $routeRecordKey = '') {
		if ($route instanceof \MvcCore\Ext\Router\Lang\Route && gettype($route->$routeRecordKey) == 'array') {
			$routeRecordKey = $route->$routeRecordKey;
			if (isset($routeRecordKey[$this->Lang])) {
				return $routeRecordKey[$this->Lang];
			} else if (isset($routeRecordKey[$this->DefaultLang])) {
				return $routeRecordKey[$this->DefaultLang];
			}
			return reset($routeRecordKey);
		}
		return $route->$routeRecordKey;
	}

	/**
	 * Store detected language in session, in request and in router.
	 * If detected is different than request version - redirect to detected version.
	 * Else if original request version is different than request version
	 * and boolean switch $this->allowNonLocalizedRoutes is true, redirect to default lang.
	 * @param string $detectedLang
	 */
	protected function setUpDetectedLangAndRedirectIfNecessary ($detectedLang) {
		$this->Lang = $detectedLang;
		$this->session->{static::LOCATIZATION_URL_PARAM} = $detectedLang;
		$this->request->Lang = $detectedLang;

		if ($detectedLang !== $this->requestLang) {
			$this->redirectToDifferentLangVersion($detectedLang);
		} else if ($this->requestLangNotAllowed && $this->requestLangNotAllowed !== $this->requestLang) {
			if (!$this->allowNonLocalizedRoutes) {
				$this->redirectToDifferentLangVersion($this->DefaultLang);
			}
		} else if (!$this->keepDefaultLangPath && rtrim($this->request->OriginalPath, '/') == '/' . $this->DefaultLang) {
			$this->redirectToDifferentLangVersion($this->DefaultLang);
		}
	}

	/**
	 * Redirect to different language path version,
	 * only by changing first path element to different value.
	 * If router is configured to use default lang root path, keep it.
	 * @param string $targetLang
	 * @return void
	 */
	protected function redirectToDifferentLangVersion ($targetLang) {
		$targetPath = '/' . $targetLang . $this->request->Path;
		if (rtrim($targetPath, '/') == '/' . $this->DefaultLang) {
			if (!$this->keepDefaultLangPath) {
				$targetPath = '/';
			}
		}
		if (isset($_GET[static::LANG_SWITCH_URL_PARAM])) {
			unset($_GET[static::LANG_SWITCH_URL_PARAM]);
			$query = count($_GET) > 0 ? '?' . http_build_query($_GET) : '';
		} else {
			$query = ($this->request->Query ? '?' . $this->request->Query : '');
		}
		$newUrl = $this->request->DomainUrl
			. $this->request->BasePath
			. $targetPath . $query;
		\MvcCore\Controller::Redirect($newUrl);
	}

	/**
	 * Try to detect language from http header: 'Accept-Language'
	 * @var string
	 */
	protected function getDetectedLangByUserAgent () {
		$result = '';
		if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			$acceptLangs = $this->parseUserAgentLangList($_SERVER['HTTP_ACCEPT_LANGUAGE']);
			foreach ($acceptLangs as $acceptLangsItem) {
				$break = FALSE;
				foreach ($acceptLangsItem as $acceptLangRec) {
					$acceptLang = substr($acceptLangRec, 0, 2);
					if (isset($this->allowedLangs[$acceptLang])) {
						$result = $acceptLang;
						$break = TRUE;
						break;
					}
				}
				if ($break) break;
			}
		}
		return $result;
	}

	/**
	 * Parse list of comma separated language tags and sort it by the quality value
	 * @param string $languagesList
	 * @return array
	 */
	protected function parseUserAgentLangList($languagesList) {
		$languages = [];
		$languageRanges = explode(',', trim($languagesList));
		foreach ($languageRanges as $languageRange) {
			$regExpResult = preg_match(
				"/(\*|[a-zA-Z0-9]{1,8}(?:-[a-zA-Z0-9]{1,8})*)(?:\s*;\s*q\s*=\s*(0(?:\.\d{0,3})|1(?:\.0{0,3})))?/",
				trim($languageRange),
				$match
			);
			if ($regExpResult) {
				if (!isset($match[2])) {
					$match[2] = '1.0';
				} else {
					$match[2] = (string) floatval($match[2]);
				}
				if (!isset($languages[$match[2]])) {
					$languages[$match[2]] = [];
				}
				$languages[$match[2]][] = strtolower($match[1]);
			}
		}
		krsort($languages);
		return $languages;
	}
}
