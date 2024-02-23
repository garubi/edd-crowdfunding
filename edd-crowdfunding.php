<?php
/*
Plugin Name: Edd Crowdfunding shortcode
Version: 2.0
Plugin URI: tupamaros.it
Description: Utilizzo: <code>[edd_crowdfunding target="3500" mode="Raccogli tutto" launch="01-01-2021" deadline="31-12-2021"]</code> <br> <code>target</code> è l'obbiettivo di raccolta; <code>mode</code> è la modalità di raccolta, è un testo libero, scrivi quello che vuoi; <code>launch</code> è la data di inizio della campagna; <code>deadline</code> è la data di scadenza della campagna, una volta superata verrà scritto "campagna terminata"
Author: Stefano Garuti  
Author URI: stefano@garuti.it
*/
if ( defined( 'EDD_PLUGIN_DIR' ) ) {
    include_once EDD_PLUGIN_DIR . 'includes/class-edd-stats.php';
    include_once EDD_PLUGIN_DIR . 'includes/payments/class-payment-stats.php';
}

if ( !class_exists('EDD_Payment_Stats')) return;

class CROWDF_Payment_Stats extends EDD_Payment_Stats {
    public function get_pledges( $start_date = false, $end_date = false, $include_taxes = true ) {
        $this->setup_dates( $start_date, $end_date );

		// Make sure start date is valid
		if ( is_wp_error( $this->start_date ) ) {
			return $this->start_date;
		}

		// Make sure end date is valid
		if ( is_wp_error( $this->end_date ) ) {
			return $this->end_date;
		}
        /**
         * Filters Order statuses that should be included when calculating stats.
         *         *
         * @param array $statuses Order statuses to include when generating stats.
         */
        $statuses = apply_filters( 'crowdf_pledges_stats_post_statuses', edd_get_net_order_statuses() );

        // Global earning stats
        $args = array(
            'post_type'              => 'edd_payment',
            'nopaging'               => true,
            'post_status'            => $statuses,
            'fields'                 => 'ids',
            'update_post_term_cache' => false,
            'suppress_filters'       => false,
            'start_date'             => $this->start_date, // These dates are not valid query args, but they are used for cache keys
            'end_date'               => $this->end_date,
            'edd_transient_type'     => 'edd_pledges', // This is not a valid query arg, but is used for cache keying
            'include_taxes'          => $include_taxes,
        );

        $args   = apply_filters( 'crowdf_stats_pledges_args', $args );
        $cached = get_transient( 'crowdf_stats_pledges' );
        $key    = md5( wp_json_encode( $args ) );

        if ( ! isset( $cached[ $key ] ) ) {
            if ( empty( $cached ) ) {
                $cached = array();
            }
            $orders = edd_get_orders( array(
                'type'          => 'sale',
                'status__in'    => $args['post_status'],
                'date_query'    => array(
                    array(
                        'after'     => array(
                            'year'  => date( 'Y', $this->start_date ),
                            'month' => date( 'm', $this->start_date ),
                            'day'   => date( 'd', $this->start_date ),
                        ),
                        'before'    => array(
                            'year'  => date( 'Y', $this->end_date ),
                            'month' => date( 'm', $this->end_date ),
                            'day'   => date( 'd', $this->end_date ),
                        ),
                        'inclusive' => true,
                    ),
                ),
                'no_found_rows' => true,
                'number'    => 0,
            ) );

            $earnings = 0;

            if ( $orders ) {
                $total_earnings = 0.00;
                $total_tax      = 0.00;


                foreach ( $orders as $order ) {
                    $total_earnings += $order->total;
                    $total_tax      += $order->tax;
                }

                $earnings = apply_filters( 'crowdf_pledges_stats_earnings_total', $total_earnings, $orders, $args );

                if ( false === $include_taxes ) {
                    $earnings -= $total_tax;
                }
            }

            // Cache the results for one hour
            $cached[ $key ] = $earnings;
            set_transient( 'crowdf_stats_pledges', $cached, HOUR_IN_SECONDS );
        }

        $result = $cached[ $key ];

		return round( $result, edd_currency_decimal_filter() );

    }

}

