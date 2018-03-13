<?php
namespace assets;
class AppAsset extends \Asset {
	public $js = [];
	public $css = [
		'/css/focus.css',
		'/css/base.css',
		'/css/common.css',
		'/css/ui-dialog.css',
		'/css/poshytip/tip-twitter/tip-twitter.css',
		'/css/poshytip/tip-violet/tip-violet.css',
		'/css/box.css',
		'/css/chosen.min.css',
	];
	public $beginJs = [
		'/js/require.js',
		'/js/config.js'
	];
}