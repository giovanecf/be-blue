<?php

add_filter( 'rest_authentication_errors', '__return_true' );


add_action( 'rest_api_init', 'be_blue_api_register_submission_routes' );

/**
 * This function is where we register our routes for our example endpoint.
 */
function be_blue_api_register_submission_routes() {
    // Here we are registering our route for a collection of submissions and creation of submissions.
    register_rest_route( 'intranet/v1', '/submissions', array(
        array(
            // By using this constant we ensure that when the WP_REST_Server changes, our readable endpoints will work as intended.
            'methods'  => WP_REST_Server::READABLE,
            // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
            'callback' => 'be_blue_api_get_submissions',
        ),
        array(
            // By using this constant we ensure that when the WP_REST_Server changes, our create endpoints will work as intended.
            'methods'  => WP_REST_Server::CREATABLE,
            // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
            'callback' => 'be_blue_api_create_submission',
        ),
    ) );

    register_rest_route( 'intranet/v1', '/submissions/(?P<id>[\w]+)', array(
        // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
        'methods'  => WP_REST_Server::READABLE,
        // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        'callback' => 'be_blue_api_get_submission_by_id_handler',
    ) );

    register_rest_route( 'intranet/v1', '/submissions/object/(?P<object>[\w]+)', array(
        // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
        'methods'  => WP_REST_Server::READABLE,
        // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        'callback' => 'be_blue_api_get_submissions_by_object_name_handler',
    ) );

    register_rest_route( 'intranet/v1', '/submissions/(?P<place>[\w]+)/events', array(
        // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
        'methods'  => WP_REST_Server::READABLE,
        // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        'callback' => 'be_blue_api_get_place_events',
    ) );


}

function be_blue_api_update_status_expense( $id, $status ) {

    global $wpdb;

    if ( ! $id ||
         ! $status )
        return false;

    $id     = sanitize_text_field( $id );
    $status = sanitize_text_field( $status );

    $expense = be_blue_api_get_submission_by_id( $id );

    $expense_data = json_decode( $expense['data'], true );

    $expense_data['status'] = $status;

    $expense['data'] = json_encode( $expense_data );

    $table_name = $wpdb->prefix . 'fafar_cf7crud_submissions';

    $res = $wpdb->update(
        $table_name,
        array(
            'data' => $expense['data']
        ),
        array(
            'id' => $id
        )
    );

    if ( $status == 'Paga' ) {

        $outcome_value = $expense_data['value'];

        if ( $expense_data['overdue_tax'] ) {
    
            $outcome_value += $expense_data['overdue_tax']; 
    
        } 
    
        if ( $expense_data['overdue_tax_percent'] ) {
    
            $outcome_value += ( $outcome_value * $expense_data['overdue_tax_percent'] ); 
    
        } 

        be_blue_api_create_cash_flow( $expense_data['desc'], 
                                      $outcome_value, 
                                      'Saída' );
    }

    return $res;

}

function be_blue_api_create_cash_flow( $desc, $value, $type, $cat = false) {

    $outcome = array(
        'object_name' => 'cash_flow',
        'data' => array(
                    'desc' => $desc,
                    'type' => $type,
                    'value' => $value,
                    'category' => $cat ?? 'Desconhecida'
                )
        );

    return be_blue_api_create( $outcome, false );

}

function be_blue_api_get_event_timestamp( $current, $hour ) {

    $d = date_create( "now", new DateTimeZone('America/Sao_Paulo') );
    $d->setTimestamp((int) $current);

    return be_blue_api_get_timestamp( $d->format("Y-m-d") . " " . $hour );

}

function be_blue_api_get_timestamp( $date_string ) {

    $d = date_create( $date_string, new DateTimeZone('America/Sao_Paulo') );

    return (int) $d->getTimestamp();

}

function be_blue_api_get_weekday_by_timestamp( $timestamp ) {

    $d = date_create( "now", new DateTimeZone('America/Sao_Paulo') );
    $d->setTimestamp((int) $timestamp);

    return (int) $d->format("w");

}
  
function be_blue_api_get_submission_by_id_handler( $request ) {

    $id = (string) $request['id'];

    $submission = be_blue_api_get_submission_by_id( $id );

    if ( $submission['msg_error'] ) {

        return new WP_Error( 'rest_api_sad', esc_html__( $submission['msg_error'], 'intranet-fafar-api' ), $submission['http_status'] );

    }

    return rest_ensure_response( json_encode( $submission ) );

}

