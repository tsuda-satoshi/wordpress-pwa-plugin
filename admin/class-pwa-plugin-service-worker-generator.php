<?php


class Pwa_Plugin_Service_Worker_Generator {

	private $plugin_root_url;
	private $version = '0.0.1';

	public function __construct( $plugin_root ) {
		$this->plugin_root_url = $plugin_root;
	}

	public function generate( $data ) {
		$initialCaches   = json_encode( $data['initial-caches'] );
		$exclusions = json_encode($data['exclusions']);
		$ttl = $data['ttl'];
		$cacheManagerUrl = $this->plugin_root_url . 'public/js/pwa-plugin-cache-manager.js?' . $this->version;
		$dexieUrl        = $this->plugin_root_url . 'public/js/lib/dexie.min.js?' . $this->version;
		$script          = <<<SCRIPT
const cacheSettings = {
	name: "pwa-plugin-cache",
	initialCaches: ${initialCaches},
	exclusions: ${exclusions},
	ttl : ${ttl}
};

importScripts('${cacheManagerUrl}');
importScripts('${dexieUrl}');
const db = new Dexie('pwa-plugin-cache')
const cacheManager = new CacheManager(this, caches, db, cacheSettings);
cacheManager.initialize();
SCRIPT;

		return $script;
	}
}
