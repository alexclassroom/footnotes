<?php // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
/**
 * Includes the Plugin Widget to put the Reference Container to the Widget area.
 *
 * @since 1.5.0
 *
 * @package footnotes
 * @subpackage public_widget
 */

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widget/class-footnotes-widget-base.php';

/**
 * Registers a Widget to put the Reference Container to the widget area.
 *
 * @since 1.5.0
 *
 * @package    footnotes
 * @subpackage public_widget
 */
class Footnotes_Widget_Reference_Container extends Footnotes_Widget_Base {

	/**
	 * The ID of this plugin.
	 *
	 * @since    2.8.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    2.8.0
	 * @param    string $plugin_name       The name of this plugin.
	 */
	public function __construct( $plugin_name ) {
		parent::__construct();
		$this->plugin_name = $plugin_name;
	}

	/**
	 * Returns an unique ID as string used for the Widget Base ID.
	 *
	 * @since 1.5.0
	 * @return string
	 */
	protected function get_id() {
		return 'footnotes_widget';
	}

	/**
	 * Returns the Public name of the Widget to be displayed in the Configuration page.
	 *
	 * @since 1.5.0
	 * @return string
	 */
	protected function get_name() {
		return $this->plugin_name;
	}

	/**
	 * Returns the Description of the child widget.
	 *
	 * @since 1.5.0
	 * @return string
	 *
	 * Edit: curly quotes 2.2.0
	 */
	protected function get_description() {
		return __( 'The widget defines the position of the reference container if set to “widget area”.', 'footnotes' );
	}

	/**
	 * Outputs the Settings of the Widget.
	 *
	 * @since 1.5.0
	 * @param mixed $instance The instance of the widget.
	 * @return void
	 *
	 * Edit: curly quotes 2.2.0
	 */
	public function form( $instance ) {
		echo __( 'The widget defines the position of the reference container if set to “widget area”.', 'footnotes' );
	}

	/**
	 * Outputs the Content of the Widget.
	 *
	 * @since 1.5.0
	 * @param mixed $args     The widget's arguments.
	 * @param mixed $instance The instance of the widget.
	 */
	public function widget( $args, $instance ) {
		global $footnotes;
		// Reference container positioning is set to "widget area".
		if ( 'widget' === Footnotes_Settings::instance()->get( Footnotes_Settings::C_STR_REFERENCE_CONTAINER_POSITION ) ) {
			// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $footnotes->a_obj_task->reference_container();
			// phpcs:enable
		}
	}
}