function be_blue_api_get_submission_by_id( $id ) {

    global $wpdb;

    $table_name = $wpdb->prefix . 'fafar_cf7crud_submissions';

    if( ! $id ) {

        return array( 'msg_error' => '[0101]No "id" found.', 'http_status' => 400 );

    }
    
    $id = sanitize_text_field( wp_unslash( $id ) );

    $query = "SELECT * FROM `SET_TABLE_NAME` WHERE `id` = '" . $id . "'";

    $submissions = be_blue_api_read( $query, false, false );

    if( ! $submissions || count( $submissions ) == 0 ) {

        return array( 'msg_error' => '[0102]No submission found with id "' . ( $id ?? 'UNKNOW_ID') . '"', 'http_status' => 400 );

    }

    if( count( $submissions ) > 1 ) {

        be_blue_logs_register_log( 
            'ERROR', 
            'be_blue_api_get_submission_by_id', 
            '[0102]Submission "id" duplicate:' . ( $id ?? 'UNKNOW_ID')
        );

        return array( 'msg_error' => '[0103]Submission "' . ( $id ?? 'UNKNOW_ID') . '" with duplicated "id"' , 'http_status' => 100 );

    }

    $submission = $submissions[0];

    // Check if 'is active'
    if( $submission['is_active'] != 1 )
        return array( 'msg_error' => '[0104]Submission "' . ( $id ?? 'UNKNOW_ID') . '" deactivated/deleted' , 'http_status' => 400 );

    // Check if is allowed to read
    if( ! be_blue_api_check_read_permission( $submission ) )
        return array( 'msg_error' => '[0105]Permission denied for submission "' . ( $id ?? 'UNKNOW_ID') . '"', 'http_status' => 400 );

    return $submission;
}

function be_blue_api_get_submissions_by_object_name_handler( $request ) {

    $object_name = (string) $request['object'];

    $submissions = be_blue_api_get_submissions_by_object_name( $object_name );

    if ( $submissions['msg_error'] ) {

        return new WP_Error( 'rest_api_sad', esc_html__( $submissions['msg_error'], 'intranet-fafar-api' ), $submissions['http_status'] );

    }

    return rest_ensure_response( json_encode( $submissions ) );

}

function be_blue_api_get_submissions_by_object_name( $object_name ) {
    
    global $wpdb;

    $table_name = $wpdb->prefix . 'fafar_cf7crud_submissions';

    if( ! $object_name ) {

        return array( 'msg_error' => '[0201]No "object name" found.', 'http_status' => 500 );

    }

    $object_name = sanitize_text_field( wp_unslash( $object_name ) );
    
    $query = "SELECT * FROM `SET_TABLE_NAME` WHERE `object_name` = '" . $object_name . "'";

    $submissions = be_blue_api_read( $query );

    if( ! $submissions || count( $submissions ) == 0 ) {

        return array( 'msg_error' => '[0202]No submission found with id "' . ( $id ?? 'UNKNOW_ID') . '"', 'http_status' => 400 );

    }

    return $submissions;
}

/**
 * SIMPLE CREATE, READ, UPDATE and DELETE FUNCS
 * 
*/

function be_blue_api_create( $submission, $check_permissions = true ) {

    if ( ! isset( $submission['data'] ) )
        return array( 'error_msg' => 'No "data" column informed!' );

    global $wpdb;
  
    $table_name = $wpdb->prefix . 'fafar_cf7crud_submissions';

    $bytes              = random_bytes( 5 );
    $unique_hash        = time().bin2hex( $bytes ); 

    $submission['id']      = $unique_hash;
    $submission['form_id'] = $submission['form_id'] ?? '-2';
    $submission['data']    = json_encode( $submission['data'] );
  
    $wpdb->insert( $table_name, $submission );

    do_action( 'be_blue_api_after_create', $submission['id'] );

    return array( 'id' => $submission['id'] );
  
}

function be_blue_api_read( $query, $check_permissions = true, $check_is_active = true ){

    if ( ! $query )
        return array( 'error_msg' => 'No query str on "be_blue_api_read"!' );

    global $wpdb;

    $table_name = $wpdb->prefix . 'fafar_cf7crud_submissions';

    $query_completed = str_replace( 'SET_TABLE_NAME', $table_name, $query );
    
    $submissions = $wpdb->get_results( $query_completed, 'ARRAY_A' );

    if ( $submissions === null ) return array();

    if( ! $check_permissions && ! $check_is_active )
        return be_blue_api_decode_all_submissions_as_arr( $submissions );

    $submissions_checked = array();
    foreach( $submissions as $submission ) {
    
        // Check if 'is active'
        if( $check_is_active && 
            $submission['is_active'] != 1 )
            continue;
    
        // Check if is allowed to read
        if( $check_permissions &&
            ! be_blue_api_check_read_permission( $submission ) )
            continue;
    
        array_push( $submissions_checked,  $submission );
    
    }

    return be_blue_api_decode_all_submissions_as_arr( $submissions_checked );

}

function be_blue_api_update( $id, $new_data, $check_permissions = true ) {

    if ( ! $new_data || ! $id )
        return array( 'error_msg' => 'No ID or data informed!' );

    global $wpdb;
  
    $table_name = $wpdb->prefix . 'fafar_cf7crud_submissions';

    if ( isset( $new_data['data'] ) )
        $new_data['data'] = json_encode( $new_data['data'] );
  
    $wpdb->update( $table_name, $new_data, array( 'id' => $id ) );

    do_action( 'be_blue_api_after_update', $id, $new_data );

    return array( 'id' => $id, 'new_data' => $new_data );
  
}


