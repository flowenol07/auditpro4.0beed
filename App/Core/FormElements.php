<?php

namespace Core;

class FormElements {

    protected static $defaultClass = 'form-control';
    protected static $labelClass = 'form-label';

    // private static function arrayParamCheck($params, $val) {
    //     return isset($params[ $val ]) ? $params[ $val ] : '';
    // }

    public static function generateInput($params = []) {

        $params['type']     = $params['type'] ?? 'text';
        $params['name']     = $params['name'] ?? '';
        $params['value']    = $params['value'] ?? '';
        $params['class']    = $params['class'] ?? self::$defaultClass;
        $params['class']    .= isset($params['appendClass']) ? (" " . $params['appendClass']) : '';
        $params['id']       = $params['id'] ?? '';
        $params['placeholder'] = $params['placeholder'] ?? '';
        $params['required'] = $params['required'] ?? false;
        $params['disabled'] = $params['disabled'] ?? false;
        $params['dataAttributes'] = generate_data_attributes($params);
        $params['extra'] = $params['extra'] ?? '';

        $inputMarkup  = "<input type='". $params['type'] ."'";
        $inputMarkup .= $params['name'] ? " name='". $params['name'] ."'" : '';
        $inputMarkup .= $params['id'] ? " id='". $params['id'] ."'" : '';
        $inputMarkup .= $params['class'] ? " class='". $params['class'] ."'" : '';
        $inputMarkup .= " value='". $params['value'] ."'";
        $inputMarkup .= $params['placeholder'] ? " placeholder='". $params['placeholder'] ."'" : '';
        $inputMarkup .= $params['dataAttributes'] ? $params['dataAttributes'] : '';
        $inputMarkup .= $params['required'] ? ' required' : '';
        $inputMarkup .= $params['disabled'] ? ' disabled' : '';
        $inputMarkup .= $params['extra'] ? (' ' . $params['extra']) : '';
        $inputMarkup .= " autocomplete='off' />";

        return $inputMarkup;
    }

    public static function generateTextArea($params = []) {

        $params['name']     = $params['name'] ?? '';
        $params['value']    = $params['value'] ?? '';
        $params['class']    = $params['class'] ?? self::$defaultClass;
        $params['class']    .= isset($params['appendClass']) ? (" " . $params['appendClass']) : '';
        $params['id']       = $params['id'] ?? '';
        $params['placeholder'] = $params['placeholder'] ?? '';
        $params['required'] = $params['required'] ?? false;
        $params['disabled'] = $params['disabled'] ?? false;
        $params['dataAttributes'] = generate_data_attributes($params);
        $params['extra'] = $params['extra'] ?? '';
        $params['rows'] = $params['rows'] ?? '3';
        $params['cols'] = $params['cols'] ?? '50';

        $inputMarkup  = "<textarea type='". $params['type'] ."'";
        $inputMarkup .= $params['name'] ? " name='". $params['name'] ."'" : '';
        $inputMarkup .= $params['id'] ? " id='". $params['id'] ."'" : '';
        $inputMarkup .= $params['class'] ? " class='". $params['class'] ."'" : '';
        $inputMarkup .= $params['placeholder'] ? " placeholder='". $params['placeholder'] ."'" : '';
        $inputMarkup .= $params['dataAttributes'] ? $params['dataAttributes'] : '';
        $inputMarkup .= $params['required'] ? ' required' : '';
        $inputMarkup .= $params['disabled'] ? ' disabled' : '';
        $inputMarkup .= $params['extra'] ? (' ' . $params['extra']) : '';
        $inputMarkup .= $params['rows'] ? " rows='". $params['rows'] ."'" : '';
        $inputMarkup .= $params['cols'] ? " cols='". $params['cols'] ."'" : '';
        $inputMarkup .= ">";
        $inputMarkup .= $params['value'];
        $inputMarkup .= "</textarea>";

        return $inputMarkup;
    }

