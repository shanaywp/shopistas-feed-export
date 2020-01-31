<?php
/**
 * Handles Batch XML export.
 *
 * Based on https://pippinsplugins.com/batch-processing-for-big-data/
 *
 * @package  WooCommerce/Export
 * @version  3.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Include dependencies.
 */
if ( ! class_exists( 'WC_XML_Exporter', false ) ) {
	require_once WOO_FEED_ABSPATH . 'includes/export/abstract-wc-xml-exporter.php';
}

/**
 * WC_XML_Exporter Class.
 */
abstract class WC_XML_Batch_Exporter extends WC_XML_Exporter {

	/**
	 * Page being exported
	 *
	 * @var integer
	 */
	protected $page = 1;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->column_names = $this->get_default_column_names();
	}

	/**
	 * Get file path to export to.
	 *
	 * @return string
	 */
	protected function get_file_path() {

		$upload_dir = wp_upload_dir();
		$file_dir_path = $upload_dir['basedir'];
		if (!file_exists($upload_dir['basedir'] . '/woo-feeds')) {
		    mkdir($upload_dir['basedir'] . '/woo-feeds', 0755, true);
		}
		if (file_exists($upload_dir['basedir'] . '/woo-feeds')) {
		    $file_dir_path = $upload_dir['basedir'] . '/woo-feeds';
		}
		return trailingslashit( $file_dir_path ) . $this->get_filename();
	}

	/**
	 * Get the file contents.
	 *
	 * @since 3.1.0
	 * @return string
	 */
	public function get_file() {
		$file = '';
		if ( @file_exists( $this->get_file_path() ) ) { // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged
			$file = @file_get_contents( $this->get_file_path() ); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents, WordPress.WP.AlternativeFunctions.file_system_read_file_get_contents
		} else {
			@file_put_contents( $this->get_file_path(), '' ); // phpcs:ignore WordPress.VIP.FileSystemWritesDisallow.file_ops_file_put_contents, Generic.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
			@chmod( $this->get_file_path(), 0664 ); // phpcs:ignore WordPress.VIP.FileSystemWritesDisallow.chmod_chmod, WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents, Generic.PHP.NoSilencedErrors.Discouraged
		}
		return $file;
	}

	/**
	 * Serve the file and remove once sent to the client.
	 *
	 * @since 3.1.0
	 */
	public function export() {
		$this->send_headers();
		$this->send_content( $this->get_file() );
		@unlink( $this->get_file_path() ); // phpcs:ignore WordPress.VIP.FileSystemWritesDisallow.file_ops_unlink, Generic.PHP.NoSilencedErrors.Discouraged
		die();
	}

	/**
	 * Generate the XML file.
	 *
	 * @since 3.1.0
	 */
	public function generate_file() {
		if ( 1 === $this->get_page() ) {
			@unlink( $this->get_file_path() ); // phpcs:ignore WordPress.VIP.FileSystemWritesDisallow.file_ops_unlink, Generic.PHP.NoSilencedErrors.Discouraged,
		}
		$this->prepare_data_to_export();
		$xml_product_str = implode('', $this->get_xml_data());
		$xml_product_str = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $xml_product_str);
		$this->write_xml_data( $xml_product_str );
	}

	/**
	 * Write data to the file.
	 *
	 * @since 3.1.0
	 * @param string $data Data.
	 */
	protected function write_xml_data( $data ) {
		
		$xmlString = '<?xml version="1.0" encoding="UTF-8"?>
		    			<shopistasstore>
		    				<created_at>'.$this->get_current_date_time().'</created_at>
		    				<products>'.$data.'</products>
		    			</shopistasstore>';

		$dom = new DOMDocument;
		$dom->preserveWhiteSpace = FALSE;
		$dom->loadXML($xmlString);

		//Save XML as a file
		$dom->save( $this->get_file_path() );
	}

	public function get_current_date_time() {

		return date( 'Y-m-d H:i', current_time( 'timestamp', 0 ) );
	}

	/**
	 * Get page.
	 *
	 * @since 3.1.0
	 * @return int
	 */
	public function get_page() {
		return $this->page;
	}

	/**
	 * Set page.
	 *
	 * @since 3.1.0
	 * @param int $page Page Nr.
	 */
	public function set_page( $page ) {
		$this->page = absint( $page );
	}

	/**
	 * Get count of records exported.
	 *
	 * @since 3.1.0
	 * @return int
	 */
	public function get_total_exported() {
		return ( ( $this->get_page() - 1 ) * $this->get_limit() ) + $this->exported_row_count;
	}

	/**
	 * Get total % complete.
	 *
	 * @since 3.1.0
	 * @return int
	 */
	public function get_percent_complete() {
		return $this->total_rows ? floor( ( $this->get_total_exported() / $this->total_rows ) * 100 ) : 100;
	}
}
