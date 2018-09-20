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

namespace MvcCore\Ext\Routers;

class		Locatization 
extends		\MvcCore\Router
implements	\MvcCore\Ext\Routers\ILocalization,
			\MvcCore\Ext\Routers\IExtended
{
	use \MvcCore\Ext\Routers\Extended;
	use \MvcCore\Ext\Routers\Localization\PropsGettersSetters;
	use \MvcCore\Ext\Routers\Localization\Routing;
	use \MvcCore\Ext\Routers\Localization\UrlCompletion;
	
	public function & Route () {
		$result = FALSE;
		if ($this->preRouteLocalization() === FALSE) return $result;
		$result = parent::Route();
		return $result;
	}
}