    public static function generateSelect($params = []) {

        $params['name']     = $params['name'] ?? '';
        $params['options']  = $params['options'] ?? [];
        $params['default']  = $params['default'] ?? null;
        $params['selected'] = $params['selected'] ?? null;
        $params['class']    = $params['class'] ?? 'form-control form-select';
        $params['class']    .= isset($params['appendClass']) ? (" " . $params['appendClass']) : '';
        $params['id']       = $params['id'] ?? '';
        $params['required'] = $params['required'] ?? false;
        $params['disabled'] = $params['disabled'] ?? false;
        $params['dataAttributes'] = generate_data_attributes($params);
        $params['extra'] = $params['extra'] ?? '';

        $selectMarkup  = "<select";
        $selectMarkup .= $params['name'] ? " name='". $params['name'] ."'" : '';
        $selectMarkup .= $params['id'] ? " id='". $params['id'] ."'" : '';
        $selectMarkup .= $params['class'] ? " class='". $params['class'] ."'" : '';
        $selectMarkup .= $params['dataAttributes'] ? $params['dataAttributes'] : '';
        $selectMarkup .= $params['required'] ? ' required' : '';
        $selectMarkup .= $params['disabled'] ? ' disabled' : '';
        $selectMarkup .= $params['extra'] ? (' ' . $params['extra']) : '';
        $selectMarkup .= ">";

        //for default value
        if( is_array($params['default']) )
            $selectMarkup .= "<option value='". $params['default'][0] ."'>". $params['default'][1] ."</option>";

        if( is_array($params['options']) && sizeof($params['options']) > 0 )
        {
            foreach ($params['options'] as $value => $label) {

                $dataAttribute = null;

                if(isset($params['options_db']) && sizeof($params['options_db']) > 0)
                {
                    if(isset($params['optionDataAttributes']))
                    {
                        foreach($params['optionDataAttributes'] as $cOPDAttri)
                        {
                            if(is_object($label) && isset( $label -> { $cOPDAttri } ))
                                $dataAttribute .= ' data-' . $cOPDAttri . '="'. $label -> { $cOPDAttri } .'"';
                            else if(is_array($label) && isset( $label[ $cOPDAttri ] ))
                                $dataAttribute .= ' data-' . $cOPDAttri . '="'. $label[ $cOPDAttri ] .'"';
                        }
                    }

                    //for db releted data
                    $label = ($params['options_db']['type'] == 'obj') ? $label -> { $params['options_db']['val'] } : $label[ $params['options_db']['val'] ];
                }

                $isSelected = ($value == $params['selected']) ? ' selected' : '';
                $selectMarkup .= "<option value='". $value ."'". $isSelected . $dataAttribute .">". $label ."</option>";
            }
        }

        $selectMarkup .= "</select>";

        return $selectMarkup;
    }

    public static function generateCheckboxOrRadio($params = []) {

        $params['type']     = $params['type'] ?? 'checkbox';
        $params['name']     = $params['name'] ?? '';
        $params['value']    = $params['value'] ?? '';
        $params['text']     = $params['text'] ?? '';
        $params['checked']  = $params['checked'] ?? false;
        $params['class']    = $params['class'] ?? 'form-check-input';
        $params['class']    .= isset($params['appendClass']) ? (" " . $params['appendClass']) : '';
        $params['id']       = $params['id'] ?? '';
        $params['required'] = $params['required'] ?? false;
        $params['customLabelClass'] = $params['customLabelClass'] ?? '';
        $params['dataAttributes'] = generate_data_attributes($params);
        $params['extra'] = $params['extra'] ?? '';

        $checkedAttribute = $params['checked'] ? ' checked' : '';

        $checkboxMarkup  = "<label class='form-check-label me-3 ". $params['customLabelClass'] ."'><input type='". $params['type'] ."'";
        $checkboxMarkup .= $params['name'] ? " name='". $params['name'] ."'" : '';
        $checkboxMarkup .= $params['id'] ? " id='". $params['id'] ."'" : '';
        $checkboxMarkup .= $params['class'] ? " class='". $params['class'] ." me-2'" : '';
        $checkboxMarkup .= $params['value'] ? " value='". $params['value'] ."'" : '';
        $checkboxMarkup .= $checkedAttribute;
        $checkboxMarkup .= $params['dataAttributes'] ? $params['dataAttributes'] : '';
        $checkboxMarkup .= $params['required'] ? ' required' : '';
        $checkboxMarkup .= $params['extra'] ? (' ' . $params['extra']) : '';
        $checkboxMarkup .= " />". $params['text'] ."</label>";

        return $checkboxMarkup;
    }

    public static function generateLabel($for, $text, $class = "") {

        $class = !empty($class) ? $class : (self::$labelClass . ' font-medium d-block mb-1');

        $labelMarkup = "<label";

        $labelMarkup .= !empty($for) ? " for='". $for ."'" : '';
        $labelMarkup .= " class='" . $class . "'>". $text ."</label>";

        return $labelMarkup;
    }

