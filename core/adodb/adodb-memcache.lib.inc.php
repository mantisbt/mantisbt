<?php

// security - hide paths
if (!defined('ADODB_DIR')) die();

global $ADODB_INCLUDED_MEMCACHE;
$ADODB_INCLUDED_MEMCACHE = 1;

/* 

  V5.05 11 July 2008  (c) 2000-2008 John Lim (jlim#natsoft.com). All rights reserved.
  Released under both BSD license and Lesser GPL library license. 
  Whenever there is any discrepancy between the two licenses, 
  the BSD license will take precedence. See License.txt. 
  Set tabs to 4 for best viewing.
  
  Latest version is available at http://adodb.sourceforge.net

Usage:
  
$db = NewADOConnection($driver);
$db->memCache = true; /// should we use memCache instead of caching in files
$db->memCacheHost = array($ip1, $ip2, $ip3);
$db->memCachePort = 11211; /// this is default memCache port
$db->memCacheCompress = false; /// Use 'true' to store the item compressed (uses zlib)

$db->Connect(...);
$db->CacheExecute($sql);
  
  Note the memcache class is shared by all connections, is created during the first call to Connect/PConnect.
  
  Class instance is stored in $ADODB_CACHE
*/

	class ADODB_Cache_MemCache {
		var $createdir = false; // create caching directory structure?
		
		//-----------------------------
		// memcache specific variables
		
		var $hosts;	// array of hosts
		var $port = 11211;
		var $compress = false; // memcache compression with zlib
		
		var $_connected = false;
		var $_memcache = false;
		
		function ADODB_Cache_MemCache(&$obj)
		{
			$this->hosts = $obj->memCacheHost;
			$this->port = $obj->memCachePort;
			$this->compress = $obj->memCacheCompress;
		}
		
		// implement as lazy connection. The connection only occurs on CacheExecute call
		function connect(&$err)
		{
			if (!function_exists('memcache_pconnect')) {
				$err = 'Memcache module PECL extension not found!';
				return false;
			}

			$memcache = new MemCache;
			
			if (!is_array($this->hosts)) $this->hosts = array($hosts);
		
			$failcnt = 0;
			foreach($this->hosts as $host) {
				if (!@$memcache->addServer($host,$this->port,true)) {
					$failcnt += 1;
				}
			}
			if ($failcnt == sizeof($hosts)) {
				$err = 'Can\'t connect to any memcache server';
				return false;
			}
			
			$this->_memcache = $memcache;
			return 0;
		}
		
		function writecache($filename, $contents,$debug, $secs2cache)
		{
			if (!$this->_connected) {
				$err = '';
				if (!$this->connect($err) && $debug) ADOConnection::outp($err);
			}
			if ($this->_memcache) return false;
			
			if (!$this->_memcache->set($filename, $contents, $this->compress, 0)) {
				if ($debug) ADOConnection::outp(" Failed to save data at the memcached server!<br>\n");
				return false;
			}
			
			return true;
		}
		
		function &readcache($filename, &$err, $secs2cache, $rsClass)
		{
			if (!$this->_connected) $this->connect($err);
			if ($this->_memcache) return false;
			
			$rs = $this->_memcache->get($filename);
			if (!$rs) {
				$err = 'Item with such key doesn\'t exists on the memcached server.';
				return $false;
			}
	
			$tdiff = intval($rs->timeCreated+$timeout - time());
			if ($tdiff <= 2) {
				switch($tdiff) {
					case 2: 
						if ((rand() & 15) == 0) {
							$err = "Timeout 2";
							return $false;
						}
						break;
					case 1:
						if ((rand() & 3) == 0) {
							$err = "Timeout 1";
							return $false;
						}
						break;
					default: 
						$err = "Timeout 0";
						return $false;
				}
			}
			return $rs;
		}
		
		function flushall($debug=false)
		{
			if (!$this->_connected) {
				$err = '';
				if (!$this->connect($err) && $debug) ADOConnection::outp($err);
			}
			if ($this->_memcache) return false;
			
			$del = $this->_memcache->flush();
			
			if ($debug) 
				if (!$del) ADOConnection::outp("flushall: failed!<br>\n");
				else ADOConnection::outp("flushall: succeeded!<br>\n");
				
			return $del;
		}
		
		function flushcache($filename, $debug=false)
		{
			$del = $this->_memcache->delete($filename);
			
			if ($debug) 
				if (!$del) ADOConnection::outp("flushcache: $key entry doesn't exist on memcached server!<br>\n");
				else ADOConnection::outp("flushcache: $key entry flushed from memcached server!<br>\n");
				
			return $del;
		}
		
		// not used for memcache
		function createdir($dir, $hash) 
		{
			return true;
		}
	}

?>