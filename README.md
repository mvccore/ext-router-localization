# MvcCore Extension - Language Router

[![Latest Stable Version](https://img.shields.io/badge/Stable-v3.2.0-brightgreen.svg?style=plastic)](https://github.com/mvccore/ext-lang-router/releases)
[![License](https://img.shields.io/badge/Licence-BSD-brightgreen.svg?style=plastic)](https://mvccore.github.io/docs/mvccore/3.0.0/LICENCE.md)
![PHP Version](https://img.shields.io/badge/PHP->=5.3-brightgreen.svg?style=plastic)

MvcCore Router extension to manage your website language version optionaly contained in url address in the beinning.

## Features
- routes application requests with language in the beginning
- generates url adresses with language in the beginning
- multi language pattern and reverse records in application routes
- routes only allowed languages
- sets recognized or default language into request object
- optionaly recognizes target language by http header `'Accept-Language'`
- optionaly holds language once defined by session
- optionaly keeps path for default language, but normaly redirects user into `'/'` for default language
- optionaly prevent paths for not localized requests

## Installation
```shell
composer require mvccore/ext-lang-router
```

## Usage
Add this to **Bootstrap.php** or to **very application beginning**, 
before application routing or any other extension configuration
using router for any purposes:
```php
# patch core class:
MvcCore::GetInstance()->SetRouterClass(MvcCoreExt_LangRouter::class);

# now you can define routes with languages:
MvcCore_Router::GetInstance()
	->SetAllowedLangs('en', 'cs')
	->SetFirstRequestStrictlyByUserAgent()
	->SetRoutes(array(
		'Admin\Index:Index'	=> array(
			'pattern'			=> array(
				'en'				=> "#^/admin#",
				'cs'				=> "#^/sprava#",
			),
		),
		'Front\Index:Index'	=> array(
			'pattern'			=> "#^([a-zA-Z0-9/_\-]*)#",
			'reverse'			=> '{%path}',
		),
	));
```

## Configuration

### Allowed languages
For every multilanguage application is necessary to allow more than default language:
```php
MvcCore_Router::GetInstance()->SetAllowedLangs('en', 'cs');
```

### Default language
When request language is not possible to recognize by url address, no possible to recognize by http header `'Accept-Language'` and no language is in session from previous request, default language is used. Default language is `'en'` by default. To configure default language, use:
```php
MvcCore_Router::GetInstance()->SetDefaultLang('de');
```

### Prevent not localized requests
To prevent all requests for whole application, which have not any language in the beginning to redirect them into default language, you can use:
```php
MvcCore_Router::GetInstance()->SetAllowNonLocalizedRoutes(FALSE);
```
Non localized routes are allowed by default.
```

### Choose language in first request strictly by user agent
To choose language in first request by user agent http header `'Accept-Language'`, where is nothing in session yet, you can use:
```php
MvcCore_Router::GetInstance()->SetAllowNonLocalizedRoutes(TRUE);
```
This options is FALSE by default.

### Strict session mode
To change managing language version into more strict mode, where is not possible to change language only by request application with different language prefix in path like:
```
/de/any/application/request/path
```
but ony where is possible to change language version by 
special $_GET param "switch_lang" like:
```
/de/any/application/request/path?switch_lang=de
```
you need to configure router into strict session mode by:
```php
MvcCoreExt_LangRouter::GetInstance()->SetStricModeBySession();
```

### Session expiration
There is possible to change session expiration about detected language
language version, when it's not possible to do it by url address 
(to not recognize language version every request again by http header `'Accept-Language'`),
you can change it by:
```php
MvcCoreExt_LangRouter::GetInstance()->SetSessionExpirationSeconds(86400); // day
```
But it's not practicly necessary, because if there is necessary to detect
user agent again, it's not so often when the detection process is only 
once per hour - it costs realy nothing per hour. And only a few users stay
on your site more than one hour.
