# MvcCore - Extension - Router - Localization

[![Latest Stable Version](https://img.shields.io/badge/Stable-v5.0.0-brightgreen.svg?style=plastic)](https://github.com/mvccore/ext-router-localization/releases)
[![License](https://img.shields.io/badge/License-BSD%203-brightgreen.svg?style=plastic)](https://mvccore.github.io/docs/mvccore/5.0.0/LICENSE.md)
![PHP Version](https://img.shields.io/badge/PHP->=5.4-brightgreen.svg?style=plastic)

MvcCore Router extension to have localized and non-localized URL addresses in your application, very configurable.  
URL addresses could contain localization by language code or by language and locale code together (`/en/custom/path` or  
`/en-US/custom/path` or `/any/non-localized/path`). 
The router works with any HTTP method and with multi or single language route patterns and reverses.

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
    4.5. [Usage - Allow Non-Localized Routes](#user-content-45-usage---allow-non-localized-routes)  
    4.6. [Usage - Detect Localization Only By Language](#user-content-46-usage---detect-localization-only-by-language)  
    4.7. [Usage - Localization Equivalents](#user-content-47-usage---localization-equivalents)  
    4.8. [Usage - Route Records By Language And Locale](#user-content-48-usage---route-records-by-language-and-locale)  
    4.9. [Usage - Redirect To Default And Back In First Request](#user-content-49-usage---redirect-to-default-and-back-in-first-request)  
    4.10. [Usage - Generate Localized URL Or Non-Localized URL](#user-content-410-usage---generate-localized-url-or-non-localized-url)  
    4.11. [Usage - Localized URL In Non-Localized Request](#user-content-411-usage---localized-url-in-non-localized-request)  
5. [Advanced Configuration](#user-content-5-advanced-configuration)  
    5.1. [Advanced Configuration - Session Expiration](#user-content-51-advanced-configuration---session-expiration)  
    5.2. [Advanced Configuration - Strict Session Mode](#user-content-52-advanced-configuration---strict-session-mode)  
    5.3. [Advanced Configuration - Routing `GET` Requests Only](#user-content-53-advanced-configuration---routing-get-requests-only)   

## 1. Installation
```shell
composer require mvccore/ext-router-localization
```

## 2. Features

### 2.1. Features - Routing
- Router works with requests with localization in URL address containing only international lower case language code or containing both codes - international language code, dash and upper case international locale code.
- Router recognizes user device localization settings by HTTP header `Accept-Language` in the first request.
- Router redirects the first request if necessary to URL address with better localization prefix or localization query string param, where is more suitable content for recognized accepting language from HTTP header.
- Router stores recognized device localization in its own session namespace with configurable expiration (to not process localization recognition in every request again and again).
- Router replaces possibly founded localization prefix substring (containing lower case international language code and optionally upper case international locale code) in request path (`$request->GetPath();`) with an empty string. It keeps request path every time in the same form to process routing as usual.
- Router completes `$request->GetLang()` and `$request->GetLocale();` (or `$router->GetLocalization();`) values to use them anywhere in your app.
- Session strict mode for localization version (configurable) to drive application localization strictly by session value.
- Router keeps only one URL address version for the default localization homepage (under slash address - `/` or `/index.php`). Requests to the default localization homepage are redirected to slash URL address automatically (so there is no page for example for default localization `en-US` on address `/en-US/`, this page is automatically redirected to `/`).
- Router accepts only allowed languages or it accepts allowed language and locale code pairs in rewritten URL addresses or it accepts allowed localizations in `localization` query string param, all other values are redirected to default localization.
- Router accepts non-localized routes and localized routes with `pattern` and `defaults` (or with explicit `match` and `reverse`) by language keys or by language and locale keys if necessary.
- Router defines for all non-localized routes localization record from session first into request object and into itself and if there is nothing in session, it defines configured default localization.

[go to top](#user-content-outline)

### 2.2. Features - Url Generating

- Router completes every application URL (or every `GET` URL by configuration) generated by built-in `Url()` method with lower case international language code only at the beginning or with both codes - international language code with dash and with upper case international locale code at the beginning and those prefixed localizations are completed automatically by currently requested localization or by localization given as second argument array with URL params for `Url()` method.
- Router could generate also localized URL addresses under non-localized requests by setting up router by `$router->SetLocalization($lang [,$locale]);`.

[go to top](#user-content-outline)

## 3. How It Works

### 3.1. How It Works - Routing
- Router completes localization from these sources:
    - From requested URL (if there is no localization prefix in URL and homepage is requested, it's completed to default loc.).
    - From session (if there is nothing, it stays on `NULL`).
    - From special `$_GET` param to switch localization in session strict mode (also could be `NULL`).
- Router process pre-route redirections by source data if necessary:
    - If there is allowed value in special `$_GET` switching param:
          - New localization is stored in session and request is redirected to new localization by special switching param.
    - Else if there is no localization in session from any previous request:
          - There is recognized nearest localization by sent `Accept-Language` HTTP header and stored in the session for next requests.
        - There is also completed flag if the detected version is the same as the requested version and flag if the detected version is the best match between allowed localizations in the application and highly prioritized localization in HTTP header.
    - If strict session mode is configured to `FALSE` (by default):
        - If the request is first (nothing is in session from previous requests):
            - If the detected localization match is not the best or match is the best but requested loc. is not recognized (`NULL`)
                - Check if to redirect this unknown first request to default localization homepage by configuration to do so.
                - If request localization is not recognized, redirect the user to localization from HTTP headers.
            - Else route request with requested localization in a standard way later, do not process any redirections.
        - Else route request with requested localization in a standard way later, do not process any redirections.
    - If strict session mode is configured to `TRUE`:
        - If the requested localization is different from the session version:
            - Redirect user to session version.
        - Else route request with requested localization in a standard way later, do not process any redirections.
- Router removes any founded localization URL prefix to process routing for any localization with the same request path.
- Then the router, routes request in a standard way.

[go to top](#user-content-outline)
    
### 3.2. How It Works - Url Completing
- The router generates URL addresses always with the same localization as requested localization version:
    - For addresses without any defined rewrite route, there is added into query string additional param about localization (`&localization=...`).
    - For addresses with defined rewrite route, there is prepended localization URL prefix by router configuration.
- If requested version is default localization, there is not necessary to put into URL addresses any additional data, so for default localization, there is always the same original URL string without any special params or prefixes.
- If you define into build-in `Url()` method into second argument array into params any different localization than requested localization is, there is added into result URL string query param or localization URL prefix by given localization param value.
- If there is configured session strict mode, special `$_GET` switching param is always added automatically.

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

Default localization configured above `en-US` is allowed automatically. 

Any other request (e.g. path like: `/something`) is not localized and if no non-localized route is matched, it's redirected to default localization path like: `/en-US/something`, which could be useful to prevent URL typo mistakes.

But also any other requested localization not allowed (e.g. path like: `/nl-NL/product-lijst`) is not used to localize request and if no non-localized route is matched, request is redirected to default localization path `/en-US/nl-NL/product-lijst`, which could generate error 404 - not found otherwise, so be careful to have everywhere only localizations you want.

```php
$router->SetAllowedLocalizations(/*'en-US', */'en-DE');
```

[go to top](#user-content-outline)

## 4.4. Usage - Routes Configuration

```php
$router->SetRoutes([

    // If you want to add automatically localized route very easily, 
    // you can use only definition like this to define router key with 
    // `Namespace\Controller:Action` and `pattern` as '/something' 
    'Admin\Index:Index'    => '/admin',
    
    // Localized route with automatically completed `match` 
    // and `reverse` records from `pattern` record:
    'Front\Products:List'   => [
        'pattern'          => [
            'en'           => "/products-list[/<page>]",
            'de'           => "/produkte-liste[/<page>]",
        ],
        'defaults'         => ['page' => 1],
        'constraints'      => ['page' => '\d+'],
    ],
    
    // Localized route with explicitly defined `match` and `reverse` 
    // records with also localized `defaults` values:
    'Front\Products:Detail' => [
        'match'            => [
            'en'           => '#^/product/(?<id>\d+)(/(?<color>[a-z]+))?/?#',
            'de'           => '#^/produkt/(?<id>\d+)(/(?<color>[a-z]+))?/?#'
        ],
        'reverse'          => [
            'en'           => '/product/<id>[/<color>]',
            'de'           => '/produkt/<id>[/<color>]'
        ],
        'defaults'         => [
            'en'           => ['color' => 'red'],
            'de'           => ['color' => 'rot'],
        ]
    ],
    
    // Automatically localized route, `pattern` record and later 
    // `match` and `reverse` records are defined for all localizations
    // with the same values `/<path>`, `constraints` are never localized:
    'Front\Index:Index'    => [
        'pattern'          => '/<path>',
        // constraints are never localized:
        'constraints'      => ['path' => '[-a-zA-Z0-9_/]+']
    ],
    
]);
```

[go to top](#user-content-outline)

## 4.5. Usage - Allow Non-Localized Routes
Non-localized routes are allowed by default, so you can route localized and non-localized routes together. But to have all routes strictly localized and to redirect all requests into default localization prefix or into URL with default localization query string param value - all requests - where was not possible to recognize localization by query string param and also where was not possible to recognize localization even by URL prefix, you need to configure router by:
```php
$router->SetAllowNonLocalizedRoutes(FALSE);
```

[go to top](#user-content-outline)

## 4.6. Usage - Detect Localization Only By Language
The router is configured with `TRUE` value by default to detect localization only by language record from `Accept-Language` HTTP header record, not strictly together with locale code. The parsed international language code is enough by default to choose final target application localization. There will be chosen first localization in the allowed list with detected language.  
If the value is `FALSE`, then there is necessary to send into application in `Accept-Language` HTTP header international language code together with international locale code with the only same combination which application has configured in allowed localizations only:
```php
$router->SetDetectLocalizationOnlyByLang(FALSE);
```

[go to top](#user-content-outline)

## 4.7. Usage - Localization Equivalents
You can define a list of localization equivalents used in localization detection by HTTP header `Accept-Language` parsed in the first request. It could be used for language very similar countries like Ukraine & Russia, Czech & Slovakia ...
Keys in this array is target localization, values are an array with target localization equivalents.

```php
$router->->SetLocalizationEquivalents([
    // Browsers preferring UK, USA or Canada are considered as `en-US` locale to send
    'en-US'    => ['en-GB', 'en-CA', 'en-AU'],
    // Browsers preferring Slovak are considered as `cs-CZ` locale to send
    'cs-CZ'    => ['sk-SK'],    // Czech and Slovak
]);
...
$router->AddLocalizationEquivalents(/*... same param syntax as method above*/);
```

[go to top](#user-content-outline)

## 4.8. Usage - Route Records By Language And Locale
 If you define `TRUE` value (default is `FALSE`), defined route records like `pattern`, `match`, `reverse` or `defaults` has to be defined by international language code and international locale code, not only by language code. This option is very rare if different locales have different naming for URL strings.
```php
$router->SetRouteRecordsByLanguageAndLocale(TRUE);
```

[go to top](#user-content-outline)


## 4.9. Usage - Redirect To Default And Back In First Request
If `TRUE` (`FALSE` by default), if request is historically first (by session), if localization by HTTP headers is not the best match or if it is the best match but requested localization is not the same as HTTP headers prefer, redirect to default localization homepage with `source_url` query string param:
```php
$router->SetRedirectFirstRequestToDefault(TRUE);
```

[go to top](#user-content-outline)

## 4.10. Usage - Generate Localized URL Or Non-Localized URL

If you put into `Url()` method as first param localized route name, there is generated localized URL automatically:
```php
// somewhere in Bootstrap.php:
$router
    ->SetDefaultLocalization('en-US')
    ->SetAllowedLocalizations('de-DE')
    ->SetRouteRecordsByLanguageAndLocale(FALSE)
    ->AddRoutes([
        'Front\Product:Detail' => [
            'pattern'              => [
                'en'               '/product/<id>',
                'de'               '/produkt/<id>',
            ],
            'constraints'          => [
                'id'               => '\d+',
            ]
        ]
    ]);
...
// somewhere in template or in controller (if router has matched localization `de-DE`):
$this->Url('Front\Product:Detail', [
    'id' => 50
]);
// will return: `/de-DE/produkt/50`
```

If there is put a non-localized route name, returned is non-localized URL.
```php
// somewhere in Bootstrap.php:
$router->AddRoutes([
    'admin' => [
        'pattern'              => '/admin/<controller>/<action>[/<id>]',
        'constraints'          => [
            'controller'       => '-a-z0-9',
            'action'           => '-a-z0-9',
            'id'               => '\d+',
        ]
    ]
]);
...
// somewhere in template or in controller (if router has matched any localization):
$this->Url('admin', [
    'controller' => 'products', 
    'action'     => 'update', 
    'id'         => 50
]);
// will return: `/admin/products/update/50`
```

[go to top](#user-content-outline)

## 4.11. Usage - Localized URL In Non-Localized Request
If request is routed on any non-localized route and request object has some strange localization from session or default localization (if there was nothing in session), you still could generate differently localized URL addresses, for example for email messages in CRON scripts like so:
```php
$router->SetLocalization('de', 'DE');
...
// anywhere you have $router instance:
$router->Url('Front\Product:Detail', ['id' => 50]);    // `/de-DE/produkt/50`
...
// or somewhere in template or in controller:
$this->Url('Front\Product:Detail', ['id' => 50]);    // `/de-DE/produkt/50`
```

[go to top](#user-content-outline)

## 5. Advanced Configuration

### 5.1. Advanced Configuration - Session Expiration
There is possible to change session expiration about detected localization value to not recognize localization every request where is no prefix in URL, 
because to process parsing `Accept-Language` HTTP header could take some time. 
By **default** there is **1 hour**. You can change it by:
```php
$router->SetSessionExpirationSeconds(
    \MvcCore\Session::EXPIRATION_SECONDS_DAY
);
```

[go to top](#user-content-outline)

### 5.2. Advanced Configuration - Strict Session Mode
**In session strict mode, there is not possible to change localization only by requesting different localization prefix in URL.**
Strict session mode is router mode when localization is managed by session value from the first request HTTP header recognition. 
All requests to different localization version than the version in session are automatically redirected to localization version stored in the session.

Normally, there is possible to get different localization version only by 
requesting different localization version URL prefix. For example - to get 
a different version from `en-US` localization, for example, to get `de-DE` localization, 
it's only necessary to request application with configured `de-DE` allowed localization 
in URL like this: `/de-DE/any/application/request/path`.

In session strict mode, there is possible to change localization version only by special `$_GET` parameter in your media version navigation. For example - 
to get a different localization from `en-US`, for example, `de-DE` localization, 
you need to add into query string parameters like this:
`/any/application/request/path?switch_localization=de-DE`
Then, there is changed localization version stored in the session and the user is redirected to the new localization version with `de-DE` URL prefixes everywhere.

To have this session strict mode, you only need to configure router by:
```php
$router->SetStricModeBySession(TRUE);
```

[go to top](#user-content-outline)

### 5.3. Advanced Configuration - Routing `GET` Requests Only
The router manages the localization version only for `GET` requests. It means
redirections to the proper version in session strict mode or to redirect
in the first request to recognized localization version. `POST` requests
and other request methods to manage for localization version doesn't make sense. For those requests, you have still localization version record in session and you can use it any time. But to process all
request methods, you can configure the router to do so like this:
```php
$router->SetRouteGetRequestsOnly(FALSE);
```

[go to top](#user-content-outline)
