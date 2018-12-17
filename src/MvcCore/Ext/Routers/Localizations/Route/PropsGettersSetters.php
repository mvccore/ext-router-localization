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

namespace MvcCore\Ext\Routers\Localizations\Route;

trait PropsGettersSetters
{
	/**
	 * Standard route pattern string(s), but for multiple localizations. Keys could 
	 * be lower case languages or lower case languages + dash + upper-case locales.
	 * @var array
	 */
	protected $patternLocalized = [];

	/**
	 * Standard route match string(s), but for multiple localizations. Keys could 
	 * be lower case languages or lower case languages + dash + upper-case locales.
	 * @var array
	 */
	protected $matchLocalized = [];

	/**
	 * Standard route reverse string(s), but for multiple localizations. Keys could 
	 * be lower case languages or lower case languages + dash + upper-case locales.
	 * @var array
	 */
	protected $reverseLocalized = [];

	/**
	 * Standard route default params values, but for multiple localizations. Keys 
	 * could be lower case languages or lower case languages + dash + upper-case 
	 * locales. Values are normal associative arrays with keys as param names and
	 * values as default param values.
	 * @var array
	 */
	protected $defaultsLocalized = [];

	/**
	 * Standard route constraint params values, but for multiple localizations. Keys 
	 * could be lower case languages or lower case languages + dash + upper-case 
	 * locales. Values are normal associative arrays with keys as param names and
	 * values as regular expression constraints and limitations for specific param.
	 * @var array
	 */
	protected $constraintsLocalized = [];

	/**
	 * Standard route metadata array about all rewrite params, but for multiple 
	 * localizations. Keys could be lower case languages or lower case languages 
	 * + dash + upper-case locales. Values are associative arrays with `\stdClass` 
	 * objects as items with metadata about all rewrite params in localization 
	 * specific `pattern` (or `reverse`) property.
	 * @var array
	 */
	protected $reverseParamsLocalized = [];

	/**
	 * Standard route metadata array about all fixed or variable sections, but 
	 * for multiple localizations. Keys could be lower case languages or lower 
	 * case languages + dash + upper-case locales. Values are number indexed 
	 * arrays with `\stdClass` objects as items with metadata about all fixed or 
	 * variable sections in localization specific `pattern` (or `reverse`) 
	 * property.
	 * @var array
	 */
	protected $reverseSectionsLocalized = [];

	/**
	 * Strings array with all reverse pattern param names. In localized route 
	 * is this property used only for param names, not for whole metadata about
	 * params, because those metadata are localization specific now. So this 
	 * holds only reverse param names. Property is completed in methods:
	 * `initMatchAndReverse()` and `initReverse()`.
	 * Example: 
	 * // For pattern `/products-list/<name>/<color>`
	 * `["name", "color"];`
	 * @var \string[]|NULL
	 */
	#protected $reverseParams	= NULL;


	/**
	 * Get route base pattern to complete match pattern string to match requested 
	 * URL and to complete reverse pattern string to build back an URL address.
	 * 
	 * If any localization is specified and if there is configured any pattern
	 * under given localization, localized pattern string is returned, else 
	 * standard route pattern value is returned.
	 * 
	 * @param string|NULL $localization	Lower case language code, optionally 
	 *									with dash and upper case locale code.
	 * @return string|array|NULL
	 */
	public function GetPattern ($localization = NULL) {
		if (
			$localization !== NULL && 
			array_key_exists($localization, $this->patternLocalized)
		) {
			return $this->patternLocalized[$localization];
		} else {
			return $this->pattern;
		}
	}

	/**
	 * Set route base pattern to complete match pattern string to match requested 
	 * URL and to complete reverse pattern string to build back an URL address.
	 * 
	 * If any localization is specified, pattern string will be stored under 
	 * localization key, else pattern string will be stored for all localizations.
	 * If given pattern value is array, it must be associative array with keys
	 * as localizations and values as pattern strings. Then any second argument
	 * is not necessary to specify.
	 * 
	 * @param string|array $pattern
	 * @param string|NULL  $localization Lower case language code, optionally 
	 *									 with dash and upper case locale code.
	 * @return \MvcCore\Ext\Routers\Localizations\Route|\MvcCore\IRoute
	 */
	public function & SetPattern ($pattern, $localization = NULL) {
		/** @var $this \MvcCore\IRoute */
		if ($localization !== NULL) {
			$this->patternLocalized[$localization] = $pattern;
		} else if (is_array($pattern)) {
			$this->patternLocalized = $pattern;
		} else {
			$this->pattern = $pattern;
		}
		return $this;
	}

	/**
	 * Get route match pattern in raw form (to use it as it is) to match requested
	 * URL. This `match` pattern must have the very same structure and content 
	 * as `reverse` pattern, because there is necessary to complete route flags 
	 * from `reverse` pattern string - to prepare proper regular expression 
	 * subject for this `match`, not just only the request `path`. Because those
	 * flags is not possible to detect from raw regular expression string.
	 * 
	 * If any localization is specified and if there is configured any match
	 * under given localization, localized match string is returned, else 
	 * standard route match value is returned.
	 *
	 * @param string|NULL $localization	Lower case language code, optionally 
	 *									with dash and upper case locale code.
	 * @return string|array|NULL
	 */
	public function GetMatch ($localization = NULL) {
		if (
			$localization !== NULL && 
			array_key_exists($localization, $this->matchLocalized)	
		) {
			return $this->matchLocalized[$localization];
		} else {
			return $this->match;
		}
	}

