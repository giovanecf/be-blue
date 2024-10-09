<?php

/**
 * Create a Income everytime a revenue is created by form
*/
add_action( 'fafar_cf7crud_after_create', 'be_blue_create_income_by_revenue' );

function be_blue_create_income_by_revenue( $id ) {

	$revenue = be_blue_api_get_submission_by_id( $id );

	$revenue_data = json_decode( $revenue['data'], true );

	be_blue_api_create_cash_flow( $revenue_data['desc'], 
								  $revenue_data['value'], 
								  'Entrada' );

}

/**
 * Routine to be execute on month start
*/
function be_blue_check_for_monthly() {

	$option_current_month = get_option( 'current_month', $default = false );

	//Nothing initialized
	// if ( ! $option_current_month )
	// 	return;

	// Month did not change
	// if ( be_blue_get_current_month() === $option_current_month )
	// 	return;

	
	//Month just changed


	error_log('OASDA>>>>>>>>>>>>>>>>>>>>>>>>>>>>>.');

	$expenses = be_blue_api_get_submissions_by_object_name( 'expense' );

	be_blue_handle_monthly_expenses( $expenses );


}

function be_blue_handle_monthly_expenses( $expenses = null ) {

	if ( ! $expenses )
		$expenses = be_blue_api_get_submissions_by_object_name( 'expense' );

	if ( isset( $expenses['msg_error'] ) )
		return;

	foreach ( $expenses as $expense ) {

		$expense_data = json_decode( $expense['data'], true );

		//print_r($expense);
		//print_r($expense_data);

		if ( $expense_data['frequency'][0] !== 'Mensal' )
			continue;

		if ( strtotime( $expense_data['bill_due'] ) > time() )
			continue; 

		if ( $expense_data['status'] === 'Paga' )
			be_blue_restart_expense( $expense  );
		else
			be_blue_handle_not_paided_expense( $expense );

	}

}

function be_blue_restart_expense( $expense = null ) {

	if ( ! $expense )
		return;

	$expense_data = json_decode( $expense['data'], true );

	$expense_data['status'] = 'Pendente';
	$expense_data['bill_due'] = be_blue_increase_month( $expense_data['bill_due'] );


	be_blue_api_update( $expense['id'], array( 'data' => $expense_data ) );

}

function be_blue_handle_not_paided_expense( $expense = null ) {

	if ( ! $expense )
		return;

	$new_single_expense = $expense;

	// Restart expense
	be_blue_restart_expense( $expense );

	// Create a new overdue single expense
	$expense_data = json_decode( $new_single_expense['data'], true );

	$expense_data['frequency'][0] = 'Avulsa';
	$expense_data['status'] = 'Vencida';

	$new_single_expense['data'] = $expense_data;

	be_blue_api_create( $new_single_expense );

}

function be_blue_desactive_not_monthly_expenses() {

	$expenses = be_blue_api_get_submissions_by_object_name( 'expenses' );

	foreach ( $expenses as $expense ) {

		$expense_data = json_decode( $expense['data'], true );
		
		if ( $expense_data['frenquency'] !== 'Mensal' ) continue;

	}

}

function be_blue_ckeck_for_options() {

	if ( ! get_option( 'current_month', $default = false ) )
		if( ! add_option( 'current_month', be_blue_get_current_month() ) )
			error_log( 'Option "current_month" not created!' );

}

function be_blue_get_current_month() {

	$d = new DateTime();

	return $d->format('m') . '/' . $d->format('Y');

}

function be_blue_increase_month( $date ) {

    $dateTime = new DateTime( $date );
    
	$dateTime->modify( '+1 month' );

    return $dateTime->format( 'Y-m-d' );

}


/**
 * Set a default trigger to automations
*/

add_action( 'loop_start', 'be_blue_default_trigger' );

function be_blue_default_trigger(){

	be_blue_ckeck_for_options();

    if( is_front_page() || is_home() ) {

        be_blue_check_for_monthly();
    
    }

}

