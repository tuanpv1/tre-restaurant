<?php
/**
 * WooCommerce Taxonomies Translations: Export to Excel
 * @author      WPGlobus
 * @category    Admin
 * @package     WPGlobus-WC/Admin
 */

/**
 * Cannot just run it because the caller function runs in `plugins_loaded`,
 * and we do not have any terms there yet.
 */
add_action( 'admin_init', 'wpglobus_wc_translations_export' );

function wpglobus_wc_translations_export() {

	$d = new WPGlobus_WC_Translations_table( true, $_POST['wpglobus_wc_term_language'] );

	$data = array(
		array(
			'Section',
			'Taxonomy',
			'Slug',
			'ID',
			'Source',
			WPGlobus::Config()->en_language_name[ WPGlobus::Config()->default_language ],
			WPGlobus::Config()->en_language_name[ $_POST['wpglobus_wc_term_language'] ],
			//				'Singular'
		)
	);

	foreach ( $d->data as $row ) {

		$table_row = array();

		switch ( $row['type'] ) :
			case 'header' :
				$table_row = array( strip_tags( $row['text'] ), '' );
				break;
			case 'standard' :
				$table_row = array(
					'',
					$row['taxonomy'],
					$row['slug'],
					$row['ID'],
					$row['source'],
					$row[ WPGlobus::Config()->default_language ],
					$row[ $_POST['wpglobus_wc_term_language'] ],
					//						$row['singular']
				);
				break;
		endswitch;

		$data[] = $table_row;

	}

	$writer = new XLSXWriter();
	$writer->writeSheet( $data );

	header( 'Content-Description: File Transfer' );
	header( 'Content-type: application/xlsx' );
	header( 'Content-Type: application/force-download' );// some browsers need this
	header( 'Content-Disposition: attachment; filename="wpglobus_wc_translations.xlsx"' );
	header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
	header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
	header( 'Pragma: no-cache' );

	$writer->writeToStdOut();
	exit;
}

# --- EOF
