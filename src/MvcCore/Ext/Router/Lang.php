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

namespace MvcCore\Ext\Router;

class Lang extends \MvcCore\Router {

	/**
	 * MvcCore Extension - Router Lang - version:
	 * Comparation by PHP function version_compare();
	 * @see http://php.net/manual/en/function.version-compare.php
	 */
	const VERSION = '4.0.0';

	/**
	 * Key name for language in second argument $params in $router->Url();  method,
	 * to tell $router->Url() method to generate different language url.
	 */
	const LANG_URL_PARAM = 'lang';

	/**
	 * Special $_GET param name for session strict mode, how to change site language version.
	 */
	const LANG_SWITCH_URL_PARAM = 'switch_lang';

	/**
	 * Route preg_match pattern in classic PHP form:
	 * array('en' => "#^/url\-begin/([^/]*)/([^/]*)/(.*)#", 'de' => "#^/url\-beginn/([^/]*)/([^/]*)/(.*)#",);
	 * @var string|array
	 */
    public $Pattern		= '';

	/**
	 * Route reverse address form from preg_replace pattern
	 * in form: array('en' => "/url-begin/{%first}/{%second}/{%third}", 'de' => "/url-beginn/{%first}/{%second}/{%third}",);
	 * @var string|array
	 */
	public $Reverse		= '';

	/**
	 * Default language, two lowercase characters, internaltional language code,
	 * lang to use in cases, when is not possible to detect lang from url, 
	 * not possible to detect lang from 'Accept-Language' http header
	 * or not possible to get from session.
	 * @var string
	 */
	public $DefaultLang = 'en';

	/**
	 * Result language, two lowercase characters, internaltional language code.
	 * Example: 'en' | 'fr' | 'de'...
	 * @var string
	 */
	public $Lang = '';

	/**
	 * Session expiration seconds for remembering detected lang version by user agent.
	 * Session record is always used to compare if user is requesting different media
	 * site version then he has in session - if there is difference - user is redirected
	 * to session media site version and this seconds is time to remember that sessio record
	 * for described redirection.
	 * @var int
	 */
	public $SessionExpirationSeconds = 3600; // hour

	/**
	 * Session record is always used to compare if user is requesting different lang
	 * version then he has in session - if there is difference - user is redirected
	 * to session lang version.
	 * @var \MvcCore\Session|\stdClass
	 */
	protected $session = NULL;

	/**
	 * Lang founded in session.
	 * @var string
	 */
	protected $sessionLang = '';

	/**
	 * Lang founded in request.
	 * @var string
	 */
	protected $requestLang = '';

	/**
	 * Lang value in special $_GET param if session mode is strict.
	 * @var string
	 */
	protected $switchUriParamLang = '';

	/**
	 * If any, lang value in request, in url, not allowed to work with.
	 * @var string
	 */
	protected $requestLangNotAllowed = '';

	/**
	 * If true, process lang version strictly by session stored version,
	 * so if request contains some version and in session is different, redirect
	 * user to session version value adress, only when lang switching param
	 * is contained in $_GET, switch the version in session.
	 * If false, process lang version more benevolently, so if request
	 * contains some lang and in session is different, store in session lang
	 * version from request and do not redirect user.
	 * @var bool
	 */
	protected $stricModeBySession = FALSE;

	/**
	 * If TRUE and language is necessary to request into default language url version,
	 * there is target url path completed into '/'+$this->DefaultLang, not to '/' (slash) only.
	 * If FALSE and language is necessary to request into default language url version,
	 * there is target url path completed only to '/' (slash).
	 * If not configured, FALSE by default.
	 * @var bool
	 */
	protected $keepDefaultLangPath = FALSE;

	/**
	 * If TRUE, redirect request to default language version if lang in request is not allowed.
	 * If not configured, TRUE by default.
	 * @var boo
	 */
	protected $allowNonLocalizedRoutes = TRUE;

	/**
	 * If TRUE, redirect request to default language version if lang in request is not allowed.
	 * If not configured, TRUE by default.
	 * @var bool
	 */
	protected $firstRequestStrictlyByUserAgent = FALSE;

	/**
	 * Allowed language codes to use in your application, default lang will be allowed automaticly.
	 * @var array
	 */
	protected $allowedLangs = array();

	/**
	 * Set international lowercase language code(s), allowed to use in your application.
	 * Default language is always allowed.
	 * @var string $lang..., international lowercase language code(s)
	 * @return \MvcCore\Ext\Router\Lang
	 */
	public function SetAllowedLangs () {
		$this->allowedLangs = array();
		call_user_func_array(array($this, 'AddAllowedLangs'), func_get_args());
		return $this;
	}