/**
 * <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
 * PERMISSION FUNCTIONS BLOCK
 * START
 * 
 * Permission code digits:
 * 0 = ---
 * 1 = --x
 * 2 = -w-
 * 3 = -wx
 * 4 = r--
 * 5 = r-x
 * 6 = rw-
 * 7 = rwx
 */

function be_blue_api_check_read_permission( $submission, $user_id = null ) {
    
    $READ_DIGIT_VALUES = array( 4, 5, 6, 7 );

    return be_blue_api_check_permissions( $submission, $READ_DIGIT_VALUES, $user_id );

}

function be_blue_api_check_write_permission( $submission, $user_id = null ) {

    $WRITE_DIGIT_VALUES = array( 1, 3, 5, 7 );

    return be_blue_api_check_permissions( $submission, $WRITE_DIGIT_VALUES, $user_id );

}

function be_blue_api_check_exec_permission( $submission, $user_id = null ) {

    $EXEC_DIGIT_VALUES = array( 1, 3, 5, 7 );

    return be_blue_api_check_permissions( $submission, $EXEC_DIGIT_VALUES, $user_id );

}

function be_blue_api_check_permissions( $submission, $permission_digit_values, $user_id = null ) {

    $owner                              = (string) ( $submission['owner'] ?? 0 );
    $group_owner                        = (string) ( $submission['group_owner'] ?? 0 );
    $permissions                        = (string) ( $submission['permissions'] ?? '777' );

    $current_user_id                    = (string) ( $user_id ?? get_current_user_id() );
    $user_meta                          = get_userdata( $current_user_id );
    $user_roles                         = ( $user_meta->roles ?? array() ); // array( [0] => 'techs', ... )

    $OWNER_PERMISSION_DIGIT_INDEX       = 0;
    $OWNER_GROUP_PERMISSION_DIGIT_INDEX = 1;
    $OTHERS_PERMISSION_DIGIT_INDEX      = 2;

    /**
     * If the current user is the 'administrator', 
     * it gets instant permission.
    */
    if( in_array( 'administrator', $user_roles ) ) return true;

    // Do not has restriction
    if ( $permissions === '777' ) return true;
    
    // Current user is the owner
    if ( $owner === $current_user_id ) {

        $permission_value = (int) str_split( $permissions )[$OWNER_PERMISSION_DIGIT_INDEX];
        return in_array( $permission_value, $permission_digit_values, true );

    }

    /**
     * Group permissions
     * If user is on $group_owner.
     * $user_roles. Array. array( [0] => 'techs', ... )
     */
    if ( in_array( strtolower( $group_owner ), $user_roles ) )
    {

        $permission_value = (int) str_split( $permissions )[$OWNER_GROUP_PERMISSION_DIGIT_INDEX];
        return in_array( $permission_value, $permission_digit_values, true );
    
    }

    // Others permissions
    $permission_value = (int) str_split( $permissions )[$OTHERS_PERMISSION_DIGIT_INDEX];
    return in_array( $permission_value, $permission_digit_values, true );

}

/**
 * PERMISSION FUNCTIONS BLOCK
 * END
 * >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
 */



function be_blue_api_san( $v ) {
    $v = ( is_array( $v ) ? $v[0] : $v );
    return sanitize_text_field( wp_unslash( $v ) );
}


function be_blue_api_decode_all_submissions_as_arr( $arr ) {
    
    $s_arr = array();

    foreach ( $arr as $item ) {
        array_push( $s_arr, be_blue_api_get_submission_as_arr( $item ) );
    }

    return $s_arr;
}

/*
 * This function join all submissions properties(columns and json) 
 * from $wpdb->get_results in one php array.
 *
 * @since 1.0.0
 * @param mixed $submission Return from $wpdb->get_results
 * @return array $submission_joined  Submission joined
*/
function be_blue_api_get_submission_as_arr( $submission ) {

    return $submission;
    
    if ( ! $submission['data'] ) {

        be_blue_logs_register_log( 
            'ERROR', 
            'be_blue_api_get_submission_as_arr', 
            'submission ' . ( $submission['id'] ?? 'UNKNOW_ID') . ' do not have "data" column value' 
        );

        return $submission;

    }

    $submission_joined = json_decode( $submission['data'], true );

    print_r($submission);

    foreach ( $submission_joined as $key => $value ) {
    
        if ( $key === 'data' )
            continue;

        if ( is_array( $submission_joined[$key] ) &&
             count( $submission_joined[$key] ) > 1 ) {

            $submission_joined[$key] = $submission_joined[$key][0];
        
        } else {

            $submission_joined[$key] = $value ?? '--';

        }


        
    }

    return $submission_joined;
}