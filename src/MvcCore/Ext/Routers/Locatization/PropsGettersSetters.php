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

trait PropsGettersSetters
{
	/**
	 * Localized route class name, never patched in ocre, 
	 * only used internaly in this class.
	 * @var string
	 */
	protected static $routeClassLocalized = '\\MvcCore\\Ext\\Routers\\Localizations\\Route';

	/**
	 * Default language, two lowercase characters, internaltional language code,
	 * lang to use in cases, when is not possible to detect lang from url,
	 * not possible to detect lang from 'Accept-Language' http header
	 * or not possible to get from session.
	 * @var string
	 */
	protected $defaultLocatization = ['en', 'US'];

	/**
	 * Result language, lowercase characters, internaltional language code.
	 * Example: 'en' | 'fr' | 'de'...
	 * @var string|NULL
	 */
	protected $lang = NULL;

	/**
	 * Result locale, uppercase characters, internaltional locale code.
	 * Example: 'US' | 'UK' | 'DE'...
	 * @var string|NULL
	 */
	protected $locale = NULL;

	// non configurable props:

	/**
	 * @var string|NULL
	 */
	protected $localization = NULL;

	/**
	 * Lang founded in session.
	 * @var string
	 */
	protected $sessionLocalization = NULL;

	/**
	 * Lang founded in request.
	 * @var string
	 */
	protected $requestLocalization = NULL;

	/**
	 * Lang value in special $_GET param if session mode is strict.
	 * @var string
	 */
	protected $switchUriParamLocalization = NULL;

	/**
	 * If TRUE, redirect request to default language version if lang in request is not allowed.
	 * If not configured, TRUE by default.
	 * @var bool
	 */
	protected $allowNonLocalizedRoutes = TRUE;

	/**
	 * If TRUE, redirect request to default language version if lang in request is not allowed.
	 * If not configured, TRUE by default.
	 * @var bool
	 */
	protected $redirectFirstRequestToDefault = FALSE;

	/**
	 * Allowed language codes to use in your application, default lang will be allowed automaticly.
	 * @var array
	 */
	protected $allowedLocalizations = [];

	
	/**
	 * @return array
	 */
	public function GetDefaultLocatization () {
		return $this->defaultLocatization;
	}
	
	/**
	 * @var string $defaultLang
	 * @var string $defaultLocale
	 * @return \MvcCore\Ext\Routers\Localization|\MvcCore\Ext\Routers\ILocalization
	 */
	public function & SetDefaultLocatization ($defaultLang, $defaultLocale = NULL) {
		$this->defaultLocatization = [$defaultLang, $defaultLocale];
		return $this;
	}

	/**
	 * @return array
	 */
	public function GetLocalization () {
		return [$this->lang, $this->locale];
	}

	/**
	 * @param string $lang
	 * @param string $locale
	 * @return \MvcCore\Ext\Routers\Localization|\MvcCore\Ext\Routers\ILocalization
	 */
	public function & SetLocalization ($lang, $locale = NULL) {
		$this->lang = $lang;
		$this->locale = $locale;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function GetRedirectFirstRequestToDefault () {
		return $this->redirectFirstRequestToDefault;
	}

	/**
	 * If TRUE, first request language will be strictly recognized by user agent
	 * http header 'Acept-Language', not by requested url. First or not first request
	 * is detected by session. If not configured, FALSE by default.
	 * @param bool $redirectFirstRequestToDefault
	 * @return \MvcCore\Ext\Routers\Localization|\MvcCore\Ext\Routers\ILocalization
	 */
	public function & SetRedirectFirstRequestToDefault ($redirectFirstRequestToDefault = TRUE) {
		$this->redirectFirstRequestToDefault = $redirectFirstRequestToDefault;
		return $this;
	}

	public function GetAllowNonLocalizedRoutes () {
		return $this->allowNonLocalizedRoutes;
	}

	/**
	 * If TRUE, redirect request to default language version if lang in request is not allowed.
	 * If not configured, TRUE by default.
	 * @param bool $allowNonLocalizedRoutes
	 * @return \MvcCore\Ext\Routers\Localization|\MvcCore\Ext\Routers\ILocalization
	 */
	public function & SetAllowNonLocalizedRoutes ($allowNonLocalizedRoutes = TRUE) {
		$this->allowNonLocalizedRoutes = $allowNonLocalizedRoutes;
		return $this;
	}

	/**
	 * @return array
	 */
	public function & GetAllowedLocalizations () {
		return array_keys($this->allowedLocalizations);
	}

	/**
	 * Set international lowercase language code(s), allowed to use in your application.
	 * Default language is always allowed.
	 * @var string $allowedLocalizations..., international lowercase language code(s) (+ optinally dash character + uppercase international locale code(s))
	 * @return \MvcCore\Ext\Routers\Localization|\MvcCore\Ext\Routers\ILocalization
	 */
	public function & SetAllowedLocalizations (/* ...$allowedLocalizations */) {
		$this->allowedLocalizations = [];
		$this->AddAllowedLocalizations(func_get_args());
		return $this;
	}

	/**
	 * Add international lowercase language code(s), allowed to use in your application.
	 * Default language is always allowed.
	 * @var string $allowedLocalizations..., international lowercase language code(s) (+ optinally dash character + uppercase international locale code(s))
	 * @return \MvcCore\Ext\Routers\Localization|\MvcCore\Ext\Routers\ILocalization
	 */
	public function & AddAllowedLocalizations (/* ...$allowedLocalizations */) {
		$allowedLocalizations = func_get_args();
		if (count($allowedLocalizations) === 1 && gettype($allowedLocalizations[0]) == 'array') 
			$allowedLocalizations = $allowedLocalizations[0];
		foreach ($allowedLocalizations as $allowedLocalization) 
			$this->allowedLocalizations[$allowedLocalization] = 1;
		return $this;
	}


	/**
	 * Detect and return `TRUE` if even one route configuration data are localized
	 * Return `TRUE` if one of `pattern`, `match`, `reverse` or `defaults` is an array.
	 * @param array $routeCfgData 
	 * @return boolean
	 */
	protected function isRouteCfgDataLocalized (array & $routeCfgData = []) {
		return (
			(isset($routeCfgData['pattern']) && is_array($routeCfgData['pattern'])) || 
			(isset($routeCfgData['match']) && is_array($routeCfgData['match'])) || 
			(isset($routeCfgData['reverse']) && is_array($routeCfgData['reverse'])) || 
			(isset($routeCfgData['defaults']) && is_array($routeCfgData['defaults']))
		);
	}

	/**
	 * Get always route instance from given route configuration data or return
	 * already created given instance.
	 * @param \MvcCore\Route|\MvcCore\Interfaces\IRoute|array $routeCfgOrRoute Route instance or
	 *																		   route config array.
	 * @return \MvcCore\Route|\MvcCore\Interfaces\IRoute
	 */
	protected function & getRouteInstance (& $routeCfgOrRoute) {
		if ($routeCfgOrRoute instanceof \MvcCore\Interfaces\IRoute) 
			return $routeCfgOrRoute;
		$routeClass = $this->isRouteCfgDataLocalized($route) 
			? self::$routeClassLocalized 
			: self::$routeClass;
		$instance = $routeClass::CreateInstance($routeCfgOrRoute);
		return $instance;
	}
}