	/**
	 * Add international lowercase language code(s), allowed to use in your application.
	 * Default language is always allowed.
	 * @var string $lang..., international lowercase language code(s)
	 * @return \MvcCore\Ext\Router\Lang
	 */
	public function AddAllowedLangs () {
		$args = func_get_args();
		if (count($args) === 1 && gettype($args[0]) == 'array') {
			$langs = $args[0];
		} else {
			$langs = $args;
		}
		$this->allowedLangs = array_merge($this->allowedLangs, $langs);
		return $this;
	}

	/**
	 * Set default lang to use in cases, when is not possible to detect 
	 * lang from url, not possible to detect lang from 'Accept-Language' http header
	 * or not possible to get from session.
	 * @param string $defaultLang 
	 * @return \MvcCore\Ext\Router\Lang
	 */
	public function SetDefaultLang ($defaultLang) {
		$this->DefaultLang = $defaultLang;
		return $this;
	}

	/**
	 * Set language externaly, not recomanded.
	 * @param string $lang 
	 * @return \MvcCore\Ext\Router\Lang
	 */
	public function SetLang ($lang) {
		$this->Lang = $lang;
		return $this;
	}

	/**
	 * Session expiration in seconds, by default - one hour.
	 * @param int $sessionExpirationSeconds 
	 * @return \MvcCore\Ext\Router\Lang
	 */
	public function SetSessionExpirationSeconds ($sessionExpirationSeconds = 3600) {
		$this->SessionExpirationSeconds = $sessionExpirationSeconds;
		return $this;
	}

	/**
	 * If TRUE, language is not possible to switch by different request url path begin,
	 * because for every different url path begin than session record is user automaticly
	 * redirected to url path begin by session. If TRUE, language version is possible to
	 * change only by special $_GET param called 'switch_lang=..' in query string.
	 * If not configured, FALSE by default.
	 * @param bool $stricModeBySession 
	 * @return \MvcCore\Ext\Router\Lang
	 */
	public function SetStricModeBySession ($stricModeBySession = TRUE) {
		$this->stricModeBySession = $stricModeBySession;
		return $this;
	}

	/**
	 * If TRUE and language is necessary to request into default language url version,
	 * there is target url path completed into '/'+$this->DefaultLang, not to '/' (slash) only.
	 * If FALSE and language is necessary to request into default language url version,
	 * there is target url path completed only to '/' (slash).
	 * If not configured, FALSE by default.
	 * @param mixed $keepDefaultLangPath 
	 * @return \MvcCore\Ext\Router\Lang
	 */
	public function SetKeepDefaultLangPath ($keepDefaultLangPath = TRUE) {
		$this->keepDefaultLangPath = $keepDefaultLangPath;
		return $this;
	}

	/**
	 * If TRUE, first request language will be strictly recognized by user agent
	 * http header 'Acept-Language', not by requested url. First or not first request 
	 * is detected by session. If not configured, FALSE by default.
	 * @param bool $firstRequestStrictlyByUserAgent 
	 * @return \MvcCore\Ext\Router\Lang
	 */
	public function SetFirstRequestStrictlyByUserAgent ($firstRequestStrictlyByUserAgent = TRUE) {
		$this->firstRequestStrictlyByUserAgent = $firstRequestStrictlyByUserAgent;
		return $this;
	}

	/**
	 * If TRUE, redirect request to default language version if lang in request is not allowed.
	 * If not configured, TRUE by default.
	 * @param bool $redirectToDefaultLangIfNotAllowed 
	 * @return \MvcCore\Ext\Router\Lang
	 */
	public function SetAllowNonLocalizedRoutes ($allowNonLocalizedRoutes = TRUE) {
		$this->allowNonLocalizedRoutes = $allowNonLocalizedRoutes;
		return $this;
	}

