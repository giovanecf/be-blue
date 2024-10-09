<?php

add_shortcode( 'be_blue_dashboard', 'be_blue_dashboard' );

add_shortcode( 'be_blue_excluir_objeto', 'be_blue_excluir_objeto' );

add_shortcode( 'be_blue_vizualizar_objeto', 'be_blue_vizualizar_objeto' );

add_shortcode( 'be_blue_atualizar_status_despesa', 'be_blue_atualizar_status_despesa' );

add_shortcode( 'be_blue_importar_extrato', 'be_blue_importar_extrato' );

function be_blue_dashboard() {

    $revenues = be_blue_api_get_submissions_by_object_name( 'revenue' );

    $expenses = be_blue_api_get_submissions_by_object_name( 'expense' );

    $cash_flows = be_blue_api_get_submissions_by_object_name( 'cash_flow' );

    if ( isset( $revenues['msg_error'] ) )
        $revenues = array();

    if ( isset( $expenses['msg_error'] ) )
        $expenses = array();

    if ( isset( $cash_flows['msg_error'] ) )
        $cash_flows = array();

    $has_expenses_overdue = be_blue_update_overdue_expenses( $expenses );

    if ( $has_expenses_overdue )
        $expenses = be_blue_api_get_submissions_by_object_name( 'expense' );

    $revenues_total            = get_total_revenues( $revenues );
    $expenses_total            = get_total_expenses( $expenses );
    $expenses_pending_total    = get_total_expenses_by_status( $expenses, 'Pendente' );
    $expenses_overdue_total    = get_total_expenses_by_status( $expenses, 'Vencida' );
    $expenses_paid_total       = get_total_expenses_by_status( $expenses, 'Paga' );
    $gross_balance             = $revenues_total - $expenses_total;
    $net_balance               = $revenues_total - $expenses_paid_total;
    $is_gross_balance_negative = ( $gross_balance < 0 ? 1 : 0 );
    $is_net_balance_negative   = ( $net_balance < 0 ? 1 : 0 );

    $income_total                  = $revenues_total;
    $outcome_total                 = get_total_cash_flow( $cash_flows, 'Saída' );
    $cash_flow_balance             = $income_total - $outcome_total;
    $is_cash_flow_balance_negative = ( $cash_flow_balance < 0 ? 1 : 0 );

    $current_month       = date( 'F/Y', time() );

	echo '
        <div class="d-flex flex-column" style="gap: 4rem;">

            <div class="d-flex flex-column gap-4">
                <h3>' . $current_month . '</h3>

                <div class="d-flex flex-wrap gap-2">

                    <div class="card" style="width: 9rem;" >
                        <div class="card-body d-flex flex-column justify-content-between">
                            <p class="card-title">Receita</p>
                            <h4 class="card-text"> $ ' . $revenues_total . ' </h4>
                        </div>
                    </div>

                    <div class="card" style="width: 9rem;" >
                        <div class="card-body d-flex flex-column justify-content-between">
                            <p class="card-title">Despesas</p>
                            <h4 class="card-text"> $ ' . $expenses_total . ' </h4>
                        </div>
                    </div>

                    <div class="card" style="width: 9rem;" >
                        <div class="card-body d-flex flex-column justify-content-between">
                            <p class="card-title">Balanço<br/>Bruto</p>
                            <h4 class="card-text ' . ( $is_gross_balance_negative ? 'text-danger' : 'text-success' ) . '"> $ ' . $gross_balance . ' </h4>
                        </div>
                    </div>

                    <div class="card" style="width: 9rem;" >
                        <div class="card-body d-flex flex-column justify-content-between">
                            <p class="card-title">Balanço<br/>Líquido</p>
                            <h4 class="card-text ' . ( $is_net_balance_negative ? 'text-danger' : 'text-success' ) . '"> $ ' . $net_balance . ' </h4>
                        </div>
                    </div>

                <!--</div>


                <div class="d-flex gap-4">-->

                    <div class="card" style="width: 9rem;" >
                        <div class="card-body d-flex flex-column justify-content-between">
                            <p class="card-title">Despesas<br/>Pendentes</p>
                            <h4 class="card-text"> $ ' . $expenses_pending_total . ' </h4>
                        </div>
                    </div>

                    <div class="card" style="width: 9rem;" >
                        <div class="card-body d-flex flex-column justify-content-between">
                            <p class="card-title">Despesas<br/>Vencidas</p>
                            <h4 class="card-text"> $ ' .  $expenses_overdue_total . ' </h4>
                        </div>
                    </div>

                    <div class="card" style="width: 9rem;" >
                        <div class="card-body d-flex flex-column justify-content-between">
                            <p class="card-title">Despesas<br/>Pagas</p>
                            <h4 class="card-text"> $ ' .  $expenses_paid_total . ' </h4>
                        </div>
                    </div>

                <!--</div>

                <div class="d-flex gap-4">-->

                    <div class="card" style="width: 9rem;" >
                        <div class="card-body d-flex flex-column justify-content-between">
                            <p class="card-title">Entradas</p>
                            <h4 class="card-text"> $ ' . $income_total . ' </h4>
                        </div>
                    </div>

                    <div class="card" style="width: 9rem;" >
                        <div class="card-body d-flex flex-column justify-content-between">
                            <p class="card-title">Saídas</p>
                            <h4 class="card-text"> $ ' . $outcome_total . ' </h4>
                        </div>
                    </div>

                    <div class="card" style="width: 9rem;" >
                        <div class="card-body d-flex flex-column justify-content-between">
                            <p class="card-title">Balanço<br/>Líquido</p>
                            <h4 class="card-text ' . ( $is_cash_flow_balance_negative ? 'text-danger' : 'text-success' ) . '"> $ ' . $cash_flow_balance . ' </h4>
                        </div>
                    </div>

                </div>

            </div>



            <div class="d-flex flex-column" style="gap: 4rem;">
            
                <div>
                    <h3><i class="bi bi-bar-chart"></i> Estatísticas</h3>
                    <div>Estatíticas</div>
                </div>

                <div>
                    <div class="d-flex justify-content-between">
                        <h3><i class="bi bi-arrow-up-square"></i> Receitas</h3>
                        <div>
                            <a href="./adicionar-receita/" class="btn btn-success text-decoration-none"><i class="bi bi-plus"></i> Receita</a>
                        </div>
                    </div>
                    <div id="wrapper_revenues"></div>
                </div>

                <div>
                    <div class="d-flex justify-content-between">
                        <h3><i class="bi bi-arrow-down-square"></i> Despesas</h3>
                        <div>
                            <a href="./adicionar-despesa/" class="btn btn-danger text-decoration-none"><i class="bi bi-plus"></i> Despesa</a>
                        </div>
                    </div>
                    <div id="wrapper_expenses"></div>
                </div>
                
                <div>
                    <div class="d-flex justify-content-between">
                        <h3><i class="bi bi-arrow-down-up"></i> Movimentações</h3>
                        <div>
                            <a href="./adicionar-movimentacao/" class="btn btn-secondary text-decoration-none"><i class="bi bi-plus"></i> Movimentação</a>
                        </div>
                    </div>
                    <div id="wrapper_cash_flow"></div>
                </div>

            </div>

        </div>
        
        <script> 
            var revenues = ' . json_encode( $revenues, JSON_UNESCAPED_SLASHES ) . '; 
            var expenses = ' . json_encode( $expenses, JSON_UNESCAPED_SLASHES ) . ';
            var cash_flows = ' . json_encode( $cash_flows, JSON_UNESCAPED_SLASHES ) . ';
        </script>
        ';

}

