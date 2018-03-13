<?php
namespace tools;
use Yaf\Registry;
class Captcha {
	const SESSION_NAME = 'captcha';
	const TYPE = 'english';
	public static function set() {
		switch (self::TYPE) {
			case 'english':
				self::generateEnglishCode();
				break;
			case 'number':
				self::generateNumberCode();
				break;
			case 'compute':
				self::getComputeCode();
				break;
			default:
				self::generateEnglishCode();
				break;
		}
	}

	public static function check($captcha) {
		return strtoupper($captcha) === self::get();
	}

	public static function get() {
		return Registry::get('session')->get(self::SESSION_NAME);
	}

	private static function setCode($code) {
		Registry::get('session')->set(self::SESSION_NAME, $code);
	}

	/**
	 * 生成数字验证码
	 * @return void      图片
	 */
	private static function generateNumberCode() {
		$num = 4;
		$width = 50;
		$height = 25;

		$code = "";
		for ($i = 0; $i < $num; $i++) {
			$code .= rand(0, 9);
		}
		// $code = '5555';
		//4位验证码也可以用rand(1000,9999)直接生成
		//将生成的验证码写入session，备验证页面使用
		self::setCode($code);
		// var_dump($code);

		//创建图片，定义颜色值
		Header("Content-type: image/PNG");
		$image = imagecreate($width, $height);
		$black = imagecolorallocate($image, 0, 0, 0);
		$gray = imagecolorallocate($image, 200, 200, 200);
		$bgcolor = imagecolorallocate($image, 255, 255, 255);

		imagefill($image, 0, 0, $gray);

		//画边框
		imagerectangle($image, 0, 0, $width-1, $height-1, $black);

		//随机绘制两条虚线，起干扰作用
		$style = [$black, $black, $black, $black, $black, $gray, $gray, $gray, $gray, $gray];
		imagesetstyle($image, $style);
		$y1 = rand(0, $height);
		$y2 = rand(0, $height);
		$y3 = rand(0, $height);
		$y4 = rand(0, $height);
		imageline($image, 0, $y1, $width, $y3, IMG_COLOR_STYLED);
		imageline($image, 0, $y2, $width, $y4, IMG_COLOR_STYLED);

		//在画布上随机生成大量黑点，起干扰作用;
		for ($i = 0; $i < 80; $i++) {
			imagesetpixel($image, rand(0, $width), rand(0, $height), $black);
		}
		//将数字随机显示在画布上,字符的水平间距和位置都按一定波动范围随机生成
		$strx = rand(3, 8);
		for ($i = 0; $i < $num; $i++) {
			$strpos = rand(1, 6);
			imagestring($image, 5, $strx, $strpos, substr($code, $i, 1), $black);
			$strx += rand(8, 12);
		}

		imagepng($image);
		imagedestroy($image);
	}

	private static function generateComputeCode() {
		$w = 100;
		$h = 25;
		$im = imagecreate($w, $h);

		//imagecolorallocate($im, 14, 114, 180); // background color
		$red = imagecolorallocate($im, 255, 0, 0);
		$white = imagecolorallocate($im, 255, 255, 255);

		$num1 = rand(1, 20);
		$num2 = rand(1, 20);

		$total = $num1 + $num2;
		self::setCode($total);

		$gray = imagecolorallocate($im, 118, 151, 199);
		$black = imagecolorallocate($im, mt_rand(0, 100), mt_rand(0, 100), mt_rand(0, 100));
		// $black = imagecolorallocate($im, 0, 0, 0);

		//画背景
		imagefilledrectangle($im, 0, 0, 100, 25, $black);
		//在画布上随机生成大量点，起干扰作用;
		for ($i = 0; $i < 80; $i++) {
			imagesetpixel($im, rand(0, $w), rand(0, $h), $gray);
		}

		imagestring($im, 5, 5, 4, $num1, $red);
		imagestring($im, 5, 30, 3, "+", $red);
		imagestring($im, 5, 45, 4, $num2, $red);
		imagestring($im, 5, 70, 3, "=", $red);
		imagestring($im, 5, 80, 2, "?", $white);

		header("Content-type: image/png");
		imagepng($im);
		imagedestroy($im);
	}

	private static function generateEnglishCode() {
		$str = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$text = '';
		for($i=0;$i<4;$i++){
			$num[$i]=rand(0,25);
			$text .= $str[$num[$i]];
		}

		self::setCode($text);
		$im_x = 85;
		$im_y = 35;
		$im = imagecreatetruecolor($im_x,$im_y);
		$text_c = ImageColorAllocate($im, mt_rand(0,100),mt_rand(0,100),mt_rand(0,100));
		$tmpC0=mt_rand(100,255);
		$tmpC1=mt_rand(100,255);
		$tmpC2=mt_rand(100,255);
		// $buttum_c = ImageColorAllocate($im,$tmpC0,$tmpC1,$tmpC2);
		$buttum_c = ImageColorAllocate($im,255,255,255);
		imagefill($im, 16, 13, $buttum_c);
		$font = 'font/t1.ttf';
		for ($i=0;$i<strlen($text);$i++) {
			$tmp =substr($text,$i,1);
			$array = array(-1,1);
			$p = array_rand($array);
			$an = $array[$p]*mt_rand(1,10);//角度
			$size = 14;
			// 文字偏移量（1+$i*$size, 24）
			imagettftext($im, $size, $an, 1+$i*$size, 24, $text_c, $font, $tmp);
		}

		$distortion_im = imagecreatetruecolor ($im_x, $im_y);

		imagefill($distortion_im, 16, 13, $buttum_c);
		for ( $i=0; $i<$im_x; $i++) {
			for ( $j=0; $j<$im_y; $j++) {
				$rgb = imagecolorat($im, $i , $j);
				if( (int)($i+20+sin($j/$im_y*2*M_PI)*10) <= imagesx($distortion_im)&& (int)($i+20+sin($j/$im_y*2*M_PI)*10) >=0 ) {
					imagesetpixel ($distortion_im, (int)($i+10+sin($j/$im_y*2*M_PI-M_PI*0.1)*4) , $j , $rgb);
				}
			}
		}
		//加入干扰象素;
		$count = 40;//干扰像素的数量
		for($i=0; $i<$count; $i++) {
			$randcolor = ImageColorallocate($distortion_im,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));
			imagesetpixel($distortion_im, mt_rand()%$im_x , mt_rand()%$im_y , $randcolor);
		}

		/*$rand = mt_rand(5,30);
		$rand1 = mt_rand(15,25);
		$rand2 = mt_rand(5,10);
		for ($yy=$rand; $yy<=+$rand+2; $yy++) {
			for ($px=-80;$px<=80;$px=$px+0.1) {
				$x=$px/$rand1;
				if ($x!=0) {
					$y=sin($x);
				}
				$py=$y*$rand2;
				imagesetpixel($distortion_im, $px+80, $py+$yy, $text_c);
			}
		}*/

		//设置文件头;
		Header("Content-type: image/JPEG");

		//以PNG格式将图像输出到浏览器或文件;
		ImagePNG($distortion_im);

		//销毁一图像,释放与image关联的内存;
		ImageDestroy($distortion_im);
		ImageDestroy($im);
	}
}