	/**
	 * Set route match pattern in raw form (to use it as it is) to match requested
	 * URL. This `match` pattern must have the very same structure and content 
	 * as `reverse` pattern, because there is necessary to complete route flags 
	 * from `reverse` pattern string - to prepare proper regular expression 
	 * subject for this `match`, not just only the request `path`. Because those
	 * flags is not possible to detect from raw regular expression string.
	 * 
	 * If any localization is specified, match string will be stored under 
	 * localization key, else match string will be stored for all localizations.
	 * If given match value is array, it must be associative array with keys
	 * as localizations and values as match strings. Then any second argument
	 * is not necessary to specify.
	 * 
	 * @param string|array $match
	 * @param string|NULL  $localization Lower case language code, optionally 
	 *									 with dash and upper case locale code.
	 * @return \MvcCore\Ext\Routers\Localizations\Route|\MvcCore\IRoute
	 */
	public function & SetMatch ($match, $localization = NULL) {
		/** @var $this \MvcCore\IRoute */
		if ($localization !== NULL) {
			$this->matchLocalized[$localization] = $match;
		} else if (is_array($match)) {
			$this->matchLocalized = $match;
		} else {
			$this->match = $match;
		}
		return $this;
	}

	/**
	 * Get route reverse address replacements pattern to build url.
	 * - No regular expression border `#` characters.
	 * - No regular expression characters escaping (`[](){}<>|=+*.!?-/`).
	 * - No start `^` or end `$` regular expression characters.
	 *
	 * If any localization is specified and if there is configured any reverse
	 * under given localization, localized reverse string is returned, else 
	 * standard route reverse value is returned.
	 *
	 * @param string|NULL $localization	Lower case language code, optionally 
	 *									with dash and upper case locale code.
	 * @return string|array|NULL
	 */
	public function GetReverse ($localization = NULL) {
		if (
			$localization !== NULL && 
			array_key_exists($localization, $this->reverseLocalized)
		) {
			return $this->reverseLocalized[$localization];
		}
		return $this->reverse;
	}

	/**
	 * Set route reverse address replacements pattern to build url.
	 * - No regular expression border `#` characters.
	 * - No regular expression characters escaping (`[](){}<>|=+*.!?-/`).
	 * - No start `^` or end `$` regular expression characters.
	 *
	 * If any localization is specified, reverse string will be stored under 
	 * localization key, else reverse string will be stored for all localizations.
	 * If given reverse value is array, it must be associative array with keys
	 * as localizations and values as reverse strings. Then any second argument
	 * is not necessary to specify.
	 * 
	 * @param string|array $reverse
	 * @param string|NULL $localization	Lower case language code, optionally 
	 *									with dash and upper case locale code.
	 * @return \MvcCore\Ext\Routers\Localizations\Route|\MvcCore\IRoute
	 */
	public function & SetReverse ($reverse, $localization = NULL) {
		/** @var $this \MvcCore\IRoute */
		if ($localization !== NULL) {
			$this->reverseLocalized[$localization] = $reverse;
		} else if (is_array($reverse)) {
			$this->reverseLocalized = $reverse;
		} else {
			$this->reverse = $reverse;
		}
		return $this;
	}

	/**
	 * Get route rewrite params default values and also any other query string 
	 * params default values. It could be used for any application request 
	 * param from those application inputs - `$_GET`, `$_POST` or `php://input`.
	 *
	 * If any localization is specified and if there is configured any default
	 * param values as array under given localization, localized default param 
	 * values are returned, else standard route default param values are returned.
	 *
	 * @param string|NULL $localization	Lower case language code, optionally 
	 *									with dash and upper case locale code.
	 * @return array|\array[]
	 */
	public function & GetDefaults ($localization = NULL) {
		if (
			$localization !== NULL && 
			array_key_exists($localization, $this->defaultsLocalized) && 
			is_array($this->defaultsLocalized[$localization])
		) {
			return $this->defaultsLocalized[$localization];
		}
		return $this->defaults;
	}

	/**
	 * Set route rewrite params default values and also any other query string 
	 * params default values. It could be used for any application request 
	 * param from those application inputs - `$_GET`, `$_POST` or `php://input`.
	 *
	 * If any localization is specified, default param values will be stored under 
	 * localization key, else default param values will be stored for all 
	 * localizations. If given default param values is an associative array with
	 * only localization keys, it must be associative array with values as 
	 * associative arrays with localized params default values. Then any second 
	 * argument is not necessary to specify.
	 * 
	 * @param array|\array[] $defaults
	 * @param string|NULL $localization	Lower case language code, optionally 
	 *									with dash and upper case locale code.
	 * @return \MvcCore\Ext\Routers\Localizations\Route|\MvcCore\IRoute
	 */
	public function & SetDefaults ($defaults = [], $localization = NULL) {
		/** @var $this \MvcCore\IRoute */
		if ($localization !== NULL) {
			$this->defaultsLocalized[$localization] = & $defaults;
		} else {
			if ($this->recordIsLocalized($defaults)) {
				$this->defaultsLocalized = $defaults;
			} else {
				$this->defaults = $defaults;	
			}
		}
		return $this;
	}

