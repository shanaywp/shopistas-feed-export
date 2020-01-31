<?php
/**
 * WooFeedExport settings
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WOO_FEED_Settings {

	public function __construct() {

		add_action('admin_menu',array($this, 'add_setting_nav'));
		add_action('admin_enqueue_scripts', array($this, 'WOO_FEED_setting_style_and_script'));
		add_action('init', array($this, 'save_all_setting'));
		include_once WOO_FEED_ABSPATH . '/includes/export.php';
	}

	public function WOO_FEED_setting_style_and_script() {

		wp_enqueue_style( 'cta-style', WOO_FEED_PLUGIN_URL . '/css/style.css');
	}

	public function add_setting_nav() {

		add_menu_page(__('Shopistas Feed Export', 'shopistas-feed-export'), __('Shopistas Feed Export', 'shopistas-feed-export'), 'manage_options', 'WOO_FEED_setting', array($this, 'WOO_FEED_setting_content'), '', 90);
 	}

	public function WOO_FEED_setting_content() {

		include WOO_FEED_ABSPATH . 'view/html-cta-settings.php';
	}

	public function add_fields() {

		$field = array();

		$path = trailingslashit( WP_CONTENT_URL ) . 'uploads/woo-feeds/wc-export.xml';

		$field[] = array(
			'name' 	=> __('Enabled?', 'shopistas-feed-export'),
			'desc' 	=> __('Product XML Feeds for WooCommerce.', 'shopistas-feed-export'),
			'id' 	=> 'woo_feed_enable_feed',
			'std' 	=> '',
			'option'=> array( 'yes' => __('Yes', 'shopistas-feed-export'), 'no' => __('No', 'shopistas-feed-export')),
			'type' 	=> 'select');

		$field[] = array(
			'name' 	=> __('XMl File Name', 'shopistas-feed-export'),
			'desc' 	=> sprintf( __( 'Enter custom XML file name. By default name is wc-export.xml %2$s File Path: %1$s', 'shopistas-feed-export' ), $path, '<br>' ),
			'id' 	=> 'woo_feed_xml_file_name',
			'std' 	=> '',
			'type' 	=> 'text');

		$field[] = array(
			'name' 	=> __('Updated Period', 'shopistas-feed-export'),
			'desc' 	=> __('Sets the number of minutes feed file creation script is allowed to run. Default 120 minutes set.', 'shopistas-feed-export'),
			'id' 	=> 'woo_feed_updated_period',
			'std' 	=> '',
			'type' 	=> 'text');

		return $field;
	}

	public function get_download_file_url() {
		$arr_option = json_decode(get_option('WOO_FEED_settings', true));
		$upload_dir = wp_upload_dir();
		$file_dir_path = $upload_dir['basedir'];
		if (file_exists($upload_dir['basedir'] . '/woo-feeds')) {
			if ($arr_option->woo_feed_xml_file_name != '') {
				$file_dir_path = trailingslashit( WP_CONTENT_URL ) . 'uploads/woo-feeds/'.sanitize_file_name( str_replace( '.xml', '', $arr_option->woo_feed_xml_file_name ) . '.xml' );	
			} else {
				$file_dir_path = trailingslashit( WP_CONTENT_URL ) . 'uploads/woo-feeds/wc-export.xml';
			}
		}
		return $file_dir_path;
	}

	public function check_file_exist() {
		$arr_option = json_decode(get_option('WOO_FEED_settings', true));
		$upload_dir = wp_upload_dir();
		$file_dir_path = $upload_dir['basedir'];

		if ($arr_option->woo_feed_xml_file_name != '' && file_exists($upload_dir['basedir'] . '/woo-feeds/' .sanitize_file_name( str_replace( '.xml', '', $arr_option->woo_feed_xml_file_name ) . '.xml' ) )) {
			return true;
		} else if ($arr_option->woo_feed_xml_file_name == '' && file_exists($upload_dir['basedir'] . '/woo-feeds/wc-export.xml')) {
			return true;
		}
		return false;
	}

	public function prepare_fields() {

		$fields = $this->add_fields();
		$arr_option = json_decode(get_option('WOO_FEED_settings', true));
		$file_dir_path = $this->get_download_file_url();

		$html = '';
		$html .= '<table class="form-table">';
			$html .= '<tbody>';
				
				foreach ($fields as $id => $field) {
					
					$id = $field['id']; 
					if($arr_option->$id != '') {

						$value = $arr_option->$id;
					} else {

						$value = '';
					}

					switch ($field['type']) {

						case "colorpicker":

							$html .= '<tr valign="top">
											<th scope="row" class="titledesc">
												<label for="'.$field['id'].'">'.$field['name'].'</label>
											</th>
											<td class="form-colorpicker">
												<input name="'.$field['id'].'" id="'.$field['id'].'" type="text" style="" value="'.$value.'" class="" placeholder="'.$field['name'].'">
												<span class="woo-feed-help-tip">'.$field['desc'].'</span>
											</td>
										</tr>';
							break;

						case "select":

							$option = '';
							
							if(!empty($field['option'])) {

								$selected = '';
								foreach ($field['option'] as $id => $optionval) {

									if($id == $value) {

										$option .= '<option value="'.$id.'" selected="selected">'.$optionval.'</option>';
									} else {

										$option .= '<option value="'.$id.'">'.$optionval.'</option>';
									}
								}
							} else {

								$option .= '<option value="Yes">Yes</option>';
							}
							$html .= '<tr valign="top">
											<th scope="row" class="titledesc">
												<label for="'.$field['id'].'">'.$field['name'].'</label>
											</th>
											<td class="form-colorpicker">
												<select name="'.$field['id'].'" id="'.$field['id'].'" placeholder="'.$field['name'].'">'.$option.'</select>
												<span class="woo-feed-help-tip">'.$field['desc'].'</span>
											</td>
										</tr>';
							break;

						default:

							$html .= '<tr valign="top">
											<th scope="row" class="titledesc">
												<label for="'.$field['id'].'">'.$field['name'].'</label>
											</th>
											<td class="form-colorpicker">
												<input name="'.$field['id'].'" id="'.$field['id'].'" type="text" style="" value="'.$value.'" class="" placeholder="'.$field['name'].'">
												<span class="woo-feed-help-tip">'.$field['desc'].'</span>
											</td>
										</tr>';
							break;
					}
				}
			$html .= '</tbody>';
			$html .= '</tfoot>';
				$html .= '</tr>';
					$html .= '<td></td>';
					$html .= '<td>';
						$html .= '<p class="submit">';
							$html .= '<input type="submit" name="submit" id="submit" class="button button-primary" value="'.__('Save Changes', 'shopistas-feed-export').'">';
							if ($this->check_file_exist()) {
								$html .= '<a href="'.$file_dir_path.'" class="download-xml button button-primary" title="'.__('Download XML', 'shopistas-feed-export').'" download>'.__('Download XML', 'shopistas-feed-export').'</a>';
							}
						$html .= '</p>';
					$html .= '</td>';
				$html .= '</tr>';
			$html .= '</tfoot>';
		$html .= '</table>';
		return $html;
	}

	public function save_all_setting() {
		
		if (isset($_POST['woo_feed_enable_feed']) && $_POST['woo_feed_enable_feed'] == '' ) {
			add_action( 'admin_notices', array($this, 'WOO_FEED_err_msg' ));
			return;
		}

		if (isset( $_POST['_WOO_FEED_settings'] ) && wp_verify_nonce( $_POST['_WOO_FEED_settings'],'save_WOO_FEED_settings' )){
			update_option('WOO_FEED_settings', json_encode($_POST));
			add_action( 'admin_notices', array($this, 'WOO_FEED_update_setting' ));
			/*if (class_exists('Export_event')) {
				$export = new Export_event;
				$export->generate_xml_file();
			}*/
		}
	}

	public function WOO_FEED_update_setting() {
	    ?>
	    <div class="updated notice">
	        <p><?php _e( 'Settings are saved successfully.', 'shopistas-feed-export' ); ?></p>
	    </div>
	    <?php
	}

	public function WOO_FEED_err_msg() {
	    ?>
	    <div class="error notice">
	        <p><?php _e( 'Plese enter value in required field', 'shopistas-feed-export' ); ?></p>
	    </div>
	    <?php
	}

	public function error_msg_add_cart() {
		echo '<div class="woocommerce-message error" role="alert">'.__('Product cannot be added to the cart.', 'shopistas-feed-export').'</div>';
	}
}
new WOO_FEED_Settings;