	/**
	 * Append or prepend new request route.
	 * Route definition array shoud be array with route
	 * configuration definition, stdClass with route configuration
	 * definition or \MvcCore\Route instance. In configuration definition is
	 * required route name, controller, action, pattern and if pattern contains
	 * regexp groups, its necessary also to define route reverse.
	 * Route name should be defined as 'Controller:Action' string or any custom
	 * route name, but then there is necessary to specify controller name and
	 * action name inside route array/stdClass configuration or route instance.
	 * @param array|\stdClass|\MvcCore\Route	$routeCfgOrRoute
	 * @param bool							$prepend
	 * @return \MvcCore\Router
	 */
	public function AddRoute ($routeCfgOrRoute, $prepend = FALSE) {
		if ($routeCfgOrRoute instanceof \MvcCore\Route) {
			$instance = & $routeCfgOrRoute;
		} else if (isset($routeCfgOrRoute['pattern']) && gettype($routeCfgOrRoute['pattern']) == 'array') {
			$instance = \MvcCore\Ext\Router\Lang\Route::GetInstance($routeCfgOrRoute);
		} else {
			$instance = \MvcCore\Route::GetInstance($routeCfgOrRoute);
		}
		if ($prepend) {
			$this->routes = array_merge(array($instance->Name => $instance), $this->routes);
		} else {
			$this->routes[$instance->Name] = & $instance;
		}
		$this->urlRoutes[$instance->Name] = & $instance;
		$this->urlRoutes[$instance->Controller . ':' . $instance->Action] = & $instance;
		return $this;
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
				$routeParams = array(
					'controller'	=>	\MvcCore\Tool::GetDashedFromPascalCase(str_replace(array('_', '\\'), '/', $controllerName)),
					'action'		=>	\MvcCore\Tool::GetDashedFromPascalCase(isset($route->Action)	? $route->Action	: ''),
				);
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
				$routeDefaultParams = isset($route->Params) ? $route->Params : array();
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
	 * Static initialization - called when class is included by autoloader
	 * @return void
	 */
	public static function StaticInit () {
		\MvcCore::AddPreRouteHandler(function (\MvcCore\Request & $request, \MvcCore\Response & $response) {
			\MvcCore::SessionStart();
			static::GetInstance()->processLangVersion($request);
		});
	}

	/**
	 * Complete url by route instance reverse info
	 * @param string $controllerActionOrRouteName
	 * @param array  $params
	 * @return string
	 */
	protected function urlByRoute ($controllerActionOrRouteName, $params) {
		$route = $this->urlRoutes[$controllerActionOrRouteName];
		$allParams = array_merge(
			is_array($route->Params) ? $route->Params : array(), $params
		);
		$lang = '';
		if (isset($allParams[static::LANG_URL_PARAM])) {
			$lang = $allParams[static::LANG_URL_PARAM];
			unset($allParams[static::LANG_URL_PARAM]);
		}
		if (gettype($route->Reverse) == 'array') {
			if (isset($route->Reverse[$lang])) {
				$result = $route->Reverse[$lang];
			} else if (isset($route->Reverse[$this->DefaultLang])) {
				$result = $route->Reverse[$this->DefaultLang];
			} else {
				$result = reset($route->Reverse);
			}
		} else {
			$result = $route->Reverse;
		}
		$result = rtrim($result, '?&');
		foreach ($allParams as $key => $value) {
			$paramKeyReplacement = "{%$key}";
			if (mb_strpos($result, $paramKeyReplacement) === FALSE) {
				$glue = (mb_strpos($result, '?') === FALSE) ? '?' : '&';
				$result .= $glue . http_build_query(array($key => $value));
			} else {
				$result = str_replace($paramKeyReplacement, $value, $result);
			}
		}
		if ($lang) {
			$result = '/' . $lang . $result;
		} else if (gettype($route->Pattern) == 'array') {
			$result = '/' . $this->DefaultLang . $result;
		}
		if (!$this->keepDefaultLangPath) {
			$resultPath = $result;
			$questionMarkPos = mb_strpos($resultPath, '?');
			$anyQueryString = $questionMarkPos !== FALSE;
			if ($anyQueryString) $resultPath = mb_substr($resultPath, 0, $questionMarkPos);
			if (trim($resultPath, '/') == $this->DefaultLang) {
				$result = '/' . ($anyQueryString ? mb_substr($result, $questionMarkPos) : '');
			}
		}
		return $this->request->BasePath . $result;
	}

	/**
	 * Detect language version by configured rules, 
	 * set up detected version to current context, 
	 * into request and into session and redirect if necessary.
	 * @param \MvcCore\Request $request
	 * @return void
	 */
	protected function processLangVersion (\MvcCore\Request & $request) {
		$this->prepareProcessing($request);
		if ($this->stricModeBySession) {
			if ($this->sessionLang) {
				if ($this->switchUriParamLang) {
					$this->setUpDetectedLangAndRedirectIfNecessary($this->switchUriParamLang);
				} else {
					$this->setUpDetectedLangAndRedirectIfNecessary($this->sessionLang);
				}
			} else {
				if ($this->firstRequestStrictlyByUserAgent) {
					$userAgentLang = $this->getDetectedLangByUserAgent();
					if ($userAgentLang) {
						$this->setUpDetectedLangAndRedirectIfNecessary($userAgentLang);
					} else {
						$this->setUpDetectedLangAndRedirectIfNecessary($this->DefaultLang);
					}
				} else {
					$this->setUpDetectedLangAndRedirectIfNecessary($this->requestLang);
				}
			}
		} else {
			if ($this->sessionLang) {
				if ($this->requestLangNotAllowed) $this->requestLang = $this->sessionLang;
				$this->setUpDetectedLangAndRedirectIfNecessary($this->requestLang);
			} else {
				if ($this->firstRequestStrictlyByUserAgent) {
					$userAgentLang = $this->getDetectedLangByUserAgent();
					if ($userAgentLang) {
						$this->setUpDetectedLangAndRedirectIfNecessary($userAgentLang);
					} else {
						$this->setUpDetectedLangAndRedirectIfNecessary($this->requestLang);
					}
				} else {
					$this->setUpDetectedLangAndRedirectIfNecessary($this->requestLang);
				}
			}
		}
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
		$this->session->{static::LANG_URL_PARAM} = $detectedLang;
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
	 * Prepare language processing:
	 * - store request object reference
	 * - store request path into request original path
	 * - try to complete switching param from $_GET
	 * - try to complete request lang
	 * - try to complete session lang
	 * @var void
	 */
	protected function prepareProcessing (\MvcCore\Request & $request) {
		$this->request = & $request;
		// store original path value for later use
		$this->request->OriginalPath = $this->request->Path;
		// add default lang, change values into keys
		$this->setUpAllowedLangs();
		// look into request params if are we just switching any new lang version
		if (isset($_GET[static::LANG_SWITCH_URL_PARAM])) {
			$switchUriParamLang = strtolower($_GET[static::LANG_SWITCH_URL_PARAM]);
			if (isset($this->allowedLangs[$switchUriParamLang])) {
				$this->switchUriParamLang = $switchUriParamLang;
			}
		}
		// get current lang version from url string
		$this->setUpRequestLangFromUrl();
		// set up session object to look inside for something from previous requests
		static::setUpSession();
		// look into session object if there are or not
		// any record about lang from previous request
		if (isset($this->session->{static::LANG_URL_PARAM})) {
			$this->sessionLang = $this->session->{static::LANG_URL_PARAM};
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
	 * Try to set up lang from request, if there is any lang, correct request path,
	 * if thre is no request language, set up default lang.
	 * @return void
	 */
	protected function setUpRequestLangFromUrl () {
		$requestPath = $this->request->Path;
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

		$rawLang = preg_replace("#[^a-z]#", '', strtolower($firstPathElm));

		if (isset($this->allowedLangs[$rawLang])) {
			$this->requestLang = $rawLang;
			$this->request->Path = $secondSlashPos === FALSE ? '/' : mb_substr($this->request->Path, $secondSlashPos);
		} else {
			$this->requestLang = $this->DefaultLang;
			if (mb_strlen($firstPathElm) > 0) {
				$this->requestLangNotAllowed = $firstPathElm;
				if (!$this->allowNonLocalizedRoutes) {
					$this->request->Path = $secondSlashPos === FALSE ? '/' : mb_substr($this->request->Path, $secondSlashPos);
				}
			}
		}
	}

	/**
	 * Add default language into allowed languages
	 * and change all array values into keys,  set all values as 1 (int).
	 * @return void
	 */
	protected function setUpAllowedLangs () {
		$allowedLangs = array();
		foreach ($this->allowedLangs as $lang) {
			$allowedLangs[$lang] = 1;
		}
		$allowedLangs[$this->DefaultLang] = 1;
		$this->allowedLangs = $allowedLangs;
	}

	/**
	 * If session namespace by this class is not initialized,
	 * init namespace and move expiration (to next hour by default)
	 * @return void
	 */
	protected function setUpSession () {
		if (is_null($this->session)) {
			$this->session = \MvcCore\Session::GetNamespace(__CLASS__);
			$this->session->SetExpirationSeconds($this->SessionExpirationSeconds);
		}
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
		$languages = array();
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
					$languages[$match[2]] = array();
				}
				$languages[$match[2]][] = strtolower($match[1]);
			}
		}
		krsort($languages);
		return $languages;
	}
}
Lang::StaticInit();