# WP Profile Box

![CI](https://github.com/inc2734/wp-profile-box/workflows/CI/badge.svg)
[![Latest Stable Version](https://poser.pugx.org/inc2734/wp-profile-box/v/stable)](https://packagist.org/packages/inc2734/wp-profile-box)
[![License](https://poser.pugx.org/inc2734/wp-profile-box/license)](https://packagist.org/packages/inc2734/wp-profile-box)


## Install
```
$ composer require inc2734/wp-profile-box
```

## How to use
```
<?php
new \Inc2734\WP_Profile_Box\Bootstrap();

// shortcode
[wp_profile_box title="(Optional)" user_id="(Optional)"]
```

The profile box don't have styles. So you need create CSS for this.

## Development

Use Node.js 24.22.1 (selected by Volta), npm 11.18.0 or later, and Docker.

```sh
npm ci
npm start
npm test
npm stop
```

PHP lint and PHPUnit run in a separate environment configured by `.wp-env.tests.json`
on port 8889. Each check installs its Composer dependencies and stops the test
environment afterward, including when it fails. The development environment remains
running. Use `npm run test:php:stop` to stop an interrupted test environment.

## Support social accounts
* Twitter
* X
* Facebook
* Threads
* Instagram
* Pinterest
* YouTube
* Linkedin
* WordPress
* Tumblr
* amazon
* LINE

## Filter hooks

### inc2734_wp_profile_box_sns_accounts

```
/**
 * Customize sns accounts
 *
 * @param $accounts array
 * @return array
 */
add_filter(
	'inc2734_wp_profile_box_sns_accounts',
	function( $accounts ) {
		return $accounts;
	}
);
```

### inc2734_wp_profile_box_avatar_size

```
/**
 * Customizer avatar size
 *
 * @param $size
 * @return $size
 */
add_filter(
	'inc2734_wp_profile_box_avatar_size',
	function( $size ) {
		return $size;
	}
);
```
