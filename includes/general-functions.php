<?php

/**
 * Check variable status
 *
 * @return void
 */
function grutto_is_true( $var ) {
    if ( is_bool( $var ) ) {
        return $var;
    }
    if ( is_string( $var ) ){
        $var = strtolower( $var );
        if( in_array( $var, array( 'yes', 'on', 'true', 'checked' ) ) ){
            return true;
        }
    }
    if ( is_numeric( $var ) ) {
        return (bool) $var;
    }
    return false;
}