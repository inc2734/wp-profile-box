# WP Awesome Widgets

[![Build Status](https://travis-ci.org/inc2734/wp-profile-box.svg?branch=master)](https://travis-ci.org/inc2734/wp-profile-box)
[![Latest Stable Version](https://poser.pugx.org/inc2734/wp-profile-box/v/stable)](https://packagist.org/packages/inc2734/wp-profile-box)
[![License](https://poser.pugx.org/inc2734/wp-profile-box/license)](https://packagist.org/packages/inc2734/wp-profile-box)


## Install
```
$ composer require inc2734/wp-profile-box
```

## How to use
```
<?php
// When Using composer auto loader
new Inc2734\WP_Profile_Box\Profile_Box();

// When not Using composer auto loader
// include_once( get_theme_file_path( '/vendor/inc2734/wp-profile-box/src/wp-profile-box.php' ) );
// new Inc2734_WP_Profile_Box();

// shortcode
[wp_profile_box user_id="(Optional)"]
```

The profile box don't have styles. So you need create CSS for this.

## Support social accounts
* Twitter
* Facebook
* Instagram
* YouTube
* Linkedin
* WordPress
* Tumblr
