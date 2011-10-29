# Wordpress to Jekyll

A simple script that will convert posts from a Wordpress export into Jekyll friendly post files.

It will pull out the title and tags from posts, along with the main post content and then save them into the correct timestamp named files based on the post date and post title slug.

This will ensure if you're moving from Wordpress to Jekyll, your old links will work so long as you include the correct permalink structure in your `_config.yml` file.

**Requires PHP 5.3**

## Install

	git clone git://github.com/davidwinter/wordpress-to-jekyll.git
	cd wordpress-to-jekyll/
	git submodule update --init --recursive

Download your Wordpress export to the same directory, and then rename it to `export.xml`, then run:

	php wordpress-to-jekyll.php

This will create a `posts` directory with all your converted files.