function be_blue_excluir_objeto() {

    if ( is_admin() ) return;

    if ( ! isset( $_GET['id'] ) ) {

        echo '
            <script>
                alert("No ID passed!");
                window.history.back();
            </script>
        ';

        return;

    }

    if ( ! isset( $_GET['confirmed'] ) ) {

        echo '<script>
                const r = confirm("\nExcluir objeto?\n\nNão poderá ser desfeito!\n");

                if(r){
                    window.location.href = "./excluir-objeto/?id=' . $_GET['id'] . '&confirmed=1";
                } else {
                    window.history.back();
                }
            </script>';

        return;

    }

    global $wpdb;

    $table_name = $wpdb->prefix . 'fafar_cf7crud_submissions';

    $id = sanitize_text_field( $_GET['id'] );

    $res = $wpdb->delete(
        $table_name,
        array(
            'id' => $id,
        )
    );

    if ( ! $res ) {

        echo '
            <script>
                alert("No object found!");
                window.history.back();
            </script>
        ';

        return;

    }

    echo '
        <script> 
            window.history.back();
        </script>
    ';

}

function be_blue_vizualizar_objeto() {

    if ( is_admin() ) return;

    if ( ! isset( $_GET['id'] ) ) {

        echo '
            <script>
                alert("No ID passed!");
                window.history.back();
            </script>
        ';

        return;

    }

    $id = sanitize_text_field( $_GET['id'] );

    $submission = be_blue_api_get_submission_by_id( $id );

    echo '<pre>';
    print_r( $submission );
    echo '</pre>';

}

