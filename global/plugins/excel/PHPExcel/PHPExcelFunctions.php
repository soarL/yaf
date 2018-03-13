<?php
namespace plugins\excel\PHPExcel;

use plugins\excel\PHPExcel\Calculation\CalFunction;

class PHPExcelFunctions {
	var $list = array(
		'ABS'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>'abs',
		'argumentCount'=>'1'
		),
		'ACCRINT'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::ACCRINT',
		'argumentCount'=>'4-7'
		),
		'ACCRINTM'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::ACCRINTM',
		'argumentCount'=>'3-5'
		),
		'ACOS'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>'acos',
		'argumentCount'=>'1'
		),
		'ACOSH'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>'acosh',
		'argumentCount'=>'1'
		),
		'ADDRESS'=> array('category'=>CalFunction::CATEGORY_LOOKUP_AND_REFERENCE,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\LookupRef::CELL_ADDRESS',
		'argumentCount'=>'2-5'
		),
		'AMORDEGRC'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::AMORDEGRC',
		'argumentCount'=>'6,7'
		),
		'AMORLINC'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::AMORLINC',
		'argumentCount'=>'6,7'
		),
		'AND'=> array('category'=>CalFunction::CATEGORY_LOGICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Logical::LOGICAL_AND',
		'argumentCount'=>'1+'
		),
		'AREAS'=> array('category'=>CalFunction::CATEGORY_LOOKUP_AND_REFERENCE,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::DUMMY',
		'argumentCount'=>'1'
		),
		'ASC'=> array('category'=>CalFunction::CATEGORY_TEXT_AND_DATA,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::DUMMY',
		'argumentCount'=>'1'
		),
		'ASIN'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>'asin',
		'argumentCount'=>'1'
		),
		'ASINH'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>'asinh',
		'argumentCount'=>'1'
		),
		'ATAN'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>'atan',
		'argumentCount'=>'1'
		),
		'ATAN2'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\MathTrig::ATAN2',
		'argumentCount'=>'2'
		),
		'ATANH'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>'atanh',
		'argumentCount'=>'1'
		),
		'AVEDEV'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::AVEDEV',
		'argumentCount'=>'1+'
		),
		'AVERAGE'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::AVERAGE',
		'argumentCount'=>'1+'
		),
		'AVERAGEA'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::AVERAGEA',
		'argumentCount'=>'1+'
		),
		'AVERAGEIF'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::AVERAGEIF',
		'argumentCount'=>'2,3'
		),
		'AVERAGEIFS'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::DUMMY',
		'argumentCount'=>'3+'
		),
		'BAHTTEXT'=> array('category'=>CalFunction::CATEGORY_TEXT_AND_DATA,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::DUMMY',
		'argumentCount'=>'1'
		),
		'BESSELI'=> array('category'=>CalFunction::CATEGORY_ENGINEERING,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Engineering::BESSELI',
		'argumentCount'=>'2'
		),
		'BESSELJ'=> array('category'=>CalFunction::CATEGORY_ENGINEERING,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Engineering::BESSELJ',
		'argumentCount'=>'2'
		),
		'BESSELK'=> array('category'=>CalFunction::CATEGORY_ENGINEERING,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Engineering::BESSELK',
		'argumentCount'=>'2'
		),
		'BESSELY'=> array('category'=>CalFunction::CATEGORY_ENGINEERING,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Engineering::BESSELY',
		'argumentCount'=>'2'
		),
		'BETADIST'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::BETADIST',
		'argumentCount'=>'3-5'
		),
		'BETAINV'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::BETAINV',
		'argumentCount'=>'3-5'
		),
		'BIN2DEC'=> array('category'=>CalFunction::CATEGORY_ENGINEERING,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Engineering::BINTODEC',
		'argumentCount'=>'1'
		),
		'BIN2HEX'=> array('category'=>CalFunction::CATEGORY_ENGINEERING,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Engineering::BINTOHEX',
		'argumentCount'=>'1,2'
		),
		'BIN2OCT'=> array('category'=>CalFunction::CATEGORY_ENGINEERING,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Engineering::BINTOOCT',
		'argumentCount'=>'1,2'
		),
		'BINOMDIST'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::BINOMDIST',
		'argumentCount'=>'4'
		),
		'CEILING'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\MathTrig::CEILING',
		'argumentCount'=>'2'
		),
		'CELL'=> array('category'=>CalFunction::CATEGORY_INFORMATION,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::DUMMY',
		'argumentCount'=>'1,2'
		),
		'CHAR'=> array('category'=>CalFunction::CATEGORY_TEXT_AND_DATA,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\TextData::CHARACTER',
		'argumentCount'=>'1'
		),
		'CHIDIST'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::CHIDIST',
		'argumentCount'=>'2'
		),
		'CHIINV'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::CHIINV',
		'argumentCount'=>'2'
		),
		'CHITEST'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::DUMMY',
		'argumentCount'=>'2'
		),
		'CHOOSE'=> array('category'=>CalFunction::CATEGORY_LOOKUP_AND_REFERENCE,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\LookupRef::CHOOSE',
		'argumentCount'=>'2+'
		),
		'CLEAN'=> array('category'=>CalFunction::CATEGORY_TEXT_AND_DATA,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\TextData::TRIMNONPRINTABLE',
		'argumentCount'=>'1'
		),
		'CODE'=> array('category'=>CalFunction::CATEGORY_TEXT_AND_DATA,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\TextData::ASCIICODE',
		'argumentCount'=>'1'
		),
		'COLUMN'=> array('category'=>CalFunction::CATEGORY_LOOKUP_AND_REFERENCE,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\LookupRef::COLUMN',
		'argumentCount'=>'-1',
		'passByReference'=>array(TRUE)
		),
		'COLUMNS'=> array('category'=>CalFunction::CATEGORY_LOOKUP_AND_REFERENCE,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\LookupRef::COLUMNS',
		'argumentCount'=>'1'
		),
		'COMBIN'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\MathTrig::COMBIN',
		'argumentCount'=>'2'
		),
		'COMPLEX'=> array('category'=>CalFunction::CATEGORY_ENGINEERING,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Engineering::COMPLEX',
		'argumentCount'=>'2,3'
		),
		'CONCATENATE'=> array('category'=>CalFunction::CATEGORY_TEXT_AND_DATA,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\TextData::CONCATENATE',
		'argumentCount'=>'1+'
		),
		'CONFIDENCE'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::CONFIDENCE',
		'argumentCount'=>'3'
		),
		'CONVERT'=> array('category'=>CalFunction::CATEGORY_ENGINEERING,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Engineering::CONVERTUOM',
		'argumentCount'=>'3'
		),
		'CORREL'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::CORREL',
		'argumentCount'=>'2'
		),
		'COS'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>'cos',
		'argumentCount'=>'1'
		),
		'COSH'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>'cosh',
		'argumentCount'=>'1'
		),
		'COUNT'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::COUNT',
		'argumentCount'=>'1+'
		),
		'COUNTA'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::COUNTA',
		'argumentCount'=>'1+'
		),
		'COUNTBLANK'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::COUNTBLANK',
		'argumentCount'=>'1'
		),
		'COUNTIF'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::COUNTIF',
		'argumentCount'=>'2'
		),
		'COUNTIFS'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::DUMMY',
		'argumentCount'=>'2'
		),
		'COUPDAYBS'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::COUPDAYBS',
		'argumentCount'=>'3,4'
		),
		'COUPDAYS'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::COUPDAYS',
		'argumentCount'=>'3,4'
		),
		'COUPDAYSNC'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::COUPDAYSNC',
		'argumentCount'=>'3,4'
		),
		'COUPNCD'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::COUPNCD',
		'argumentCount'=>'3,4'
		),
		'COUPNUM'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::COUPNUM',
		'argumentCount'=>'3,4'
		),
		'COUPPCD'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::COUPPCD',
		'argumentCount'=>'3,4'
		),
		'COVAR'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::COVAR',
		'argumentCount'=>'2'
		),
		'CRITBINOM'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::CRITBINOM',
		'argumentCount'=>'3'
		),
		'CUBEKPIMEMBER'=> array('category'=>CalFunction::CATEGORY_CUBE,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::DUMMY',
		'argumentCount'=>'?'
		),
		'CUBEMEMBER'=> array('category'=>CalFunction::CATEGORY_CUBE,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::DUMMY',
		'argumentCount'=>'?'
		),
		'CUBEMEMBERPROPERTY'=> array('category'=>CalFunction::CATEGORY_CUBE,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::DUMMY',
		'argumentCount'=>'?'
		),
		'CUBERANKEDMEMBER'=> array('category'=>CalFunction::CATEGORY_CUBE,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::DUMMY',
		'argumentCount'=>'?'
		),
		'CUBESET'=> array('category'=>CalFunction::CATEGORY_CUBE,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::DUMMY',
		'argumentCount'=>'?'
		),
		'CUBESETCOUNT'=> array('category'=>CalFunction::CATEGORY_CUBE,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::DUMMY',
		'argumentCount'=>'?'
		),
		'CUBEVALUE'=> array('category'=>CalFunction::CATEGORY_CUBE,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::DUMMY',
		'argumentCount'=>'?'
		),
		'CUMIPMT'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::CUMIPMT',
		'argumentCount'=>'6'
		),
		'CUMPRINC'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::CUMPRINC',
		'argumentCount'=>'6'
		),
		'DATE'=> array('category'=>CalFunction::CATEGORY_DATE_AND_TIME,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\DateTime::DATE',
		'argumentCount'=>'3'
		),
		'DATEDIF'=> array('category'=>CalFunction::CATEGORY_DATE_AND_TIME,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\DateTime::DATEDIF',
		'argumentCount'=>'2,3'
		),
		'DATEVALUE'=> array('category'=>CalFunction::CATEGORY_DATE_AND_TIME,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\DateTime::DATEVALUE',
		'argumentCount'=>'1'
		),
		'DAVERAGE'=> array('category'=>CalFunction::CATEGORY_DATABASE,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Database::DAVERAGE',
		'argumentCount'=>'3'
		),
		'DAY'=> array('category'=>CalFunction::CATEGORY_DATE_AND_TIME,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\DateTime::DAYOFMONTH',
		'argumentCount'=>'1'
		),
		'DAYS360'=> array('category'=>CalFunction::CATEGORY_DATE_AND_TIME,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\DateTime::DAYS360',
		'argumentCount'=>'2,3'
		),
		'DB'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::DB',
		'argumentCount'=>'4,5'
		),
		'DCOUNT'=> array('category'=>CalFunction::CATEGORY_DATABASE,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Database::DCOUNT',
		'argumentCount'=>'3'
		),
		'DCOUNTA'=> array('category'=>CalFunction::CATEGORY_DATABASE,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Database::DCOUNTA',
		'argumentCount'=>'3'
		),
		'DDB'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::DDB',
		'argumentCount'=>'4,5'
		),
		'DEC2BIN'=> array('category'=>CalFunction::CATEGORY_ENGINEERING,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Engineering::DECTOBIN',
		'argumentCount'=>'1,2'
		),
		'DEC2HEX'=> array('category'=>CalFunction::CATEGORY_ENGINEERING,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Engineering::DECTOHEX',
		'argumentCount'=>'1,2'
		),
		'DEC2OCT'=> array('category'=>CalFunction::CATEGORY_ENGINEERING,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Engineering::DECTOOCT',
		'argumentCount'=>'1,2'
		),
		'DEGREES'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>'rad2deg',
		'argumentCount'=>'1'
		),
		'DELTA'=> array('category'=>CalFunction::CATEGORY_ENGINEERING,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Engineering::DELTA',
		'argumentCount'=>'1,2'
		),
		'DEVSQ'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::DEVSQ',
		'argumentCount'=>'1+'
		),
		'DGET'=> array('category'=>CalFunction::CATEGORY_DATABASE,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Database::DGET',
		'argumentCount'=>'3'
		),
		'DISC'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::DISC',
		'argumentCount'=>'4,5'
		),
		'DMAX'=> array('category'=>CalFunction::CATEGORY_DATABASE,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Database::DMAX',
		'argumentCount'=>'3'
		),
		'DMIN'=> array('category'=>CalFunction::CATEGORY_DATABASE,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Database::DMIN',
		'argumentCount'=>'3'
		),
		'DOLLAR'=> array('category'=>CalFunction::CATEGORY_TEXT_AND_DATA,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\TextData::DOLLAR',
		'argumentCount'=>'1,2'
		),
		'DOLLARDE'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::DOLLARDE',
		'argumentCount'=>'2'
		),
		'DOLLARFR'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::DOLLARFR',
		'argumentCount'=>'2'
		),
		'DPRODUCT'=> array('category'=>CalFunction::CATEGORY_DATABASE,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Database::DPRODUCT',
		'argumentCount'=>'3'
		),
		'DSTDEV'=> array('category'=>CalFunction::CATEGORY_DATABASE,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Database::DSTDEV',
		'argumentCount'=>'3'
		),
		'DSTDEVP'=> array('category'=>CalFunction::CATEGORY_DATABASE,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Database::DSTDEVP',
		'argumentCount'=>'3'
		),
		'DSUM'=> array('category'=>CalFunction::CATEGORY_DATABASE,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Database::DSUM',
		'argumentCount'=>'3'
		),
		'DURATION'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::DUMMY',
		'argumentCount'=>'5,6'
		),
		'DVAR'=> array('category'=>CalFunction::CATEGORY_DATABASE,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Database::DVAR',
		'argumentCount'=>'3'
		),
		'DVARP'=> array('category'=>CalFunction::CATEGORY_DATABASE,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Database::DVARP',
		'argumentCount'=>'3'
		),
		'EDATE'=> array('category'=>CalFunction::CATEGORY_DATE_AND_TIME,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\DateTime::EDATE',
		'argumentCount'=>'2'
		),
		'EFFECT'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::EFFECT',
		'argumentCount'=>'2'
		),
		'EOMONTH'=> array('category'=>CalFunction::CATEGORY_DATE_AND_TIME,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\DateTime::EOMONTH',
		'argumentCount'=>'2'
		),
		'ERF'=> array('category'=>CalFunction::CATEGORY_ENGINEERING,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Engineering::ERF',
		'argumentCount'=>'1,2'
		),
		'ERFC'=> array('category'=>CalFunction::CATEGORY_ENGINEERING,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Engineering::ERFC',
		'argumentCount'=>'1'
		),
		'ERROR.TYPE'=> array('category'=>CalFunction::CATEGORY_INFORMATION,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::ERROR_TYPE',
		'argumentCount'=>'1'
		),
		'EVEN'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\MathTrig::EVEN',
		'argumentCount'=>'1'
		),
		'EXACT'=> array('category'=>CalFunction::CATEGORY_TEXT_AND_DATA,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::DUMMY',
		'argumentCount'=>'2'
		),
		'EXP'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>'exp',
		'argumentCount'=>'1'
		),
		'EXPONDIST'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::EXPONDIST',
		'argumentCount'=>'3'
		),
		'FACT'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\MathTrig::FACT',
		'argumentCount'=>'1'
		),
		'FACTDOUBLE'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\MathTrig::FACTDOUBLE',
		'argumentCount'=>'1'
		),
		'FALSE'=> array('category'=>CalFunction::CATEGORY_LOGICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Logical::FALSE',
		'argumentCount'=>'0'
		),
		'FDIST'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::DUMMY',
		'argumentCount'=>'3'
		),
		'FIND'=> array('category'=>CalFunction::CATEGORY_TEXT_AND_DATA,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\TextData::SEARCHSENSITIVE',
		'argumentCount'=>'2,3'
		),
		'FINDB'=> array('category'=>CalFunction::CATEGORY_TEXT_AND_DATA,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\TextData::SEARCHSENSITIVE',
		'argumentCount'=>'2,3'
		),
		'FINV'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::DUMMY',
		'argumentCount'=>'3'
		),
		'FISHER'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::FISHER',
		'argumentCount'=>'1'
		),
		'FISHERINV'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::FISHERINV',
		'argumentCount'=>'1'
		),
		'FIXED'=> array('category'=>CalFunction::CATEGORY_TEXT_AND_DATA,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\TextData::FIXEDFORMAT',
		'argumentCount'=>'1-3'
		),
		'FLOOR'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\MathTrig::FLOOR',
		'argumentCount'=>'2'
		),
		'FORECAST'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::FORECAST',
		'argumentCount'=>'3'
		),
		'FREQUENCY'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::DUMMY',
		'argumentCount'=>'2'
		),
		'FTEST'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::DUMMY',
		'argumentCount'=>'2'
		),
		'FV'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::FV',
		'argumentCount'=>'3-5'
		),
		'FVSCHEDULE'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::FVSCHEDULE',
		'argumentCount'=>'2'
		),
		'GAMMADIST'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::GAMMADIST',
		'argumentCount'=>'4'
		),
		'GAMMAINV'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::GAMMAINV',
		'argumentCount'=>'3'
		),
		'GAMMALN'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::GAMMALN',
		'argumentCount'=>'1'
		),
		'GCD'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\MathTrig::GCD',
		'argumentCount'=>'1+'
		),
		'GEOMEAN'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::GEOMEAN',
		'argumentCount'=>'1+'
		),
		'GESTEP'=> array('category'=>CalFunction::CATEGORY_ENGINEERING,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Engineering::GESTEP',
		'argumentCount'=>'1,2'
		),
		'GETPIVOTDATA'=> array('category'=>CalFunction::CATEGORY_LOOKUP_AND_REFERENCE,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::DUMMY',
		'argumentCount'=>'2+'
		),
		'GROWTH'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::GROWTH',
		'argumentCount'=>'1-4'
		),
		'HARMEAN'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::HARMEAN',
		'argumentCount'=>'1+'
		),
		'HEX2BIN'=> array('category'=>CalFunction::CATEGORY_ENGINEERING,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Engineering::HEXTOBIN',
		'argumentCount'=>'1,2'
		),
		'HEX2DEC'=> array('category'=>CalFunction::CATEGORY_ENGINEERING,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Engineering::HEXTODEC',
		'argumentCount'=>'1'
		),
		'HEX2OCT'=> array('category'=>CalFunction::CATEGORY_ENGINEERING,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Engineering::HEXTOOCT',
		'argumentCount'=>'1,2'
		),
		'HLOOKUP'=> array('category'=>CalFunction::CATEGORY_LOOKUP_AND_REFERENCE,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\LookupRef::HLOOKUP',
		'argumentCount'=>'3,4'
		),
		'HOUR'=> array('category'=>CalFunction::CATEGORY_DATE_AND_TIME,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\DateTime::HOUROFDAY',
		'argumentCount'=>'1'
		),
		'HYPERLINK'=> array('category'=>CalFunction::CATEGORY_LOOKUP_AND_REFERENCE,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\LookupRef::HYPERLINK',
		'argumentCount'=>'1,2',
		'passCellReference'=>TRUE
		),
		'HYPGEOMDIST'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::HYPGEOMDIST',
		'argumentCount'=>'4'
		),
		'IF'=> array('category'=>CalFunction::CATEGORY_LOGICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Logical::STATEMENT_IF',
		'argumentCount'=>'1-3'
		),
		'IFERROR'=> array('category'=>CalFunction::CATEGORY_LOGICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Logical::IFERROR',
		'argumentCount'=>'2'
		),
		'IMABS'=> array('category'=>CalFunction::CATEGORY_ENGINEERING,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Engineering::IMABS',
		'argumentCount'=>'1'
		),
		'IMAGINARY'=> array('category'=>CalFunction::CATEGORY_ENGINEERING,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Engineering::IMAGINARY',
		'argumentCount'=>'1'
		),
		'IMARGUMENT'=> array('category'=>CalFunction::CATEGORY_ENGINEERING,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Engineering::IMARGUMENT',
		'argumentCount'=>'1'
		),
		'IMCONJUGATE'=> array('category'=>CalFunction::CATEGORY_ENGINEERING,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Engineering::IMCONJUGATE',
		'argumentCount'=>'1'
		),
		'IMCOS'=> array('category'=>CalFunction::CATEGORY_ENGINEERING,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Engineering::IMCOS',
		'argumentCount'=>'1'
		),
		'IMDIV'=> array('category'=>CalFunction::CATEGORY_ENGINEERING,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Engineering::IMDIV',
		'argumentCount'=>'2'
		),
		'IMEXP'=> array('category'=>CalFunction::CATEGORY_ENGINEERING,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Engineering::IMEXP',
		'argumentCount'=>'1'
		),
		'IMLN'=> array('category'=>CalFunction::CATEGORY_ENGINEERING,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Engineering::IMLN',
		'argumentCount'=>'1'
		),
		'IMLOG10'=> array('category'=>CalFunction::CATEGORY_ENGINEERING,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Engineering::IMLOG10',
		'argumentCount'=>'1'
		),
		'IMLOG2'=> array('category'=>CalFunction::CATEGORY_ENGINEERING,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Engineering::IMLOG2',
		'argumentCount'=>'1'
		),
		'IMPOWER'=> array('category'=>CalFunction::CATEGORY_ENGINEERING,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Engineering::IMPOWER',
		'argumentCount'=>'2'
		),
		'IMPRODUCT'=> array('category'=>CalFunction::CATEGORY_ENGINEERING,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Engineering::IMPRODUCT',
		'argumentCount'=>'1+'
		),
		'IMREAL'=> array('category'=>CalFunction::CATEGORY_ENGINEERING,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Engineering::IMREAL',
		'argumentCount'=>'1'
		),
		'IMSIN'=> array('category'=>CalFunction::CATEGORY_ENGINEERING,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Engineering::IMSIN',
		'argumentCount'=>'1'
		),
		'IMSQRT'=> array('category'=>CalFunction::CATEGORY_ENGINEERING,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Engineering::IMSQRT',
		'argumentCount'=>'1'
		),
		'IMSUB'=> array('category'=>CalFunction::CATEGORY_ENGINEERING,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Engineering::IMSUB',
		'argumentCount'=>'2'
		),
		'IMSUM'=> array('category'=>CalFunction::CATEGORY_ENGINEERING,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Engineering::IMSUM',
		'argumentCount'=>'1+'
		),
		'INDEX'=> array('category'=>CalFunction::CATEGORY_LOOKUP_AND_REFERENCE,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\LookupRef::INDEX',
		'argumentCount'=>'1-4'
		),
		'INDIRECT'=> array('category'=>CalFunction::CATEGORY_LOOKUP_AND_REFERENCE,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\LookupRef::INDIRECT',
		'argumentCount'=>'1,2',
		'passCellReference'=>TRUE
		),
		'INFO'=> array('category'=>CalFunction::CATEGORY_INFORMATION,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::DUMMY',
		'argumentCount'=>'1'
		),
		'INT'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\MathTrig::INT',
		'argumentCount'=>'1'
		),
		'INTERCEPT'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::INTERCEPT',
		'argumentCount'=>'2'
		),
		'INTRATE'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::INTRATE',
		'argumentCount'=>'4,5'
		),
		'IPMT'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::IPMT',
		'argumentCount'=>'4-6'
		),
		'IRR'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::IRR',
		'argumentCount'=>'1,2'
		),
		'ISBLANK'=> array('category'=>CalFunction::CATEGORY_INFORMATION,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::IS_BLANK',
		'argumentCount'=>'1'
		),
		'ISERR'=> array('category'=>CalFunction::CATEGORY_INFORMATION,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::IS_ERR',
		'argumentCount'=>'1'
		),
		'ISERROR'=> array('category'=>CalFunction::CATEGORY_INFORMATION,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::IS_ERROR',
		'argumentCount'=>'1'
		),
		'ISEVEN'=> array('category'=>CalFunction::CATEGORY_INFORMATION,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::IS_EVEN',
		'argumentCount'=>'1'
		),
		'ISLOGICAL'=> array('category'=>CalFunction::CATEGORY_INFORMATION,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::IS_LOGICAL',
		'argumentCount'=>'1'
		),
		'ISNA'=> array('category'=>CalFunction::CATEGORY_INFORMATION,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::IS_NA',
		'argumentCount'=>'1'
		),
		'ISNONTEXT'=> array('category'=>CalFunction::CATEGORY_INFORMATION,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::IS_NONTEXT',
		'argumentCount'=>'1'
		),
		'ISNUMBER'=> array('category'=>CalFunction::CATEGORY_INFORMATION,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::IS_NUMBER',
		'argumentCount'=>'1'
		),
		'ISODD'=> array('category'=>CalFunction::CATEGORY_INFORMATION,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::IS_ODD',
		'argumentCount'=>'1'
		),
		'ISPMT'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::ISPMT',
		'argumentCount'=>'4'
		),
		'ISREF'=> array('category'=>CalFunction::CATEGORY_INFORMATION,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::DUMMY',
		'argumentCount'=>'1'
		),
		'ISTEXT'=> array('category'=>CalFunction::CATEGORY_INFORMATION,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::IS_TEXT',
		'argumentCount'=>'1'
		),
		'JIS'=> array('category'=>CalFunction::CATEGORY_TEXT_AND_DATA,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::DUMMY',
		'argumentCount'=>'1'
		),
		'KURT'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::KURT',
		'argumentCount'=>'1+'
		),
		'LARGE'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::LARGE',
		'argumentCount'=>'2'
		),
		'LCM'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\MathTrig::LCM',
		'argumentCount'=>'1+'
		),
		'LEFT'=> array('category'=>CalFunction::CATEGORY_TEXT_AND_DATA,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\TextData::LEFT',
		'argumentCount'=>'1,2'
		),
		'LEFTB'=> array('category'=>CalFunction::CATEGORY_TEXT_AND_DATA,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\TextData::LEFT',
		'argumentCount'=>'1,2'
		),
		'LEN'=> array('category'=>CalFunction::CATEGORY_TEXT_AND_DATA,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\TextData::STRINGLENGTH',
		'argumentCount'=>'1'
		),
		'LENB'=> array('category'=>CalFunction::CATEGORY_TEXT_AND_DATA,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\TextData::STRINGLENGTH',
		'argumentCount'=>'1'
		),
		'LINEST'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::LINEST',
		'argumentCount'=>'1-4'
		),
		'LN'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>'log',
		'argumentCount'=>'1'
		),
		'LOG'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\MathTrig::LOG_BASE',
		'argumentCount'=>'1,2'
		),
		'LOG10'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>'log10',
		'argumentCount'=>'1'
		),
		'LOGEST'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::LOGEST',
		'argumentCount'=>'1-4'
		),
		'LOGINV'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::LOGINV',
		'argumentCount'=>'3'
		),
		'LOGNORMDIST'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::LOGNORMDIST',
		'argumentCount'=>'3'
		),
		'LOOKUP'=> array('category'=>CalFunction::CATEGORY_LOOKUP_AND_REFERENCE,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\LookupRef::LOOKUP',
		'argumentCount'=>'2,3'
		),
		'LOWER'=> array('category'=>CalFunction::CATEGORY_TEXT_AND_DATA,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\TextData::LOWERCASE',
		'argumentCount'=>'1'
		),
		'MATCH'=> array('category'=>CalFunction::CATEGORY_LOOKUP_AND_REFERENCE,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\LookupRef::MATCH',
		'argumentCount'=>'2,3'
		),
		'MAX'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::MAX',
		'argumentCount'=>'1+'
		),
		'MAXA'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::MAXA',
		'argumentCount'=>'1+'
		),
		'MAXIF'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::MAXIF',
		'argumentCount'=>'2+'
		),
		'MDETERM'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\MathTrig::MDETERM',
		'argumentCount'=>'1'
		),
		'MDURATION'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::DUMMY',
		'argumentCount'=>'5,6'
		),
		'MEDIAN'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::MEDIAN',
		'argumentCount'=>'1+'
		),
		'MEDIANIF'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::DUMMY',
		'argumentCount'=>'2+'
		),
		'MID'=> array('category'=>CalFunction::CATEGORY_TEXT_AND_DATA,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\TextData::MID',
		'argumentCount'=>'3'
		),
		'MIDB'=> array('category'=>CalFunction::CATEGORY_TEXT_AND_DATA,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\TextData::MID',
		'argumentCount'=>'3'
		),
		'MIN'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::MIN',
		'argumentCount'=>'1+'
		),
		'MINA'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::MINA',
		'argumentCount'=>'1+'
		),
		'MINIF'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::MINIF',
		'argumentCount'=>'2+'
		),
		'MINUTE'=> array('category'=>CalFunction::CATEGORY_DATE_AND_TIME,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\DateTime::MINUTEOFHOUR',
		'argumentCount'=>'1'
		),
		'MINVERSE'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\MathTrig::MINVERSE',
		'argumentCount'=>'1'
		),
		'MIRR'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::MIRR',
		'argumentCount'=>'3'
		),
		'MMULT'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\MathTrig::MMULT',
		'argumentCount'=>'2'
		),
		'MOD'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\MathTrig::MOD',
		'argumentCount'=>'2'
		),
		'MODE'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::MODE',
		'argumentCount'=>'1+'
		),
		'MONTH'=> array('category'=>CalFunction::CATEGORY_DATE_AND_TIME,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\DateTime::MONTHOFYEAR',
		'argumentCount'=>'1'
		),
		'MROUND'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\MathTrig::MROUND',
		'argumentCount'=>'2'
		),
		'MULTINOMIAL'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\MathTrig::MULTINOMIAL',
		'argumentCount'=>'1+'
		),
		'N'=> array('category'=>CalFunction::CATEGORY_INFORMATION,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::N',
		'argumentCount'=>'1'
		),
		'NA'=> array('category'=>CalFunction::CATEGORY_INFORMATION,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::NA',
		'argumentCount'=>'0'
		),
		'NEGBINOMDIST'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::NEGBINOMDIST',
		'argumentCount'=>'3'
		),
		'NETWORKDAYS'=> array('category'=>CalFunction::CATEGORY_DATE_AND_TIME,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\DateTime::NETWORKDAYS',
		'argumentCount'=>'2+'
		),
		'NOMINAL'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::NOMINAL',
		'argumentCount'=>'2'
		),
		'NORMDIST'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::NORMDIST',
		'argumentCount'=>'4'
		),
		'NORMINV'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::NORMINV',
		'argumentCount'=>'3'
		),
		'NORMSDIST'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::NORMSDIST',
		'argumentCount'=>'1'
		),
		'NORMSINV'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::NORMSINV',
		'argumentCount'=>'1'
		),
		'NOT'=> array('category'=>CalFunction::CATEGORY_LOGICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Logical::NOT',
		'argumentCount'=>'1'
		),
		'NOW'=> array('category'=>CalFunction::CATEGORY_DATE_AND_TIME,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\DateTime::DATETIMENOW',
		'argumentCount'=>'0'
		),
		'NPER'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::NPER',
		'argumentCount'=>'3-5'
		),
		'NPV'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::NPV',
		'argumentCount'=>'2+'
		),
		'OCT2BIN'=> array('category'=>CalFunction::CATEGORY_ENGINEERING,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Engineering::OCTTOBIN',
		'argumentCount'=>'1,2'
		),
		'OCT2DEC'=> array('category'=>CalFunction::CATEGORY_ENGINEERING,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Engineering::OCTTODEC',
		'argumentCount'=>'1'
		),
		'OCT2HEX'=> array('category'=>CalFunction::CATEGORY_ENGINEERING,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Engineering::OCTTOHEX',
		'argumentCount'=>'1,2'
		),
		'ODD'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\MathTrig::ODD',
		'argumentCount'=>'1'
		),
		'ODDFPRICE'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::DUMMY',
		'argumentCount'=>'8,9'
		),
		'ODDFYIELD'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::DUMMY',
		'argumentCount'=>'8,9'
		),
		'ODDLPRICE'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::DUMMY',
		'argumentCount'=>'7,8'
		),
		'ODDLYIELD'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::DUMMY',
		'argumentCount'=>'7,8'
		),
		'OFFSET'=> array('category'=>CalFunction::CATEGORY_LOOKUP_AND_REFERENCE,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\LookupRef::OFFSET',
		'argumentCount'=>'3,5',
		'passCellReference'=>TRUE,
		'passByReference'=>array(TRUE)
		),
		'OR'=> array('category'=>CalFunction::CATEGORY_LOGICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Logical::LOGICAL_OR',
		'argumentCount'=>'1+'
		),
		'PEARSON'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::CORREL',
		'argumentCount'=>'2'
		),
		'PERCENTILE'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::PERCENTILE',
		'argumentCount'=>'2'
		),
		'PERCENTRANK'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::PERCENTRANK',
		'argumentCount'=>'2,3'
		),
		'PERMUT'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::PERMUT',
		'argumentCount'=>'2'
		),
		'PHONETIC'=> array('category'=>CalFunction::CATEGORY_TEXT_AND_DATA,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::DUMMY',
		'argumentCount'=>'1'
		),
		'PI'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>'pi',
		'argumentCount'=>'0'
		),
		'PMT'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::PMT',
		'argumentCount'=>'3-5'
		),
		'POISSON'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::POISSON',
		'argumentCount'=>'3'
		),
		'POWER'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\MathTrig::POWER',
		'argumentCount'=>'2'
		),
		'PPMT'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::PPMT',
		'argumentCount'=>'4-6'
		),
		'PRICE'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::PRICE',
		'argumentCount'=>'6,7'
		),
		'PRICEDISC'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::PRICEDISC',
		'argumentCount'=>'4,5'
		),
		'PRICEMAT'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::PRICEMAT',
		'argumentCount'=>'5,6'
		),
		'PROB'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::DUMMY',
		'argumentCount'=>'3,4'
		),
		'PRODUCT'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\MathTrig::PRODUCT',
		'argumentCount'=>'1+'
		),
		'PROPER'=> array('category'=>CalFunction::CATEGORY_TEXT_AND_DATA,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\TextData::PROPERCASE',
		'argumentCount'=>'1'
		),
		'PV'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::PV',
		'argumentCount'=>'3-5'
		),
		'QUARTILE'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::QUARTILE',
		'argumentCount'=>'2'
		),
		'QUOTIENT'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\MathTrig::QUOTIENT',
		'argumentCount'=>'2'
		),
		'RADIANS'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>'deg2rad',
		'argumentCount'=>'1'
		),
		'RAND'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\MathTrig::RAND',
		'argumentCount'=>'0'
		),
		'RANDBETWEEN'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\MathTrig::RAND',
		'argumentCount'=>'2'
		),
		'RANK'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::RANK',
		'argumentCount'=>'2,3'
		),
		'RATE'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::RATE',
		'argumentCount'=>'3-6'
		),
		'RECEIVED'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::RECEIVED',
		'argumentCount'=>'4-5'
		),
		'REPLACE'=> array('category'=>CalFunction::CATEGORY_TEXT_AND_DATA,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\TextData::REPLACE',
		'argumentCount'=>'4'
		),
		'REPLACEB'=> array('category'=>CalFunction::CATEGORY_TEXT_AND_DATA,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\TextData::REPLACE',
		'argumentCount'=>'4'
		),
		'REPT'=> array('category'=>CalFunction::CATEGORY_TEXT_AND_DATA,
		'functionCall'=>'str_repeat',
		'argumentCount'=>'2'
		),
		'RIGHT'=> array('category'=>CalFunction::CATEGORY_TEXT_AND_DATA,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\TextData::RIGHT',
		'argumentCount'=>'1,2'
		),
		'RIGHTB'=> array('category'=>CalFunction::CATEGORY_TEXT_AND_DATA,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\TextData::RIGHT',
		'argumentCount'=>'1,2'
		),
		'ROMAN'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\MathTrig::ROMAN',
		'argumentCount'=>'1,2'
		),
		'ROUND'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>'round',
		'argumentCount'=>'2'
		),
		'ROUNDDOWN'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\MathTrig::ROUNDDOWN',
		'argumentCount'=>'2'
		),
		'ROUNDUP'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\MathTrig::ROUNDUP',
		'argumentCount'=>'2'
		),
		'ROW'=> array('category'=>CalFunction::CATEGORY_LOOKUP_AND_REFERENCE,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\LookupRef::ROW',
		'argumentCount'=>'-1',
		'passByReference'=>array(TRUE)
		),
		'ROWS'=> array('category'=>CalFunction::CATEGORY_LOOKUP_AND_REFERENCE,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\LookupRef::ROWS',
		'argumentCount'=>'1'
		),
		'RSQ'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::RSQ',
		'argumentCount'=>'2'
		),
		'RTD'=> array('category'=>CalFunction::CATEGORY_LOOKUP_AND_REFERENCE,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::DUMMY',
		'argumentCount'=>'1+'
		),
		'SEARCH'=> array('category'=>CalFunction::CATEGORY_TEXT_AND_DATA,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\TextData::SEARCHINSENSITIVE',
		'argumentCount'=>'2,3'
		),
		'SEARCHB'=> array('category'=>CalFunction::CATEGORY_TEXT_AND_DATA,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\TextData::SEARCHINSENSITIVE',
		'argumentCount'=>'2,3'
		),
		'SECOND'=> array('category'=>CalFunction::CATEGORY_DATE_AND_TIME,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\DateTime::SECONDOFMINUTE',
		'argumentCount'=>'1'
		),
		'SERIESSUM'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\MathTrig::SERIESSUM',
		'argumentCount'=>'4'
		),
		'SIGN'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\MathTrig::SIGN',
		'argumentCount'=>'1'
		),
		'SIN'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>'sin',
		'argumentCount'=>'1'
		),
		'SINH'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>'sinh',
		'argumentCount'=>'1'
		),
		'SKEW'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::SKEW',
		'argumentCount'=>'1+'
		),
		'SLN'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::SLN',
		'argumentCount'=>'3'
		),
		'SLOPE'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::SLOPE',
		'argumentCount'=>'2'
		),
		'SMALL'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::SMALL',
		'argumentCount'=>'2'
		),
		'SQRT'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>'sqrt',
		'argumentCount'=>'1'
		),
		'SQRTPI'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\MathTrig::SQRTPI',
		'argumentCount'=>'1'
		),
		'STANDARDIZE'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::STANDARDIZE',
		'argumentCount'=>'3'
		),
		'STDEV'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::STDEV',
		'argumentCount'=>'1+'
		),
		'STDEVA'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::STDEVA',
		'argumentCount'=>'1+'
		),
		'STDEVP'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::STDEVP',
		'argumentCount'=>'1+'
		),
		'STDEVPA'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::STDEVPA',
		'argumentCount'=>'1+'
		),
		'STEYX'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::STEYX',
		'argumentCount'=>'2'
		),
		'SUBSTITUTE'=> array('category'=>CalFunction::CATEGORY_TEXT_AND_DATA,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\TextData::SUBSTITUTE',
		'argumentCount'=>'3,4'
		),
		'SUBTOTAL'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\MathTrig::SUBTOTAL',
		'argumentCount'=>'2+'
		),
		'SUM'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\MathTrig::SUM',
		'argumentCount'=>'1+'
		),
		'SUMIF'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\MathTrig::SUMIF',
		'argumentCount'=>'2,3'
		),
		'SUMIFS'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::DUMMY',
		'argumentCount'=>'?'
		),
		'SUMPRODUCT'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\MathTrig::SUMPRODUCT',
		'argumentCount'=>'1+'
		),
		'SUMSQ'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\MathTrig::SUMSQ',
		'argumentCount'=>'1+'
		),
		'SUMX2MY2'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\MathTrig::SUMX2MY2',
		'argumentCount'=>'2'
		),
		'SUMX2PY2'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\MathTrig::SUMX2PY2',
		'argumentCount'=>'2'
		),
		'SUMXMY2'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\MathTrig::SUMXMY2',
		'argumentCount'=>'2'
		),
		'SYD'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::SYD',
		'argumentCount'=>'4'
		),
		'T'=> array('category'=>CalFunction::CATEGORY_TEXT_AND_DATA,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\TextData::RETURNSTRING',
		'argumentCount'=>'1'
		),
		'TAN'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>'tan',
		'argumentCount'=>'1'
		),
		'TANH'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>'tanh',
		'argumentCount'=>'1'
		),
		'TBILLEQ'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::TBILLEQ',
		'argumentCount'=>'3'
		),
		'TBILLPRICE'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::TBILLPRICE',
		'argumentCount'=>'3'
		),
		'TBILLYIELD'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::TBILLYIELD',
		'argumentCount'=>'3'
		),
		'TDIST'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::TDIST',
		'argumentCount'=>'3'
		),
		'TEXT'=> array('category'=>CalFunction::CATEGORY_TEXT_AND_DATA,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\TextData::TEXTFORMAT',
		'argumentCount'=>'2'
		),
		'TIME'=> array('category'=>CalFunction::CATEGORY_DATE_AND_TIME,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\DateTime::TIME',
		'argumentCount'=>'3'
		),
		'TIMEVALUE'=> array('category'=>CalFunction::CATEGORY_DATE_AND_TIME,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\DateTime::TIMEVALUE',
		'argumentCount'=>'1'
		),
		'TINV'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::TINV',
		'argumentCount'=>'2'
		),
		'TODAY'=> array('category'=>CalFunction::CATEGORY_DATE_AND_TIME,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\DateTime::DATENOW',
		'argumentCount'=>'0'
		),
		'TRANSPOSE'=> array('category'=>CalFunction::CATEGORY_LOOKUP_AND_REFERENCE,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\LookupRef::TRANSPOSE',
		'argumentCount'=>'1'
		),
		'TREND'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::TREND',
		'argumentCount'=>'1-4'
		),
		'TRIM'=> array('category'=>CalFunction::CATEGORY_TEXT_AND_DATA,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\TextData::TRIMSPACES',
		'argumentCount'=>'1'
		),
		'TRIMMEAN'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::TRIMMEAN',
		'argumentCount'=>'2'
		),
		'TRUE'=> array('category'=>CalFunction::CATEGORY_LOGICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Logical::TRUE',
		'argumentCount'=>'0'
		),
		'TRUNC'=> array('category'=>CalFunction::CATEGORY_MATH_AND_TRIG,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\MathTrig::TRUNC',
		'argumentCount'=>'1,2'
		),
		'TTEST'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::DUMMY',
		'argumentCount'=>'4'
		),
		'TYPE'=> array('category'=>CalFunction::CATEGORY_INFORMATION,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::TYPE',
		'argumentCount'=>'1'
		),
		'UPPER'=> array('category'=>CalFunction::CATEGORY_TEXT_AND_DATA,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\TextData::UPPERCASE',
		'argumentCount'=>'1'
		),
		'USDOLLAR'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::DUMMY',
		'argumentCount'=>'2'
		),
		'VALUE'=> array('category'=>CalFunction::CATEGORY_TEXT_AND_DATA,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::DUMMY',
		'argumentCount'=>'1'
		),
		'VAR'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::VARFunc',
		'argumentCount'=>'1+'
		),
		'VARA'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::VARA',
		'argumentCount'=>'1+'
		),
		'VARP'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::VARP',
		'argumentCount'=>'1+'
		),
		'VARPA'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::VARPA',
		'argumentCount'=>'1+'
		),
		'VDB'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::DUMMY',
		'argumentCount'=>'5-7'
		),
		'VERSION'=> array('category'=>CalFunction::CATEGORY_INFORMATION,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::VERSION',
		'argumentCount'=>'0'
		),
		'VLOOKUP'=> array('category'=>CalFunction::CATEGORY_LOOKUP_AND_REFERENCE,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\LookupRef::VLOOKUP',
		'argumentCount'=>'3,4'
		),
		'WEEKDAY'=> array('category'=>CalFunction::CATEGORY_DATE_AND_TIME,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\DateTime::DAYOFWEEK',
		'argumentCount'=>'1,2'
		),
		'WEEKNUM'=> array('category'=>CalFunction::CATEGORY_DATE_AND_TIME,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\DateTime::WEEKOFYEAR',
		'argumentCount'=>'1,2'
		),
		'WEIBULL'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::WEIBULL',
		'argumentCount'=>'4'
		),
		'WORKDAY'=> array('category'=>CalFunction::CATEGORY_DATE_AND_TIME,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\DateTime::WORKDAY',
		'argumentCount'=>'2+'
		),
		'XIRR'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::XIRR',
		'argumentCount'=>'2,3'
		),
		'XNPV'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::XNPV',
		'argumentCount'=>'3'
		),
		'YEAR'=> array('category'=>CalFunction::CATEGORY_DATE_AND_TIME,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\DateTime::YEAR',
		'argumentCount'=>'1'
		),
		'YEARFRAC'=> array('category'=>CalFunction::CATEGORY_DATE_AND_TIME,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\DateTime::YEARFRAC',
		'argumentCount'=>'2,3'
		),
		'YIELD'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Functions::DUMMY',
		'argumentCount'=>'6,7'
		),
		'YIELDDISC'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::YIELDDISC',
		'argumentCount'=>'4,5'
		),
		'YIELDMAT'=> array('category'=>CalFunction::CATEGORY_FINANCIAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Financial::YIELDMAT',
		'argumentCount'=>'5,6'
		),
		'ZTEST'=> array('category'=>CalFunction::CATEGORY_STATISTICAL,
		'functionCall'=>PHPEXCEL_SPACE.'PHPExcel\Calculation\Statistical::ZTEST',
		'argumentCount'=>'2-3'
		)
	);
}