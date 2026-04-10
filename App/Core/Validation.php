<?php

namespace Core;

class Validation {

    public static $regexArray = array(
        'charRegex' 		=> '/^[a-zA-Z.\'\- ]+$/',
        'alphaNumricRegex'	=> '/^[a-zA-Z0-9.\'\- ]+$/',
        'alphaNumericSymbolsRegex' => '/^[a-zA-Z0-9.\'()\[\],\-_\/\\ ]+$/',
        'numberRegex' 		=> '/^-?[0-9]+$/',
        'numberSpaceRegex' 	=> '/^-?[0-9 ]+$/',
        'floatNumberRegex'	=> '/^-?([0-9]*[.])?[0-9]\d{0,5}+$/',
        'mobileRegex' 		=> '/^[7896]\d{9}+$/',
        'emailRegex' 		=> "^([a-z0-9_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-zA-Z]{2,4}$^",
        'tenDigitRegex'	 	=> '/^\d{10}$/',
        'dateRegex' 		=> '/^\d{4}\-\d{2}-\d{2}$/',
        'yearMonthRegex' 	=> '/^\d{4}\-\d{2}$/',
        'timeRegex'			=> '/^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/',
        'imageNameRegex'    => '/^[0-9]*\.(jpg|jpeg|png)/'
    );

    public static function incrementError(Request $request) {
        $request -> setInputCustom('error', ( $request -> input('error') + 1 ));
    }

    public static function flashErrorMsg(/*Request $request, */$msg = 'invalidForm', $warning = 'warning') {
        // if($request -> input( 'error' ) > 0)
            Session::flash($warning, $msg);
    }

    private static function convert2Array($str, $key, $needExplode = null) {

        $str = str_replace($key, '', $str);
        $str = substr($str, 1, -1);

        if($needExplode != null)
            return !empty($str) ? explode($needExplode, $str) : [];

        return $str;
    }

