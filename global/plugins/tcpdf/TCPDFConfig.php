<?php 
namespace plugins\tcpdf;
class TCPDFConfig {
	public static $params = [
		/**
		 * Deafult image logo used be the default Header() method.
		 * Please set here your own logo or an empty string to disable it.
		 */
		'PDF_HEADER_LOGO'=>'images/xwsdlogo.jpg',

		/**
		 * Header logo image width in user units.
		 */
		//'PDF_HEADER_LOGO_WIDTH'=>0,

		/**
		 * Cache directory for temporary files (full path).
		 */
		'K_PATH_CACHE'=>'/tmp/',

		/**
		 * Generic name for a blank image.
		 */
		'K_BLANK_IMAGE'=>'_blank.png',

		/**
		 * Page format.
		 */
		'PDF_PAGE_FORMAT'=>'A4',

		/**
		 * Page orientation (P=portrait,L=landscape).
		 */
		'PDF_PAGE_ORIENTATION'=>'P',

		/**
		 * Document creator.
		 */
		'PDF_CREATOR'=>'TCPDF',

		/**
		 * Document author.
		 */
		'PDF_AUTHOR'=>'TCPDF',

		/**
		 * Header title.
		 */
		'PDF_HEADER_TITLE'=>'TCPDF Example',

		/**
		 * Header description string.
		 */
		'PDF_HEADER_STRING'=>"by Nicola Asuni - Tecnick.com\nwww.tcpdf.org",

		/**
		 * Document unit of measure [pt=point,mm=millimeter,cm=centimeter,in=inch].
		 */
		'PDF_UNIT'=>'mm',

		/**
		 * Header margin.
		 */
		'PDF_MARGIN_HEADER'=>5,

		/**
		 * Footer margin.
		 */
		'PDF_MARGIN_FOOTER'=>10,

		/**
		 * Top margin.
		 */
		'PDF_MARGIN_TOP'=>27,

		/**
		 * Bottom margin.
		 */
		'PDF_MARGIN_BOTTOM'=>25,

		/**
		 * Left margin.
		 */
		'PDF_MARGIN_LEFT'=>15,

		/**
		 * Right margin.
		 */
		'PDF_MARGIN_RIGHT'=>15,

		/**
		 * Default main font name.
		 */
		'PDF_FONT_NAME_MAIN'=>'helvetica',

		/**
		 * Default main font size.
		 */
		'PDF_FONT_SIZE_MAIN'=>10,

		/**
		 * Default data font name.
		 */
		'PDF_FONT_NAME_DATA'=>'helvetica',

		/**
		 * Default data font size.
		 */
		'PDF_FONT_SIZE_DATA'=>8,

		/**
		 * Default monospaced font name.
		 */
		'PDF_FONT_MONOSPACED'=>'courier',

		/**
		 * Ratio used to adjust the conversion of pixels to user units.
		 */
		'PDF_IMAGE_SCALE_RATIO'=>1.25,

		/**
		 * Magnification factor for titles.
		 */
		'HEAD_MAGNIFICATION'=>1.1,

		/**
		 * Height of cell respect font height.
		 */
		'K_CELL_HEIGHT_RATIO'=>1.25,

		/**
		 * Title magnification respect main font size.
		 */
		'K_TITLE_MAGNIFICATION'=>1.3,

		/**
		 * Reduction factor for small font.
		 */
		'K_SMALL_RATIO'=>1.67,

		/**
		 * Set to true to enable the special procedure used to avoid the overlappind of symbols on Thai language.
		 */
		'K_THAI_TOPCHARS'=>true,

		/**
		 * If true allows to call TCPDF methods using HTML syntax
		 * IMPORTANT: For security reason, disable this feature if you are printing user HTML content.
		 */
		'K_TCPDF_CALLS_IN_HTML'=>false,

		/**
		 * If true and PHP version is greater than 5=>then the Error() method throw new exception instead of terminating the execution.
		 */
		'K_TCPDF_THROW_EXCEPTION_ERROR'=>false,

		/**
		 * Default timezone for datetime functions
		 */
		'K_TIMEZONE'=>'UTC',
	];

	public static function get($name) {
		return self::$params[$name];
	}
}