function be_blue_atualizar_status_despesa() {

    if ( is_admin() ) return;

    if ( ! isset( $_GET['id'] ) || 
         ! isset( $_GET['status'] ) ) {

        echo '
            <script>
                alert("No ID or/and Status passed!");
                window.history.back();
            </script>
        ';

        return;

    }

    $id     = sanitize_text_field( $_GET['id'] );
    $status = sanitize_text_field( $_GET['status'] );
    

    if ( ! isset( $_GET['confirmed'] ) ) {

        echo '<script>
                const r = confirm("\nAtualizar despesa para ' . $status . '?\n");

                if(r){
                    window.location.href = "./atualizar-status-despesa/?id=' . $id . '&status=' . $status . '&confirmed=1";
                } else {
                    window.history.back();
                }
            </script>';

        return;

    }

    $res = be_blue_api_update_status_expense( $id, $status );

    if ( ! $res ) {

        echo '
            <script>
                alert("Error on updating!");
                window.history.back();
            </script>
        ';

        return;

    }

    echo '
        <script> 
            window.history.back();
        </script>
    ';

}

function be_blue_importar_extrato() {

    global $wpdb;

    $table_name = $wpdb->prefix . 'fafar_cf7crud_submissions';


    if( isset( $_POST["json_string"] ) ) {

        $json_string = $_POST["json_string"];

        print_r("Processando.....");
        print_r("<br/>");
        //print_r($json_string);

        //$json_d = json_decode( preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $json_string), true );
        $json_d = json_decode(stripslashes($json_string));

        //print_r($json_d);

        print_r("<br/>");
        print_r("JSON LAST ERROR: ");
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                echo ' - No errors';
            break;
            case JSON_ERROR_DEPTH:
                echo ' - Maximum stack depth exceeded';
            break;
            case JSON_ERROR_STATE_MISMATCH:
                echo ' - Underflow or the modes mismatch';
            break;
            case JSON_ERROR_CTRL_CHAR:
                echo ' - Unexpected control character found';
            break;
            case JSON_ERROR_SYNTAX:
                echo ' - Syntax error, malformed JSON';
            break;
            case JSON_ERROR_UTF8:
                echo ' - Malformed UTF-8 characters, possibly incorrectly encoded';
            break;
            default:
                echo ' - Unknown error';
            break;
        }

        print_r("<br/>");

        $total_rows = count( $json_d );
        $count = 0;
        foreach( $json_d as $json_item ) {

            print_r($json_item);
            print_r("<br/>");
            print_r( (($count++)/$total_rows*100) . "%" );
            print_r("<br/>");

            $bytes       = random_bytes(5);
            $unique_hash = time().bin2hex($bytes); 
        
            $form_post_id      = -1;
            $form_data_as_json = json_encode( $json_item );
        
            $wpdb->insert( $table_name, array(
                'id'          => $unique_hash,
                'form_id'     => $form_post_id,
                'object_name' => $_POST['input_object_name'] ?? '',
                'data'        => $form_data_as_json,
                'permissions' => $_POST['input_permissions'] ?? '777',
                'owner'       => ($_POST['input_owner'] ?? ''),
                'group_owner' => ($_POST['input_group_owner'] ?? null)
            ) );

        }

    }


    echo '
    
        <form class="my-3" action="/importar-extrato" method="POST">

            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="floatingInput" name="input_object_name"/>
                <label for="floatingInput">Nome do Objeto</label>
            </div>

            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="fasd" name="input_owner"/>
                <label for="fasd">Dono</label>
            </div>

            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="gsdge" name="input_group_owner"/>
                <label for="gsdge">Grupo Dono</label>
            </div>

            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="asdasd" name="input_permissions"/>
                <label for="asdasd">Permissões</label>
            </div>

            <div class="form-floating mb-3">
                <textarea class="form-control" placeholder="Insira o texto JSON aqui" id="floatingTextarea" name="json_string" rows="15" required></textarea>
                <label for="floatingTextarea">Texto JSON</label>
            </div>

            <button type="submit">
                <i class="bi bi-file-earmark-arrow-up"></i>
                Importar
            </button>
        </form>

    ';

}