    private static function validate($key, $validate, Request $request, $data = [])
    {
        if(preg_match("/regex/i", $validate))
        {
            $tempConvertArray = self::convert2Array($validate, 'regex', ',');
            $validate = 'regex';
        }
        elseif(preg_match("/match/i", $validate))
        {
            $tempConvertArray = self::convert2Array($validate, 'match', ',');
            $validate = 'match';
        }
        elseif(preg_match("/not_match/i", $validate))
        {
            $tempConvertArray = self::convert2Array($validate, 'not_match', ',');
            $validate = 'not_match';
        }
        elseif(preg_match("/in_array/i", $validate))
        {
            $tempConvertArray = self::convert2Array($validate, 'in_array', ',');
            $validate = 'in_array';
        }
        elseif(preg_match("/array_key/i", $validate))
        {
            $tempConvertArray = self::convert2Array($validate, 'array_key', ',');
            $validate = 'array_key';
        }
        elseif(preg_match("/is_unique/i", $validate))
        {
            $tempConvertArray = self::convert2Array($validate, 'is_unique', ',');
            $validate = 'is_unique';
        }
        elseif(preg_match("/file_upload/i", $validate))
        {
            $tempConvertArray = self::convert2Array($validate, 'file_upload', ',');
            $validate = 'file_upload';
        }

        switch($validate)
        {
            case 'required': {
                
                if(!$request -> has( $key ) || ($request -> input( $key ) != '0' && empty($request -> input( $key ))) )
                {
                    $request -> setInputCustom($key . '_err', 'required');
                    self::incrementError($request);
                }

                break;
            }

            case 'regex': {

                $cVal = $request -> input( $key );

                if( !empty($cVal) && $tempConvertArray[0] == 'custom_regx' && !preg_match(('/' . $request -> input( 'custom_regex' ) . '/'), $cVal) )
                {
                    $request -> setInputCustom($key . '_err', $tempConvertArray[1]);
                    self::incrementError($request);
                }   

                if( !empty($cVal) && $tempConvertArray[0] != 'custom_regx' && !preg_match(self::$regexArray[ $tempConvertArray[0] ], $cVal) )
                {
                    // $errKey = isset($tempConvertArray[2]) ? $tempConvertArray[2] : $tempConvertArray[1];
                    $request -> setInputCustom($key . '_err', $tempConvertArray[1]);
                    self::incrementError($request);
                }

                break;
            }        
            
            case 'match': {

                $val1 = $request -> input( $key );
                $val2 = $request -> input( $tempConvertArray[0] );

                if(!empty($val1) && !empty($val2) && $val1 == $val2)
                {
                    $request -> setInputCustom($key . '_err', $tempConvertArray[1]);
                    self::incrementError($request);
                }

                break;
            }

            case 'not_match': {

                $val1 = $request -> input( $key );
                $val2 = $request -> input( $tempConvertArray[0] );

                if(!empty($val1) && !empty($val2) && $val1 != $val2)
                {
                    $request -> setInputCustom($key . '_err', $tempConvertArray[1]);
                    self::incrementError($request);
                }

                break;
            }

            case 'in_array': {

                $cVal = $request -> input( $key );

                if( !array_key_exists($tempConvertArray[0], $data) || 
                    (is_array($data[ $tempConvertArray[0] ]) && !sizeof($data[ $tempConvertArray[0] ]) > 0) )
                {
                    $request -> setInputCustom($key . '_err', $tempConvertArray[1]);
                    self::incrementError($request);
                }

                elseif( !empty($cVal) &&  !in_array($cVal, $data[ $tempConvertArray[0] ] ) )
                {
                    $request -> setInputCustom($key . '_err', $tempConvertArray[1]);
                    self::incrementError($request);
                }
                
                break;
            }

            case 'array_key': {

                $cVal = $request -> input( $key );

                if( !array_key_exists($tempConvertArray[0], $data) || 
                    (is_array($data[ $tempConvertArray[0] ]) && !sizeof($data[ $tempConvertArray[0] ]) > 0) )
                {
                    $request -> setInputCustom($key . '_err', $tempConvertArray[1]);
                    self::incrementError($request);
                }

                elseif( !empty($cVal) &&  !array_key_exists($cVal, $data[ $tempConvertArray[0] ] ) )
                {
                    $request -> setInputCustom($key . '_err', $tempConvertArray[1]);
                    self::incrementError($request);
                }

                break;
            }

            case 'is_unique': {

                $errBool = false;

                if(!array_key_exists($tempConvertArray[0], $data) || 
                  ( isset($data[ $tempConvertArray[0] ]) && 
                    (!array_key_exists('model', $data[ $tempConvertArray[0] ]) || 
                    !array_key_exists('where', $data[ $tempConvertArray[0] ]) || 
                    !array_key_exists('params', $data[ $tempConvertArray[0] ]) 
                    ) 
                  )
                )
                {
                    //error
                    $errBool = true;
                    
                }
                else
                {
                    $where = ['where' => $data[ $tempConvertArray[0]]['where'], 'params' => $data[ $tempConvertArray[0]]['params'] ];

                    //has all data
                    $checkData = $data[ $tempConvertArray[0] ]['model'] -> selectSingle(
                        $data[ $tempConvertArray[0] ]['model'] -> getTableName(),
                        $where, '*'
                    );

                    //error
                    if(is_object($checkData))
                        $errBool = true;
                }

                //has error
                if($errBool)
                {
                    $request -> setInputCustom($key . '_err', $tempConvertArray[1]);
                    self::incrementError($request);
                }

                break;
            }

            case 'file_upload': {

                // if( !array_key_exists($tempConvertArray[0], $data) || 
                //     (is_array($data[ $tempConvertArray[0] ]) && !sizeof($data[ $tempConvertArray[0] ]) > 0) )
                // {
                //     $request -> setInputCustom($key . '_err', $tempConvertArray[1]);
                //     self::incrementError($request);
                // }

                $fileValidationsArr = [
                    'csv' => [ 'types' => ['text/csv'], 'size' => 10 ]
                ];

                if( !isset($_FILES[ $key ]) || 
                    !is_array($_FILES[ $key ]) || 
                    ( is_array($_FILES[ $key ]) && !in_array($_FILES[ $key ]['type'], $fileValidationsArr[ $tempConvertArray[0] ]['types'])) )
                {
                    $request -> setInputCustom($key . '_err', ($tempConvertArray[0] . 'UploadError'));
                    self::incrementError($request);
                }
                else
                {
                    // echo $_FILES[ $key ]['size'] . ' ' ;
                    // echo ($fileValidationsArr['csv']['size'] * (1024 * 1024) );

                    if ( number_format($_FILES[ $key ]['size'] / ($fileValidationsArr['csv']['size'] * (1024 * 1024) ), 2) > $fileValidationsArr['csv']['size'] ) {

                        $request -> setInputCustom($key . '_err', 'Error: Max file upload file size: '. $fileValidationsArr['csv']['size'] .'MB');
                        self::incrementError($request);
                    }

                }

            }

            default: {
                break;
            }
        }
    }

    public static function validateData(Request $request, $validations, $data = [])
    {
        /*
            //validation seprated by | symbol
            $validations = array(
                'input_name' => 'required|regex[regex_key, err_msg]|array_key[table_key]|unique[table, table_key]'
            )
        
        */

        $request -> setInputCustom('error', 0);

        foreach($validations as $cInputKey => $cNeedValidations)
        {
            $cNeedValidations = explode('|', $cNeedValidations);

            if(is_array($cNeedValidations) && sizeof($cNeedValidations) > 0)
            {
                foreach($cNeedValidations as $cValidate)
                {
                    self::validate($cInputKey, $cValidate, $request, $data);

                    if($request -> has($cInputKey . '_err'))
                        break;
                }
            }
        }

        //method call
        if($request -> input( 'error' ) > 0)
            self::flashErrorMsg(/*$request*/);
    }    

}

?>