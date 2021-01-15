<?php

/**
 * MvcCore
 *
 * This source file is subject to the BSD 3 License
 * For the full copyright and license information, please view
 * the LICENSE.md file that are distributed with this source code.
 *
 * @copyright	Copyright (c) 2016 Tom Flidr (https://github.com/mvccore)
 * @license		https://mvccore.github.io/docs/mvccore/5.0.0/LICENCE.md
 */

namespace MvcCore\Ext\Routers\Localizations;

/**
 * Responsibility - describing request(s) to match and reversely build URL
 * addresses for different languages or for different languages and localizations.
 * - Describing request to match and target it (read more about properties),
 *   for all languages in the same or in different form.
 * - Matching request by given request object, see `\MvcCore\Route::Matches()`
 *   by localization specific matching rules and default params and constraints.
 * - Completing URL address by given params array, see `\MvcCore\Route::Url()`,
 *   by localization specific reverse patterns and default params.
 */
class Route extends \MvcCore\Route {

	use \MvcCore\Ext\Routers\Localizations\Route\PropsGettersSetters;
	//use \MvcCore\Ext\Routers\Localizations\Route\Instancing;
	use \MvcCore\Ext\Routers\Localizations\Route\Matching;
	use \MvcCore\Ext\Routers\Localizations\Route\InternalInits;
	use \MvcCore\Ext\Routers\Localizations\Route\UrlBuilding;

	// PHP 5.4 workaround:
	use \MvcCore\Ext\Routers\Localizations\Route\Instancing {
		\MvcCore\Ext\Routers\Localizations\Route\Instancing::__construct as protected __constructLocalized;
	}
	public function __construct() {
		call_user_func_array(
			[$this, '__constructLocalized'],
			func_get_args()
		);
	}
}