function get_total_outcomes( $outcomes ) {

    $total_outcome = array( 'total' => 0 );

    foreach ( $outcomes as $outcome ) {

        $data = json_decode( $outcome['data'], TRUE );

        $status = $data['status'][0];

        if ( isset( $total_outcome[$status] ) ) $total_outcome[$status] += (float) $data['value'];
        else $total_outcome[$status] = (float) $data['value'];

        $total_outcome['total'] += (float) $data['value'];

    }

    return $total_outcome;

}

function get_total_revenues( $submissions ) {

    $total = 0;

    foreach ( $submissions as $submission ) {

        $data = json_decode( $submission['data'], TRUE );

        $total += (float) $data['value'];

    }

    return $total;

}

function get_total_expenses( $submissions ) {

    $total = 0;

    foreach ( $submissions as $submission ) {

        $data = json_decode( $submission['data'], TRUE );

        $total += (float) $data['value'];

    }

    return $total;

}

function get_total_expenses_by_status( $submissions, $status ) {

    $total = 0;

    foreach ( $submissions as $submission ) {

        $data = json_decode( $submission['data'], TRUE );

        if ( $data['status'] != $status ) continue;

        $total += (float) $data['value'];

    }

    return $total;

}

function get_total_cash_flow( $submissions, $type ) {

    $total = 0;

    foreach ( $submissions as $submission ) {

        $data = json_decode( $submission['data'], TRUE );

        if ( $data['type'] != $type ) continue;

        $total += (float) $data['value'];

    }

    return $total;

}

function be_blue_update_overdue_expenses( $expenses ) {

    $has_expenses_overdue = false;

    foreach ( $expenses as $expense ) {

        if ( be_blue_is_expense_overdue( $expense ) && 
             ! be_blue_is_expense_paid( $expense ) ) {

            $expense_data = json_decode( $expense['data'], true );

            $res = be_blue_api_update_status_expense( $expense['id'], 'Vencida' );

            if ( $res == false ) return false;

            $has_expenses_overdue = true;

        }

    }

    return $has_expenses_overdue;

}

function be_blue_is_expense_overdue( $expense ) {

    $expense_data = json_decode( $expense['data'], true );

    $bill_due_in_timestamp = be_blue_api_get_timestamp( $expense_data['bill_due'] );

    return ( time() > $bill_due_in_timestamp );

}

function be_blue_is_expense_paid( $expense ) {

    $expense_data = json_decode( $expense['data'], true );

    return ( $expense_data['status'] == 'Paga' );

}