add_shortcode( 'edd_crowdfunding', 'edd_crowdfunding_shortcode' );
function edd_crowdfunding_shortcode( $atts ) {
    if ( !class_exists('EDD_Payment_Stats')) return;

	// Attributes
	$atts = shortcode_atts(
		array(
			'launch' => '01-01-2021',
            'target'    => 0,
            'mode'      => 'Raccogli tutto',
            'deadline'   => '01-02-2021',
		),
		$atts,
		'edd_crowdfunding'
	);
    
    // if( $atts['deadline'] ){
    //     $atts['deadline'] = str_replace( '/', '-', $atts['deadline'] );
        $now = new DateTime( );
        $dead = new DateTime( $atts['deadline'] );
        $interval = $dead->diff( $now );
        $diff = (int) $interval->format( '%r%a' );
        $deadline = ($diff > 0 )? 'Campagna terminata' : abs($diff) . ' giorni rimanenti';
    // }

    $start = new DateTime( $atts['launch'] );
    $start_date = $start->format('Y/m/d');
    $end_date = $dead->format('Y/m/d');
    // $end_date = false;
    // echo $start_date;
    // echo ' - ';
    // echo $end_date; 

    // $stats = new EDD_Payment_Stats;
    $stats = new CROWDF_Payment_Stats;
    // $earnings   = $stats->get_earnings( '', $start_date, $end_date );
    $earnings   = $stats->get_pledges( $start_date, $end_date );
    // var_dump( $earnings );  
    $earnings   = number_format_i18n( $earnings, 2 );
    $sales      = $stats->get_sales( 0, $start_date, $end_date );
    $sales      = number_format_i18n( $sales );
    $target     = $atts['target'];
    $target     = number_format_i18n( $target );
    $mode       = $atts['mode']; 
    
    $template = "
        <div class=\"crowdfunding-box\">
        <ul class=\"crowdfunding-data\">
        <li class=\"grid-system \"><span class=\"label-funded\">Raccolti: </span><b class=\"value value-funded\">€&nbsp;$earnings</b></li>
        <li class=\"grid-system\"><span class=\"label-target\">Obiettivo: </span><b class=\"value value-target\">€&nbsp;$target</b></li>
        <li class=\"grid-system\"><span class=\"label-sales\">Sostenitori: </span><b class=\"value value-sales\">$sales</b></li>
        <li class=\"grid-system\">
            <span class=\"label-deadline\">Scadenza: </span>
            <b class=\"value value-deadline\">
            
                <span>$deadline <span class=\"dashicons dashicons-calendar-alt\" title=\"La campagna va dal $start_date al $end_date\" style=\"color:#bbb; vertical-align:middle;\"></span></span>
                
            </b>
        
        </li>
        <li class=\"grid-system\"><span class=\"label-mode\">Modalità: </span><b class=\"value value-mode\">$mode</b></li>
        </ul>
        </div>
    ";
    return $template;
}

add_action( 'edd_complete_purchase', 'crowdfunding_reset_earnings' );
function crowdfunding_reset_earnings(){
    delete_transient('crowdf_stats_pledges');
}

function crowdfunding_load_dashicons(){
    wp_enqueue_style('dashicons');
    // https://unpkg.com/microtip/microtip.css
    // wp_enqueue_style( 'microtip', plugin_dir_url( __FILE__ ) . '/microtip.css', array(), null );
    // https://github.com/ghosh/microtip
    wp_enqueue_style( 'microtip', 'https://unpkg.com/microtip/microtip.css', array(), null );
}
add_action('wp_enqueue_scripts', 'crowdfunding_load_dashicons');

