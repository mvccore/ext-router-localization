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

trait Redirecting
{
	/**
	 * Redirect to target localization version with path and by cloned request 
	 * object global `$_GET` collection. Return always `FALSE`.
	 * @param \string[] $targetLocalization 
	 * @return bool
	 */
	protected function redirectToTargetLocalization ($targetLocalization) {
		// unset site key switch param and redirect to no switch param uri version
		$request = & $this->request;
		$localizationUrlParam = static::URL_PARAM_LOCALIZATION;

		$targetLocalizationStr = implode(static::LANG_AND_LOCALE_SEPARATOR, $targetLocalization);
		$targetLocalizationSameAsDefault = $targetLocalizationStr === $this->defaultLocalizationStr;

		if (isset($this->requestGlobalGet[$localizationUrlParam])) {
			if ($targetLocalizationSameAsDefault) {
				if (isset($this->requestGlobalGet[$localizationUrlParam]))
					unset($this->requestGlobalGet[$localizationUrlParam]);
			} else {
				$this->requestGlobalGet[$localizationUrlParam] = $targetLocalizationStr;
			}
			$targetLocalizationPrefix = '';
		} else {
			$path = $request->GetPath(TRUE);
			$targetLocalizationPrefix = (
				$targetLocalizationSameAsDefault && 
				(trim($path, '/') === '' || $path === $this->request->GetScriptName())
			)
				? ''
				: '/' . $targetLocalizationStr;
		}

		if ($this->anyRoutesConfigured) {
			$targetUrl = $request->GetBaseUrl() 
				. $targetLocalizationPrefix
				. $request->GetPath(TRUE);
		} else {
			$targetUrl = $request->GetBaseUrl();
			$this->removeDefaultCtrlActionFromGlobalGet();
			if ($this->requestGlobalGet)
				$targetUrl .= $request->GetScriptName();
		}

		if ($this->requestGlobalGet) {
			$amp = $this->getQueryStringParamsSepatator();
			$targetUrl .= '?' . str_replace('%2F', '/', http_build_query($this->requestGlobalGet, '', $amp, PHP_QUERY_RFC3986));
		}
		
		if ($this->request->GetFullUrl() === $targetUrl) return TRUE;

		$this->redirect($targetUrl, \MvcCore\IResponse::MOVED_PERMANENTLY);
		return FALSE;
	}
}
