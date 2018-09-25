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

trait UrlCompletion
{
	/**
	 * Complete non-absolute, non-localized url by route instance reverse info.
	 * Example:
	 *	Input (`\MvcCore\Route::$reverse`):
	 *		`"/products-list/<name>/<color>"`
	 *	Input ($params):
	 *		`array(
	 *			"name"		=> "cool-product-name",
	 *			"color"		=> "red",
	 *			"variant"	=> array("L", "XL"),
	 *		);`
	 *	Output:
	 *		`/application/base-bath/products-list/cool-product-name/blue?variant[]=L&amp;variant[]=XL"`
	 * @param \MvcCore\Route &$route
	 * @param array $params
	 * @return string
	 */
	public function UrlByRoute (\MvcCore\Interfaces\IRoute & $route, & $params = []) {
		$requestedUrlParams = & $this->GetRequestedUrlParams();
		$localizationParamName = (string) static::LOCATIZATION_URL_PARAM;
		$localizedRoute = $route instanceof \MvcCore\Ext\Routers\Localizations\Route;
		$localization = (
			isset($params[$localizationParamName])
				? $params[$localizationParamName]
				: (isset($requestedUrlParams[$localizationParamName])
					? $requestedUrlParams[$localizationParamName]
					: $this->defaultLocatizationStr)
		);
		unset($params[$localizationParamName]);
		$result = $route->Url(
			$params, $requestedUrlParams, $this->getQueryStringParamsSepatator()
		);
		//x([$localization, $result]);
		if ($localizedRoute) 
			$result = '/' . $localization . $result;
		$questionMarkPos = mb_strpos($result, '?');
		$anyQueryString = $questionMarkPos !== FALSE;
		$resultPath = $anyQueryString 
			? mb_substr($result, 0, $questionMarkPos)
			: $result;
		if (trim($resultPath, '/') === $this->defaultLocatizationStr) 
			$result = '/' . ($anyQueryString ? mb_substr($result, $questionMarkPos) : '');
		return $this->request->GetBasePath() . $result;
	}

	/**
	 * Complete non-absolute, non-localized url with all params in query string.
	 * Example: `"/application/base-bath/index.php?controller=ctrlName&amp;action=actionName&amp;name=cool-product-name&amp;color=blue"`
	 * @param string $controllerActionOrRouteName
	 * @param array  $params
	 * @return string
	 */
	/*public function UrlByQueryString ($controllerActionOrRouteName = 'Index:Index', & $params = []) {
		$toolClass = self::$toolClass;
		list($ctrlPc, $actionPc) = explode(':', $controllerActionOrRouteName);
		$amp = $this->getQueryStringParamsSepatator();
		list($dfltCtrl, $dftlAction) = $this->application->GetDefaultControllerAndActionNames();
		$result = $this->request->GetBasePath();
		if ($params || $ctrlPc !== $dfltCtrl || $actionPc !== $dftlAction) {
			$result .= $this->request->GetScriptName()
				. '?controller=' . $toolClass::GetDashedFromPascalCase($ctrlPc)
				. $amp . 'action=' . $toolClass::GetDashedFromPascalCase($actionPc);
			if ($params) 
				// `http_build_query()` automaticly converts all XSS chars to entities (`< > & " ' &`):
				$result .= $amp . str_replace('%2F', '/', http_build_query($params, '', $amp));
		}
		return $result;
	}*/

	/**
	 * Get all request params - params parsed by route and query string params.
	 * Be carefull, it could contain XSS chars. Use always `htmlspecialchars()`.
	 * @return array
	 */
	public function & GetRequestedUrlParams () {
		if ($this->requestedUrlParams === NULL) {
			// create global `$_GET` array clone:
			$this->requestedUrlParams = array_merge([], $this->request->GetGlobalCollection('get'));
			$requestLocalization = [$this->request->GetLang()];
			$requestLocale = $this->request->GetLocale();
			if ($requestLocale !== NULL)
				$requestLocalization[] = $requestLocale;
			$this->requestedUrlParams[static::LOCATIZATION_URL_PARAM] = implode(
				static::LANG_AND_LOCALE_SEPARATOR, $requestLocalization
			);
		}
		return $this->requestedUrlParams;
	}





	/**
	 * Complete url by route instance reverse info
	 * @param string $controllerActionOrRouteName
	 * @param array  $params
	 * @return string
	 */
	/*protected function urlByRoute ($controllerActionOrRouteName, $params) {
		$route = $this->urlRoutes[$controllerActionOrRouteName];
		$allParams = array_merge(
			is_array($route->Params) ? $route->Params : [], $params
		);
		$lang = '';
		if (isset($allParams[static::LANG_URL_PARAM])) {
			$lang = $allParams[static::LANG_URL_PARAM];
			unset($allParams[static::LANG_URL_PARAM]);
		}
		if (is_array($route->Reverse)) {
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
				$result .= $glue . http_build_query([$key => $value]);
			} else {
				$result = str_replace($paramKeyReplacement, $value, $result);
			}
		}
		if ($lang) {
			$result = '/' . $lang . $result;
		} else if (is_array($route->Pattern)) {
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
	}*/

	/**
	 * Get route non-localized or localized record - 'Pattern' and 'Reverse'
	 * @param \MvcCore\Route $route
	 * @param string $routeRecordKey
	 * @return string
	 */
	/*protected function getRouteLocalizedRecord (\MvcCore\Route & $route, $routeRecordKey = '') {
		if ($route instanceof \MvcCore\Ext\Router\Lang\Route && is_array($route->$routeRecordKey)) {
			$routeRecordKey = $route->$routeRecordKey;
			if (isset($routeRecordKey[$this->Lang])) {
				return $routeRecordKey[$this->Lang];
			} else if (isset($routeRecordKey[$this->DefaultLang])) {
				return $routeRecordKey[$this->DefaultLang];
			}
			return reset($routeRecordKey);
		}
		return $route->$routeRecordKey;
	}*/
}
