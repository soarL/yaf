<?php
/**
 * PHPExcel
 *
 * Copyright (c) 2006 - 2014 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPExcel
 * @package    PHPExcel
 * @copyright  Copyright (c) 2006 - 2014 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 * @version    1.8.0, 2014-03-02
 */

// PHPExcel_Autoloader::Register();

/**
 * PHPExcel_Autoloader
 *
 * @category    PHPExcel
 * @package     PHPExcel
 * @copyright   Copyright (c) 2006 - 2014 PHPExcel (http://www.codeplex.com/PHPExcel)
 */
class PHPExcel_Autoloader
{
    /**
     * Register the Autoloader with SPL
     *
     */
    public static function Register() {
        if (function_exists('__autoload')) {
            //    Register any existing autoloader function with SPL, so we don't get any clashes
            spl_autoload_register('__autoload');
        }
        //    Register ourselves with SPL
        return spl_autoload_register(array('PHPExcel_Autoloader', 'Load'));
    }   //    function Register()


    /**
     * Autoload a class identified by name
     *
     * @param    string    $pClassName        Name of the object to load
     */
    public static function Load($pClassName){
        // $className = str_replace(PHPEXCEL_SPACE . '\\', '', $pClassName);
        $className = $pClassName;
        if ((class_exists($className,FALSE)) || (strpos($className, 'PHPExcel') !== 0)) {
            //    Either already loaded, or not a PHPExcel class request
            return FALSE;
        }

        $pClassFilePath = PHPEXCEL_ROOT .
                          str_replace('_',DIRECTORY_SEPARATOR,$className) .
                          '.php';
        if ((file_exists($pClassFilePath) === FALSE) || (is_readable($pClassFilePath) === FALSE)) {
            //    Can't load
            return FALSE;
        }
        require($pClassFilePath);
    }   //    function Load()

}
