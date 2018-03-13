<?php
class Log {
	const BASE_PATH = '../../log';
	public static function write($content, $type='common') {
		$fullPath = self::BASE_PATH.'/'.$type.'/';
        if(!is_dir($fullPath)) {
            mkdir($fullPath);
        }
        $fullPath = $fullPath . date('Ym') . '/';
        if(!is_dir($fullPath)) {
            mkdir($fullPath);
        }
        file_put_contents($fullPath.date('d').'.log', $content.' ----- '. date('H:i:s') . PHP_EOL, FILE_APPEND);
	}
}