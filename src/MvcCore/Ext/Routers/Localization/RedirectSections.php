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

trait RedirectSections
{
	/**
	 * Redirect to target localization version with path and by cloned request 
	 * object global `$_GET` collection. Return always `FALSE`.
	 * @param array $systemParams 
	 * @return array `string $urlBaseSection, string $urlPathWithQuerySection, array $systemParams, bool|NULL $urlPathWithQueryIsHome`
	 */
	protected function redirectToVersionSections ($systemParams) {
		$request = & $this->request;
		$urlBaseSection = $request->GetBaseUrl();
		$urlPathWithQuerySection = $request->GetPath(TRUE);


		// unset site key switch param and redirect to no switch param uri version
		$localizationParamName = static::URL_PARAM_LOCALIZATION;
		$targetLocalizationUrlValue = $this->redirectLocalizationGetUrlValueAndUnsetGet(
			$systemParams[$localizationParamName]
		);
		
		$urlPathWithQueryIsHome = NULL;
		if ($this->anyRoutesConfigured) {
			
			if ($targetLocalizationUrlValue === NULL) {
				unset($systemParams[$localizationParamName]);
			} else {
				$systemParams[$localizationParamName] = $targetLocalizationUrlValue;
				if ($targetLocalizationUrlValue === $this->defaultLocalizationStr) {
					$urlPathWithQueryIsHome = $this->urlIsHomePath($urlPathWithQuerySection);
					if ($urlPathWithQueryIsHome)
						unset($systemParams[$localizationParamName]);
				}
			}

			$this->redirectAddAllRemainingInGlobalGet($urlPathWithQuerySection);
		} else {
			$this->removeDefaultCtrlActionFromGlobalGet();
			if ($this->requestGlobalGet)
				$urlPathWithQuerySection .= $request->GetScriptName();
			$this->redirectAddAllRemainingInGlobalGet($urlPathWithQuerySection);
		}

		$this->redirectStatusCode = \MvcCore\IResponse::MOVED_PERMANENTLY;

		return [
			$urlBaseSection,
			$urlPathWithQuerySection, 
			$systemParams,
			$urlPathWithQueryIsHome
		];
	}
}
