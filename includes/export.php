<?php
class Export_event {

	public $settings = array();

	public $selected_interval = '';

	public $xml_file_name = '';

	public function __construct() {

		$this->settings = json_decode(get_option('WOO_FEED_settings', true));
		
		$this->selected_interval = (!empty($this->settings->woo_feed_updated_period) && $this->settings->woo_feed_updated_period > 0) ? $this->settings->woo_feed_updated_period : '120';

		$this->selected_interval = ceil($this->selected_interval * 60);
		
		$this->xml_file_name = (!empty($this->settings->woo_feed_xml_file_name)) ? $this->settings->woo_feed_xml_file_name : '';

		if ($this->settings->woo_feed_enable_feed == 'yes' || empty($this->settings->woo_feed_enable_feed)) {

			add_action( 'init',           array( $this, 'schedule_the_events' ) );
			add_action( 'admin_init',     array( $this, 'schedule_the_events' ) );
			add_filter( 'cron_schedules', array( $this, 'cron_add_custom_intervals' ) );
			add_action( 'woo_feed_create_products_xml_hook', array( $this, 'create_products_xml_cron' ), PHP_INT_MAX, 2 );
		}
	}

	/**
	 * cron_add_custom_intervals.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function cron_add_custom_intervals( $schedules ) {
		
		$schedules['secondly'] = array(
			'interval' => $this->selected_interval,
			'display' => __( 'Once a Hour', 'product-xml-feeds-for-woocommerce' )
		);
		
		return $schedules;
	}

	/**
	 * On an early action hook, check if the hook is scheduled - if not, schedule it.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	public function schedule_the_events() {

		$update_intervals  = 'secondly';
		$event_hook = 'woo_feed_create_products_xml_hook';
		if ( $this->settings->woo_feed_enable_feed == 'yes' ) {
			
			$event_timestamp = wp_next_scheduled( $event_hook );
			
			if ( $this->selected_interval ) {
				update_option( 'woo_feed_create_products_xml_cron_time', gmdate("H:i:s", $event_timestamp) );
			}
			if ( !$event_timestamp ) {
				wp_schedule_event( time(), $update_intervals, $event_hook );
			} else if ( $event_timestamp ) {
				wp_unschedule_event( $event_timestamp, $event_hook );
			}

		} else {
			// unschedule all events
			update_option( 'woo_feed_create_products_xml_cron_time', '' );
			$event_timestamp = wp_next_scheduled( $event_hook, array( $update_intervals ) );
			if ( $event_timestamp ) {
				wp_unschedule_event( $event_timestamp, $event_hook, array( $update_intervals ) );
			}
		}
	}

	public function create_products_xml_cron() {

		$this->generate_xml_file();
	}

	public function generate_xml_file() {

		include_once WOO_FEED_ABSPATH . 'includes/export/class-wc-product-xml-exporter.php';

		if (class_exists('WC_Product_XML_Exporter')) {
			$exporter = new WC_Product_XML_Exporter();
			$exporter->set_product_category_to_export( wp_unslash( array_values( array() ) ) );
			if ($this->xml_file_name != '') {
				$exporter->set_filename( wp_unslash( $this->xml_file_name ) );
			}
			$exporter->generate_file();

			// For Additional functionality in future development
			/*if ( ! empty( $_POST['columns'] ) ) { // WPCS: input var ok.
				$exporter->set_column_names( wp_unslash( $_POST['columns'] ) ); // WPCS: input var ok, sanitization ok.
			}

			if ( ! empty( $_POST['selected_columns'] ) ) { // WPCS: input var ok.
				$exporter->set_columns_to_export( wp_unslash( $_POST['selected_columns'] ) ); // WPCS: input var ok, sanitization ok.
			}

			if ( ! empty( $_POST['export_meta'] ) ) { // WPCS: input var ok.
				$exporter->enable_meta_export( true );
			}

			if ( ! empty( $_POST['export_types'] ) ) { // WPCS: input var ok.
				$exporter->set_product_types_to_export( wp_unslash( $_POST['export_types'] ) ); // WPCS: input var ok, sanitization ok.
			}*/
		}
	}
}
new Export_event;