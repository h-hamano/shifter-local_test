<?php
final class Shifter_Tips {
	private $widget_name = 'Shifter';
	public function initialize() {
		// Show Welcome content. See templates/welcome.php
		add_action( 'welcome_panel', array( $this, 'render_welcome_contents' ) );
		// Add some scripts on the footer. See templates/footer.php
		add_action( 'admin_footer', array( $this, 'render_admin_footer' ) );
		// Add dashboard widget in welcome sceen. See templates/welcome.php
		// If you want to hide it, please comment it out.
		add_action( 'wp_dashboard_setup', array( $this, 'render_welcome_widgets' ) );
	}
	public function render_welcome_contents() {
		require_once('templates/welcome.php');
	}
	public function render_admin_footer() {
		require_once('templates/footer.php');
	}
	public function render_welcome_widgets() {
		wp_add_dashboard_widget( 'shifter_dashboard_widget', $this->widget_name, array( $this, 'shifter_dashboard_widget_function' ) );
		global $wp_meta_boxes;
		$normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];	
		$example_widget_backup = array( 'shifter_dashboard_widget' => $normal_dashboard['shifter_dashboard_widget'] );
		unset( $normal_dashboard['shifter_dashboard_widget'] );

		$sorted_dashboard = array_merge( $example_widget_backup, $normal_dashboard );
		$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
	}
	function shifter_dashboard_widget_function() {
		require_once('templates/welcome-widget.php');
	} 
}