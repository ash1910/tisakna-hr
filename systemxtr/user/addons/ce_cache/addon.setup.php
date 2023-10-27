<?php

require_once PATH_THIRD.'ce_cache/config.php';

return array(
    'author'         => 'Aaron Waldon - Causing Effect',
    'author_url'     => 'https://www.causingeffect.com/',
    'name'           => 'CE Cache',
    'description'    => 'Fragment caching via db, files, APC, Redis, SQLite, Memcache, and/or Memcached + static file caching',
    'version'        => CE_CACHE_VERSION,
    'namespace'      => 'CE\Ce_cache',
    'docs_url'       => 'http://docs.causingeffect.com/expressionengine/ce-cache',
	'settings_exist' => true
);
