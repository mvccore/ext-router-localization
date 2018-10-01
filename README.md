# MvcCore Extension - Router - Localization

[![Latest Stable Version](https://img.shields.io/badge/Stable-v4.3.1-brightgreen.svg?style=plastic)](https://github.com/mvccore/ext-router-localization/releases)
[![License](https://img.shields.io/badge/Licence-BSD-brightgreen.svg?style=plastic)](https://mvccore.github.io/docs/mvccore/4.0.0/LICENCE.md)
![PHP Version](https://img.shields.io/badge/PHP->=5.3-brightgreen.svg?style=plastic)

MvcCore Router extension to have localized and non-localized URL addresses in your application, very configurable. URL addresses could contain localization by language code or by language and locale code together (`/en/custom/path` or `/en-US/custom/path` or `/any/non-localized/path`). Router works with any http method and with multi or single language route patterns and reverses.

## Outline  
1. [Installation](#user-content-1-installation)  
2. [Features](#user-content-2-features)  
	2.1. [Features - Routing](#user-content-21-features---routing)  
	2.2. [Features - Url Generating](#user-content-22-features---url-generating)  
3. [How It Works](#user-content-3-how-it-works)  
	3.1. [How It Works - Routing](#user-content-31-how-it-works---routing)  
	3.2. [How It Works - Url Completing](#user-content-32-how-it-works---url-completing)  
4. [Usage](#user-content-4-usage)  
	4.1. [Usage - `Bootstrap` Initialization](#user-content-41-usage---bootstrap-initialization)  
	4.2. [Usage - Default Localization](#user-content-42-usage---default-localization)  
	4.3. [Usage - Allowed Localizations](#user-content-43-usage---allowed-localizations)  
	4.4. [Usage - Routes Configuration](#user-content-44-usage---routes-configuration)  
	4.5. [Usage - Allow Non-Localized Routes](#user-content-45-usage---allow non-localized-routes)  
	4.6. [Usage - Detect Localization Only By Language](#user-content-46-usage---detect-localization-only-by-language)  
	4.7. [Usage - Localization Equivalents](#user-content-47-usage---localization-equivalents)  
	4.8. [Usage - Route Records By Language And Locale](#user-content-48-usage---route-records-by-language-and-locale)  
	4.9. [Usage - Redirect To Default And Back In First Request](#user-content-49-usage---redirect-to-default-and-back-in-first-request)  
	4.10. [Usage - Localized URL In Non-Localized Request](#user-content-410-usage---localized-url-in-non-localized-request)  
5. [Advanced Configuration](#user-content-5-advanced-configuration)  
	5.1. [Advanced Configuration - Session Expiration](#user-content-51-advanced-configuration---session-expiration)  
	5.2. [Advanced Configuration - Strict Session Mode](#user-content-52-advanced-configuration---strict-session-mode)  
	5.3. [Advanced Configuration - Routing `GET` Requests Only](#user-content-53-advanced-configuration---routing-get-requests-only)   

## 1. Installation
```shell
composer require mvccore/ext-router-localization
```

## 2. Features

- routes application requests with language and locale in the beginning
- generates url adresses with language and locale in the beginning
- multi language/locale pattern and reverse records in application routes
- routes only allowed languages
- sets recognized or default language into request object
- optionaly recognizes target language by http header `'Accept-Language'`
- optionaly holds language once defined by session
- optionaly keeps path for default language, but normaly redirects user into `'/'` for default language
- optionaly prevent paths for not localized requests

### 2.1. Features - Routing


[go to top](#user-content-outline)

### 2.2. Features - Url Generating


[go to top](#user-content-outline)

## 4. Usage

## 4.1. Usage - `Bootstrap` Initialization
Add this to `Bootstrap.php` or to **very application beginning**, 
before application routing or any other extension configuration
using router for any purposes:
```php
$app = & \MvcCore\Application::GetInstance();
$app->SetRouterClass('\MvcCore\Ext\Routers\Localization');
...
// to get router instance for next configuration:
/** @var $router \MvcCore\Ext\Routers\Localization */
$router = & \MvcCore\Router::GetInstance();
```

[go to top](#user-content-outline)

## 4.2. Usage - Default Localization

Default localization configuration is always required.
```php
$router->SetDefaultLocalization('en-US');
```

[go to top](#user-content-outline)

## 4.3. Usage - Allowed Localizations

Default localization configured above `en-US` is allowed automaticly. 
Any other requested localization (e.g. path like: `/nl-NL/product-lijst` will not be used 
to localize request and path could probably generates an error 404 - Not Found.
```php
$router->SetAllowedLocalizations(/*'en-US', */'en-DE');
```

[go to top](#user-content-outline)

## 4.4. Usage - Routes Configuration

```php
$router->SetRoutes([
	// If you want to add non-localized route, 
	// you can use only definition like this:
	'Admin\Index:Index'		=> '/admin',
	// Localized route with automaticly completed `match` 
	// and `reverse` records from `pattern` record:
	'Front\Product:List'	=> [
		'pattern'				=> [
			'en'					=> "/products-list",
			'de'					=> "/produkte-liste",
		],
	],
	// 
	'Front\Product:Detail'	=> [
		'match'					=> [
			'en'					=> '#^/product/(?<id>\d+)#',
			'de'					=> '#^/produkt/(?<id>\d+)#'
		],
		'reverse'				=> [
			'en'					=> '/product/<id>',
			'de'					=> '/produkt/<id>'
		],
		'defaults'				=> [
			'en'					=> 'red',
			'de'					=> 'rot'
		]
	],
	'Front\Index:Index'		=> [
		'pattern'				=> '/<path>',
		// constraints are never localized
		'constraints'			=> [
			'path' 					=> '[-a-zA-Z0-9_/]*'
		]
	],
]);
```

[go to top](#user-content-outline)

## 5. Configuration

### Allowed languages
For every multilanguage application is necessary to allow more than default language:
```php
\MvcCore\Ext\Routers\Localization::GetInstance()->SetAllowedLangs('en', 'de');
```

### Default language
When request language is not possible to recognize by url address, no possible to recognize by http header `'Accept-Language'` and no language is in session from previous request, default language is used. Default language is `'en'` by default. To configure default language, use:
```php
\MvcCore\Ext\Routers\Localization::GetInstance()->SetDefaultLang('de');
```

### Prevent not localized requests
To prevent all requests for whole application, which have not any language in the beginning to redirect them into default language, you can use:
```php
\MvcCore\Ext\Routers\Localization::GetInstance()->SetAllowNonLocalizedRoutes(FALSE);
```
Non localized routes are allowed by default.


### Choose language in first request strictly by user agent
To choose language in first request by user agent http header `'Accept-Language'`, where is nothing in session yet, you can use:
```php
\MvcCore\Ext\Routers\Localization::GetInstance()->SetAllowNonLocalizedRoutes(TRUE);
```
This options is FALSE by default.

### 5.2. Configuration - Session Expiration
There is possible to change session expiration about detected media
site version value to not recognize media site version every request
where is no prefix in URL, because to process all regular expressions 
in `\Mobile_Detect` library could take some time. By **default** there is **1 hour**. 
You can change it by:
```php
$router->SetSessionExpirationSeconds(
    \MvcCore\Session::EXPIRATION_SECONDS_DAY
);
```

[go to top](#user-content-outline)

### 5.3. Configuration - Strict Session Mode
**In session strict mode, there is not possible to change media site version only by requesting different media site version prefix in URL.**
Stric session mode is router mode when media site version is managed by session value from the first request recognition. 
All requests to different media site version than the version in session are automatically redirected to media site version stored in the session.

Normally, there is possible to get different media site version only by 
requesting different media site version URL prefix. For example - to get 
a different version from `full` version, for example, to get `mobile` version, 
it's only necessary to request application with configured `mobile` prefix 
in URL like this: `/mobile/any/application/request/path`.

In session strict mode, there is possible to change media site version only by special `$_GET` parameter in your media version navigation. For example - 
to get a different version from `full` version, for example, `mobile` version, 
you need to add into query string parameters like this:
`/any/application/request/path?switch_media_version=mobile`
Then, there is changed media site version stored in the session and the user is redirected to the mobile application version with mobile URL prefixes everywhere.

To have this session strict mode, you only need to configure router by:
```php
$router->SetStricModeBySession(TRUE);
```

[go to top](#user-content-outline)

### 5.4. Configuration - Routing `GET` Requests Only
The router manages media site version only for `GET` requests. It means
redirections to the proper version in session strict mode or to redirect
in the first request to recognized media site version. `POST` requests
and other request methods to manage for media site version doesn't make sense. For those requests, you have still media site version record in session and you can use it any time. But to process all
request methods, you can configure the router to do so like this:
```php
$router->SetRouteGetRequestsOnly(FALSE);
```
