<?php
if (!defined('ABSPATH'))
exit; // Exit if accessed directly

/**
* WPFM_Taxonomy_Radio_Checklist class.
*/

class WPFM_Taxonomy_Radio_Checklist extends Walker_Category_Checklist {
    public function walk($elements, $max_depth, ...$args) {
        $output = parent::walk($elements, $max_depth, ...$args);
        $output = str_replace(
            array('type="checkbox"', "type='checkbox'"),
            array('type="radio"', "type='radio'"),
            $output
        );
        return $output;
    }
}