<?php
/*
Plugin Name: Convert Myisam To Innodb
Description: Using this plugin we can convert MyISAM storage engine type to InnoDB.
Author: Mehdi Binam
Version: 1.2
Author URI: https://github.com/mehdibinam/convert-myisam-to-innodb
License: GPLv2 or later
Text Domain: Convert Myisam To Innodb
*/
		add_action('admin_menu', 'SMTI_add_option');
		include_once(ABSPATH . 'wp-includes/pluggable.php');
		// current user's info 
		$current_user = wp_get_current_user(); 
		if ( !($current_user instanceof WP_User) ) 
    	return; 
		function SMTI_add_option(){
        	add_submenu_page( 'options-general.php', 'Convert MyISAM to InnoDB', 'Convert MyISAM to InnoDB', 'manage_options', basename(__FILE__), 'SMTI_manage_update');
		}
		function SMTI_manage_update(){
		global $wpdb;
	    $list_of_table = $wpdb->get_results("SHOW TABLE STATUS");
		?>
		<style type="text/css">
			table.db-rp-outer {
				width: 100%;
				border-collapse: collapse;
				margin-top: 20px;
				table-layout: fixed;
			}
			#wpbody {
				padding: 1rem;
			}
			table.db-rp-inner {
				width: 100%;
				border-collapse: collapse;
				margin: 0 10px;
				background-color: #ffffff;
				border-radius: 8px;
				overflow: hidden;
			}
			table.db-rp-inner th {
				background-color: #325acb;
				color: #ffffff;
				padding: 12px;
				text-align: left;
				border: 1px solid #d2d2d2;
				font-weight: 500;
			}
			table.db-rp-inner td {
				padding: 10px;
				border: 1px solid #d2d2d2;
				font-size: 14px;
				color: #333333;
				overflow: hidden;
				text-overflow: ellipsis;
				white-space: nowrap;
			}
			table.db-rp-inner th:first-child,
			table.db-rp-inner td:first-child {
				width: 60%;
			}
			table.db-rp-inner th:nth-child(2),
			table.db-rp-inner td:nth-child(2) {
				width: 0%;
			}
			table.db-rp-inner th:nth-child(3),
			table.db-rp-inner td:nth-child(3) {
				width: 0%;
			}
			table.db-rp-inner tr:nth-child(even) {
				background-color: #f5f5f5;
			}
			.status-myisam {
				background-color: #ff0000;
				color: #ffffff;
				padding: 6px 12px;
				border-radius: 0;
			}
			.status-innodb {
				background-color: #009900;
				color: #ffffff;
				padding: 6px 12px;
				border-radius: 0;
			}
			.warning-message {
				position: fixed;
				top: 40px;
				left: 10px;
				background-color: #ffd700;
				padding: 10px;
				border-radius: 8px;
				font-weight: bold;
				color: #333333;
				z-index: 1000;
				box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
				display: flex;
				width: 89%;
				flex-direction: row-reverse;
				align-items: center;
			}
			.warning-controls {
				margin-left: auto;
			}
			.warning-controls label {
				margin-right: 10px;
				font-weight: 500;
				color: #325acb;
				font-size: 14px;
			}
			.warning-controls label input {
				margin-right: 5px;
			}
			input.submit-floating {
				background-color: #325acb;
				color: #ffffff;
				border: none;
				padding: 10px 30px;
				border-radius: 5px;
				font-size: 14px;
				font-weight: 500;
				cursor: pointer;
				transition: background-color 0.4s ease-in-out;
			}
			input.submit-floating:hover {
				background-color: #000000;
			}
	</style>
		<form name="SMTI_form" action="admin.php?page=<?php echo basename(__FILE__); ?>" method="post">
			<?php wp_nonce_field('SMTI_submit','SMTI_nonce'); ?>
			<table class="db-rp-outer">
				<tr>
				<?php
				$num_columns = 3; // Changed to 3 columns
				$total = count($list_of_table);
				$per_col = ceil($total / $num_columns);
				$chunks = array_chunk($list_of_table, $per_col);
				foreach ($chunks as $chunk) {
				?>
					<td valign="top">
						<table class="db-rp-inner">
							<tr>
								<th>Table Name</th>
								<th align="center">Status</th>
								<th align="center">Upgrade</th>
							</tr>
							<?php
							foreach($chunk as $check) {
							?>
								<tr>
									<td><?php echo $check->Name; ?></td>
									<td align="center" class="<?php echo ($check->Engine != 'InnoDB') ? 'status-myisam' : 'status-innodb'; ?>"><?php echo $check->Engine; ?></td>
									<td align="center"><input name="tables[]" type="checkbox" value="<?php echo esc_attr($check->Name); ?>" <?php if($check->Engine=='InnoDB') {?> disabled <?php } ?>></td>
								</tr>
							<?php
							}
							?>
						</table>
					</td>
				<?php
				}
				?>
				</tr>
			</table>
			<div class="warning-message">
				<span>⚠️We always recommend backing up your MySQL database before using this plugin⚠️</span>
				<div class="warning-controls">
					<label for="selectall"><input type="checkbox" id="selectall" onclick="SMTI_toggle(this);"> Select All</label>
					<input type="submit" name="SMTI_form_submit" class="submit-floating" value="Submit">
				</div>
			</div>
		</form>
		<script type="text/javascript">
			function SMTI_toggle(source) {
				var checkboxes = document.querySelectorAll('input[name="tables[]"]:not(:disabled)');
				for (var i = 0; i < checkboxes.length; i++) {
					checkboxes[i].checked = source.checked;
				}
			}
		</script>
		<?php
		if ( isset( $_POST['SMTI_form_submit'] ) && !check_admin_referer('SMTI_submit','SMTI_nonce')){	
				$table_checked = esc_attr($_POST['tables']);
			echo '<div id="message" class="error fade"><p><strong>'.__('ERROR','simple-myisam-to-innodb').' - '.__('Please try again.','simple-myisam-to-innodb').'</strong></p></div>';
		}
		elseif( isset( $_POST['SMTI_form_submit'] ) && isset($_POST['SMTI_nonce']) )
		{
			$table_checked = $_POST['tables'];
			//print_r($table_checked);
			if(!empty($table_checked)) {
			foreach($table_checked as $table)
			{
					$repair_db = $wpdb->query("ALTER TABLE $table ENGINE=INNODB");
					if(!$repair_db) {
						echo '<p style="color: red;">'.esc_html($table).' Engine Type could not be upgraded!</p>';
					} else {
						echo '<p style="color: green;align:middle;">'.esc_html($table).' Engine Type is upgraded!</p>';
					}
				}
			}
			else
			{
				echo '<p style="color: red;"><strong>'.__('ERROR','simple-myisam-to-innodb').' - '.__('Please select a table to upgrade!.','simple-myisam-to-innodb').'</strong></p>';
			}
			?>
			<script type="text/javascript">
				window.location.href = '<?php echo $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']; ?>';
			</script>
			<?php
		}
	 }  
	 add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'SMTI_add_settings_link' );
	 function SMTI_add_settings_link( $links ) {
		 $settings_link = '<a href="options-general.php?page=' . basename(__FILE__) . '">' . __( 'Settings' ) . '</a>';
		 array_push( $links, $settings_link );
		 return $links;
	 }
?>