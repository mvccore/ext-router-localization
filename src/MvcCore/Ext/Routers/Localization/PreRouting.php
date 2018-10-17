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

trait PreRouting
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

		} else if ($this->requestLocalizationEquivalent !== NULL) {
			// if there was catched localization equivalentm redirect to it's target
			return $this->redirectToVersion(
				$this->setUpLocalizationToContextAndSession($this->requestLocalizationEquivalent)
			);
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
	 * Store new localization from url in session namespace, remove localization
	 * switching param from request object global collection `$_GET` and 
	 * redirect to the same page without switching param.
	 * @return bool
	 */
	protected function manageLocalizationSwitchingAndRedirect () {
		$targetLocalization = explode(static::LANG_AND_LOCALE_SEPARATOR, $this->switchUriParamLocalization);
		// unset site key switch param
		unset($this->requestGlobalGet[static::URL_PARAM_SWITCH_LOCALIZATION]);
		// redirect to no switch param uri version
		return $this->redirectToVersion(
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
		$localizationUrlParam = static::URL_PARAM_LOCALIZATION;
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
				$this->requestGlobalGet[static::URL_PARAM_REDIRECTED_SOURCE] = rawurlencode(
					$request->GetBaseUrl() 
					. $request->GetOriginalPath()
					. $request->GetQuery(TRUE, TRUE) 
					. $request->GetFragment(TRUE, TRUE)
				);
				$request->SetPath('/');
			} else if ($this->requestLocalization !== NULL) {
				$targetLocalization = $this->requestLocalization;
			} else {
				$targetLocalization = $this->localization;
			}
		} else if (($this->stricModeBySession && !$this->adminRequest) || $this->requestLocalization === NULL) {
			$targetLocalization = $this->localization;
		} else {
			$this->setUpLocalizationToContextAndSession($this->requestLocalization);
			$targetLocalization = $this->requestLocalization;
		}
		$originalRequestPath = trim($this->originalRequestPath, '/');
		if ($originalRequestPath === $this->defaultLocalizationStr) 
			return $this->redirectToVersion(
				$this->setUpLocalizationToContextAndSession($this->requestLocalization)	
			);
		if ($targetLocalization === $this->requestLocalization) 
			return TRUE;
		return $this->redirectToVersion(
			$this->setUpLocalizationToContextAndSession($targetLocalization)	
		);
	}

	/**
	 * Set up localization array into current context and into session and return it.
	 * @param \string[] $targetLocalization 
	 * @return array
	 */
	protected function setUpLocalizationToContextAndSession ($targetLocalization) {
		$this->session->{static::URL_PARAM_LOCALIZATION} = $targetLocalization;
		$this->localization = $targetLocalization;
		return [\MvcCore\Ext\Routers\ILocalization::URL_PARAM_LOCALIZATION => $targetLocalization];
	}
}