	/**
	 * Get array with param names and their custom regular expression matching 
	 * rules. Not required, for all rewrite params there is used default 
	 * matching rules from route static properties `defaultDomainConstraint` or
	 * `defaultPathConstraint`. It should be changed to any value. Default value 
	 * is `"[^.]+"` for domain part and `"[^/]+"` for path part.
	 *
	 * If any localization is specified and if there is configured any 
	 * constraints values as array under given localization, localized 
	 * constraints are returned, else standard route constraints are returned.
	 *
	 * @param string|NULL $localization	Lower case language code, optionally 
	 *									with dash and upper case locale code.
	 * @return array|\array[]
	 */
	public function & GetConstraints ($localization = NULL) {
		if (
			$localization !== NULL && 
			array_key_exists($localization, $this->constraintsLocalized) && 
			is_array($this->constraintsLocalized[$localization])
		) {
			return $this->constraintsLocalized[$localization];
		}
		return $this->constraints;
	}

	/**
	 * Set array with param names and their custom regular expression matching 
	 * rules. Not required, for all rewrite params there is used default 
	 * matching rules from route static properties `defaultDomainConstraint` or
	 * `defaultPathConstraint`. It should be changed to any value. Default value 
	 * is `"[^.]+"` for domain part and `"[^/]+"` for path part.
	 *
	 * If any localization is specified, constraints values will be stored under 
	 * localization key, else constraints values will be stored for all 
	 * localizations. If given constraints values is an associative array with
	 * only localization keys, it must be associative array with values as 
	 * associative arrays with params constraints values. Then any second 
	 * argument is not necessary to specify.
	 * 
	 * @param array|\array[] $constraints
	 * @param string|NULL $localization	Lower case language code, optionally 
	 *									with dash and upper case locale code.
	 * @return \MvcCore\Ext\Routers\Localizations\Route|\MvcCore\IRoute
	 */
	public function & SetConstraints ($constraints = [], $localization = NULL) {
		/** @var $this \MvcCore\IRoute */
		if ($localization !== NULL) {
			$this->constraintsLocalized[$localization] = & $constraints;
			if (!isset($this->defaultsLocalized[$localization]))
				$this->defaultsLocalized[$localization] = [];
			$defaults = & $this->defaultsLocalized[$localization];
			foreach ($constraints as $key => $value)
				if (!isset($defaults[$key]))
					$defaults[$key] = NULL;
		} else {
			$localizedConstraints = $this->recordIsLocalized($constraints);
			if ($localization === NULL && $localizedConstraints) {
				$this->constraintsLocalized = & $constraints;
				$defaults = & $this->defaultsLocalized;
				foreach ($constraints as $localization => $constraintsLocalized) {
					if (!isset($this->defaultsLocalized[$localization]))
						$this->defaultsLocalized[$localization] = [];
					$defaults = & $this->defaultsLocalized[$localization];
					foreach ($constraintsLocalized as $key => $value)
						if (!isset($defaults[$key]))
							$defaults[$key] = NULL;
				}
			} else if ($localization === NULL && !$localizedConstraints) {
				$this->constraints = & $constraints;
				$defaults = & $this->defaults;
				foreach ($constraints as $key => $value) {
					if (!isset($defaults[$key]))
						$defaults[$key] = NULL;
				}
			}
		}
		return $this;
	}

	/**
	 * Return only reverse params names as `string`s array.
	 * Example: `["name", "color"];`
	 * @return \string[]|NULL
	 */
	public function GetReverseParams () {
		return $this->reverseParams;
	}

	/**
	 * Get route group name(s) to belongs to. Group name is always first word
	 * parsed from request path. First word is content between two first slashes  
	 * in request path. If group name is `NULL`, route belongs to default group 
	 * and that group is used when no other group matching the request path.
	 * This method could return associative array with localization keys and 
	 * values as group names for specific localizations, if group name is not 
	 * the same for all localizations.
	 * @return string|array|NULL
	 */
	public function GetGroupName () {
		return $this->groupName;
	}

	/**
	 * Set route group name(s) to belongs to. Group name is always first word 
	 * parsed from request path. First word is content between two first slashes 
	 * in request path. If group name is `NULL`, route belongs to default group 
	 * and that group is used when no other group matching the request path.
	 * This method accepts with argument to be a string for group name in the 
	 * same for all localizations or an associative array with localization keys
	 * and values as localization specific group names.
	 * @param string|array|NULL $groupName 
	 * @return \MvcCore\Route|\MvcCore\IRoute
	 */
	public function & SetGroupName ($groupName) {
		/** @var $this \MvcCore\IRoute */
		$this->groupName = $groupName;
		return $this;
	}
}
