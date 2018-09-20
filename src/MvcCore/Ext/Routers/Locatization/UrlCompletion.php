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
	 * Complete url by route instance reverse info
	 * @param string $controllerActionOrRouteName
	 * @param array  $params
	 * @return string
	 */
	protected function urlByRoute ($controllerActionOrRouteName, $params) {
		$route = $this->urlRoutes[$controllerActionOrRouteName];
		$allParams = array_merge(
			is_array($route->Params) ? $route->Params : [], $params
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
				$result .= $glue . http_build_query([$key => $value]);
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
}