    public static function generateFormGroup($inputMarkup, $data = [], $err = null, $class = 'default', $label = '') {

        $class = !empty($class) ? $class : '';
        $class = ($class == 'default') ? 'form-group mb-3' : '';

        $formGroupMarkup = "<div";
        $formGroupMarkup .= !empty($class) ? " class='". $class ."'" : '';
        $formGroupMarkup .= ">";
        $formGroupMarkup .= is_array($label) ? self::generateLabel($label[0], $label[1]) : '';
        $formGroupMarkup .= $inputMarkup;

        if(!empty($err))
            $formGroupMarkup .= $data['noti']::getInputNoti($data['request'], ($err . '_err'));

        $formGroupMarkup .= "</div>";

        return $formGroupMarkup;
    }

    public static function generateFormStart($params = []) {

        $params['name']     = $params['name'] ?? '';
        $params['id']       = $params['id'] ?? '';
        $params['method']   = $params['method'] ?? 'post';
        $params['action']   = $params['action'] ?? '';
        $params['class']    = $params['class'] ?? '';
        $params['class']    .= isset($params['appendClass']) ? (" " . $params['appendClass']) : '';
        $params['enctype']  = $params['enctype'] ?? '';
        $params['dataAttributes'] = generate_data_attributes($params);
        $params['extra'] = $params['extra'] ?? '';

        $formMarkup = "<form";

        $formMarkup .= $params['name'] ? " name='". $params['name'] ."'" : '';
        $formMarkup .= $params['id'] ? " id='". $params['id'] ."'" : '';
        $formMarkup .= $params['class'] ? " class='". $params['class'] ."'" : '';
        $formMarkup .= $params['method'] ? " method='". $params['method'] ."'" : '';
        $formMarkup .= " action='". $params['action'] ."'";
        $formMarkup .= $params['dataAttributes'] ? $params['dataAttributes'] : '';
        $formMarkup .= $params['enctype'] ? " enctype='". $params['enctype'] ."'" : '';
        $formMarkup .= $params['extra'] ? (' ' . $params['extra']) : '';
        $formMarkup .= ">";

        return $formMarkup;
    }

    public static function generateFormClose() {
        return '</form>';
    }

    public static function generateSubmitButton($btnType = '', $params = []) {

        if(in_array($btnType, ['add', 'update', 'search', 'find', 'filter', 'print', 'excel', 'reset']))
        {
            $params['class'] = 'icn-grid icn-bf';

            if($btnType == 'add')
                $params['class'] .= ' btn btn-success icn-add';
            else if($btnType == 'search')
                $params['class'] .= ' btn btn-success icn-search';
            else if($btnType == 'update')
                $params['class'] .= ' btn btn-success icn-update';
            else if($btnType == 'find')
                $params['class'] .= ' btn btn-primary icn-search';
            else if($btnType == 'filter')
                $params['class'] .= ' btn btn-success icn-filter';
            else if($btnType == 'print')
                $params['class'] .= ' btn btn-primary icn-print';
            else if($btnType == 'excel')
                $params['class'] .= ' btn btn-primary icn-download';
            else if($btnType == 'reset')
                $params['class'] .= ' btn btn-secondary icn-reload';
            else
                $params['class'] .= ' icn-link';            
        }
        else
            $params['class'] = 'btn btn-primary';

        // $params['class'] = $params['class'] ?? 'btn btn-primary';
        $params['name']  = $params['name'] ?? '';
        $params['value'] = $params['value'] ?? 'Submit';
        
        $params['id']    = $params['id'] ?? 'submitBtn';
        $params['dataAttributes'] = generate_data_attributes($params);
        $params['extra'] = $params['extra'] ?? '';
        $params['class'] .= isset($params['appendClass']) ? (" " . $params['appendClass']) : '';

        $inputMarkup  = "<button";
        $inputMarkup .= (!isset($params['type'])) ? " type='submit'" : ((isset($params['type']) && !empty($params['type'])) ? (" type='" . $params['type'] . "'") : '');
        $inputMarkup .= $params['name'] ? " name='". $params['name'] ."'" : '';
        $inputMarkup .= $params['id'] ? " id='". $params['id'] ."'" : '';
        $inputMarkup .= $params['class'] ? " class='". $params['class'] ."'" : '';
        $inputMarkup .= $params['dataAttributes'] ? $params['dataAttributes'] : '';
        $inputMarkup .= $params['extra'] ? (' ' . $params['extra']) : '';
        $inputMarkup .= ">";
        $inputMarkup .= $params['value'] ? $params['value'] : '';
        $inputMarkup .= '</button>';

        return $inputMarkup;
    }
}

?>