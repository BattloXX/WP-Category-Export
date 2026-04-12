<?php
/**
 * Plugin Name: Kategorie Post & Bild Exporter
 * Plugin URI:  https://github.com/BattloXX/WP-Category-Export
 * Description: Exportiert alle Beiträge einer Kategorie als CSV oder Excel (XLSX) und lädt die Headerbilder als ZIP herunter. Das Headerbild ist im Export eindeutig verlinkt und benannt.
 * Version:     1.0.0
 * Author:      BattloXX
 * Text Domain: kategorie-exporter
 * License:     GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* =========================================================================
 * Bootstrap
 * ======================================================================= */

add_action( 'admin_menu',  [ 'Kategorie_Exporter', 'register_menu' ] );
add_action( 'admin_init',  [ 'Kategorie_Exporter', 'handle_export'  ] );
add_action( 'admin_notices', [ 'Kategorie_Exporter', 'show_notices' ] );

class Kategorie_Exporter {

	const NONCE_ACTION = 'kategorie_export';
	const NONCE_FIELD  = '_wpnonce';
	const ACTION_FIELD = 'kat_export_action';
	const CAT_FIELD    = 'kat_export_cat_id';

	/* -----------------------------------------------------------------------
	 * Admin Menu
	 * --------------------------------------------------------------------- */

	public static function register_menu() {
		add_management_page(
			__( 'Kategorie Exporter', 'kategorie-exporter' ),
			__( 'Kategorie Exporter', 'kategorie-exporter' ),
			'manage_options',
			'kategorie-exporter',
			[ __CLASS__, 'render_page' ]
		);
	}

	/* -----------------------------------------------------------------------
	 * Admin Page HTML
	 * --------------------------------------------------------------------- */

	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Keine Berechtigung.', 'kategorie-exporter' ) );
		}

		$categories = get_categories( [ 'hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC' ] );
		$selected   = isset( $_GET['cat_id'] ) ? absint( $_GET['cat_id'] ) : 0;
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Kategorie Post & Bild Exporter', 'kategorie-exporter' ); ?></h1>

			<div style="background:#fff;padding:24px 28px;margin-top:16px;border:1px solid #c3c4c7;border-radius:4px;max-width:680px;">

				<p style="margin-top:0;color:#50575e;">
					<?php esc_html_e( 'Wähle eine Kategorie und exportiere alle veröffentlichten Beiträge als CSV oder Excel, oder lade alle Headerbilder als ZIP herunter. Der Dateiname des Bildes in der ZIP-Datei stimmt exakt mit dem Wert in der Spalte "Headerbild_Dateiname" im Export überein.', 'kategorie-exporter' ); ?>
				</p>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD ); ?>

					<table class="form-table" role="presentation" style="margin-bottom:0;">
						<tr>
							<th scope="row" style="padding-left:0;">
								<label for="kat_export_cat_id">
									<?php esc_html_e( 'Kategorie', 'kategorie-exporter' ); ?>
								</label>
							</th>
							<td style="padding-left:0;">
								<select name="<?php echo esc_attr( self::CAT_FIELD ); ?>" id="kat_export_cat_id" style="min-width:300px;">
									<option value=""><?php esc_html_e( '— Bitte wählen —', 'kategorie-exporter' ); ?></option>
									<?php foreach ( $categories as $cat ) : ?>
										<option value="<?php echo esc_attr( $cat->term_id ); ?>"
											<?php selected( $selected, $cat->term_id ); ?>>
											<?php echo esc_html( $cat->name ); ?>
											(<?php echo (int) $cat->count; ?>)
										</option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
					</table>

					<div style="margin-top:18px;display:flex;gap:10px;flex-wrap:wrap;">
						<button type="submit" name="<?php echo esc_attr( self::ACTION_FIELD ); ?>" value="csv"
							class="button button-primary">
							&#8595; <?php esc_html_e( 'Als CSV exportieren', 'kategorie-exporter' ); ?>
						</button>
						<button type="submit" name="<?php echo esc_attr( self::ACTION_FIELD ); ?>" value="xlsx"
							class="button button-primary">
							&#8595; <?php esc_html_e( 'Als Excel (XLSX) exportieren', 'kategorie-exporter' ); ?>
						</button>
						<button type="submit" name="<?php echo esc_attr( self::ACTION_FIELD ); ?>" value="zip"
							class="button button-secondary"
							style="background:#2271b1;border-color:#2271b1;color:#fff;">
							&#128247; <?php esc_html_e( 'Bilder als ZIP herunterladen', 'kategorie-exporter' ); ?>
						</button>
					</div>

					<p style="margin-bottom:0;margin-top:14px;color:#787c82;font-size:12px;">
						<?php esc_html_e( 'Es werden nur veröffentlichte Beiträge berücksichtigt. Beiträge ohne Headerbild erscheinen im Export, jedoch nicht in der ZIP-Datei.', 'kategorie-exporter' ); ?>
					</p>
				</form>
			</div>
		</div>
		<?php
	}

	/* -----------------------------------------------------------------------
	 * Admin notices (error feedback)
	 * --------------------------------------------------------------------- */

	public static function show_notices() {
		if ( ! empty( $_GET['kat_export_error'] ) ) {
			$code = sanitize_key( $_GET['kat_export_error'] );
			$messages = [
				'no_cat'      => __( 'Bitte eine Kategorie auswählen.', 'kategorie-exporter' ),
				'invalid_cat' => __( 'Ungültige Kategorie.', 'kategorie-exporter' ),
				'no_posts'    => __( 'Diese Kategorie enthält keine veröffentlichten Beiträge.', 'kategorie-exporter' ),
				'no_images'   => __( 'Keine Headerbilder in dieser Kategorie gefunden.', 'kategorie-exporter' ),
				'zip_fail'    => __( 'Die ZIP-Erstellung ist fehlgeschlagen. Bitte prüfe die PHP ZipArchive-Erweiterung.', 'kategorie-exporter' ),
			];
			$msg = $messages[ $code ] ?? __( 'Unbekannter Fehler.', 'kategorie-exporter' );
			printf(
				'<div class="notice notice-error is-dismissible"><p><strong>Kategorie Exporter:</strong> %s</p></div>',
				esc_html( $msg )
			);
		}
	}

	/* -----------------------------------------------------------------------
	 * Export Router (runs on admin_init before any output)
	 * --------------------------------------------------------------------- */

	public static function handle_export() {
		if ( empty( $_POST[ self::ACTION_FIELD ] ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Keine Berechtigung.', 'kategorie-exporter' ) );
		}

		if ( ! isset( $_POST[ self::NONCE_FIELD ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_FIELD ] ) ), self::NONCE_ACTION ) ) {
			wp_die( esc_html__( 'Sicherheitscheck fehlgeschlagen.', 'kategorie-exporter' ) );
		}

		$cat_id = absint( $_POST[ self::CAT_FIELD ] ?? 0 );
		$action = sanitize_key( $_POST[ self::ACTION_FIELD ] );

		// Validate category
		if ( ! $cat_id ) {
			self::redirect_error( 'no_cat' );
		}
		$cat = get_category( $cat_id );
		if ( is_wp_error( $cat ) || ! $cat ) {
			self::redirect_error( 'invalid_cat' );
		}

		// Fetch posts
		$posts = self::get_posts( $cat_id );
		if ( empty( $posts ) ) {
			self::redirect_error( 'no_posts' );
		}

		$rows = self::build_rows( $posts );

		// Extend execution time & memory for large exports
		@set_time_limit( 0 );
		@ini_set( 'memory_limit', '256M' );

		switch ( $action ) {
			case 'csv':
				self::send_csv( $rows, $cat );
				break;
			case 'xlsx':
				self::send_xlsx( $rows, $cat );
				break;
			case 'zip':
				self::send_zip( $posts, $cat );
				break;
			default:
				self::redirect_error( 'invalid_cat' );
		}
	}

	/* -----------------------------------------------------------------------
	 * Data helpers
	 * --------------------------------------------------------------------- */

	private static function get_posts( int $cat_id ): array {
		$q = new WP_Query( [
			'cat'            => $cat_id,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'no_found_rows'  => true,
			'orderby'        => 'date',
			'order'          => 'DESC',
		] );
		$posts = $q->posts;
		wp_reset_postdata();
		return $posts ?: [];
	}

	/**
	 * Returns the ZIP-safe image filename: "{post_id}_{original_basename}"
	 * This exact string is stored in the export column AND used as the ZIP entry name.
	 */
	private static function image_zip_name( int $post_id, int $thumb_id ): string {
		$path = get_attached_file( $thumb_id );
		if ( ! $path ) {
			return '';
		}
		return $post_id . '_' . basename( $path );
	}

	private static function build_rows( array $posts ): array {
		$headers = [
			'ID',
			'Titel',
			'Datum',
			'Autor',
			'URL',
			'Auszug',
			'Kategorien',
			'Headerbild_URL',
			'Headerbild_Dateiname',
		];

		$rows = [ $headers ];

		foreach ( $posts as $post ) {
			$thumb_id        = get_post_thumbnail_id( $post->ID );
			$thumb_url       = $thumb_id ? wp_get_attachment_url( $thumb_id ) : '';
			$thumb_zip_name  = $thumb_id ? self::image_zip_name( $post->ID, $thumb_id ) : '';

			$cats = wp_get_post_categories( $post->ID, [ 'fields' => 'all' ] );
			$cat_names = is_array( $cats ) ? implode( ', ', wp_list_pluck( $cats, 'name' ) ) : '';

			$excerpt = $post->post_excerpt;
			if ( empty( $excerpt ) ) {
				$excerpt = wp_trim_words( strip_shortcodes( $post->post_content ), 30, '…' );
			}

			$rows[] = [
				$post->ID,
				$post->post_title,
				$post->post_date,
				get_the_author_meta( 'display_name', $post->post_author ),
				get_permalink( $post ),
				$excerpt,
				$cat_names,
				$thumb_url,
				$thumb_zip_name,
			];
		}

		return $rows;
	}

	/* -----------------------------------------------------------------------
	 * CSV export
	 * --------------------------------------------------------------------- */

	private static function send_csv( array $rows, object $cat ): void {
		$filename = 'kategorie-' . sanitize_title( $cat->name ) . '-' . gmdate( 'Y-m-d' ) . '.csv';

		while ( ob_get_level() ) {
			ob_end_clean();
		}
		nocache_headers();
		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

		$out = fopen( 'php://output', 'w' );
		// UTF-8 BOM so Excel opens it correctly
		fwrite( $out, "\xEF\xBB\xBF" );

		foreach ( $rows as $row ) {
			fputcsv( $out, array_map( 'strval', $row ), ';' );
		}

		fclose( $out );
		exit;
	}

	/* -----------------------------------------------------------------------
	 * XLSX export (no external library — pure OpenXML via ZipArchive)
	 * --------------------------------------------------------------------- */

	private static function send_xlsx( array $rows, object $cat ): void {
		if ( ! class_exists( 'ZipArchive' ) ) {
			self::redirect_error( 'zip_fail' );
		}

		$filename = 'kategorie-' . sanitize_title( $cat->name ) . '-' . gmdate( 'Y-m-d' ) . '.xlsx';
		$tmp      = tempnam( sys_get_temp_dir(), 'kat_xlsx_' );

		// Register cleanup in case of fatal error
		register_shutdown_function( function() use ( $tmp ) {
			if ( file_exists( $tmp ) ) {
				@unlink( $tmp );
			}
		} );

		// --- Build shared strings & sheet rows ---------------------------
		$strings       = [];  // value => index
		$string_order  = [];  // index => value

		$add_string = function( string $val ) use ( &$strings, &$string_order ): int {
			if ( ! isset( $strings[ $val ] ) ) {
				$strings[ $val ] = count( $string_order );
				$string_order[]  = $val;
			}
			return $strings[ $val ];
		};

		$col_letters = [ 'A','B','C','D','E','F','G','H','I','J','K','L','M',
		                 'N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
		                 'AA','AB','AC','AD','AE','AF' ];

		$sheet_rows_xml = '';
		foreach ( $rows as $row_idx => $row ) {
			$row_num = $row_idx + 1;
			$sheet_rows_xml .= '<row r="' . $row_num . '">';
			foreach ( array_values( $row ) as $col_idx => $cell_val ) {
				$col_ref  = ( $col_letters[ $col_idx ] ?? 'A' ) . $row_num;
				$cell_val = (string) $cell_val;
				$si       = $add_string( $cell_val );
				$sheet_rows_xml .= '<c r="' . esc_attr( $col_ref ) . '" t="s"><v>' . $si . '</v></c>';
			}
			$sheet_rows_xml .= '</row>';
		}

		// --- Shared strings XML ------------------------------------------
		$ss_count  = count( $string_order );
		$ss_items  = '';
		foreach ( $string_order as $str ) {
			$ss_items .= '<si><t xml:space="preserve">' . self::xml_escape( $str ) . '</t></si>';
		}
		$shared_strings_xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
			. '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
			. ' count="' . $ss_count . '" uniqueCount="' . $ss_count . '">'
			. $ss_items . '</sst>';

		// --- sheet1.xml --------------------------------------------------
		$col_count  = count( $rows[0] ?? [] );
		$row_count  = count( $rows );
		$last_col   = $col_letters[ $col_count - 1 ] ?? 'A';
		$sheet_ref  = 'A1:' . $last_col . $row_count;

		$sheet_xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
			. '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
			. ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
			. '<sheetData>' . $sheet_rows_xml . '</sheetData>'
			. '</worksheet>';

		// --- workbook.xml ------------------------------------------------
		$workbook_xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
			. '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
			. ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
			. '<sheets><sheet name="Export" sheetId="1" r:id="rId1"/></sheets>'
			. '</workbook>';

		// --- styles.xml (minimal — required by Excel) --------------------
		$styles_xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
			. '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
			. '<fonts count="1"><font><sz val="11"/><name val="Calibri"/></font></fonts>'
			. '<fills count="2">'
			. '<fill><patternFill patternType="none"/></fill>'
			. '<fill><patternFill patternType="gray125"/></fill>'
			. '</fills>'
			. '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
			. '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
			. '<cellXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/></cellXfs>'
			. '</styleSheet>';

		// --- Relationships -----------------------------------------------
		$rels_xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
			. '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
			. '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument"'
			. ' Target="xl/workbook.xml"/>'
			. '</Relationships>';

		$wb_rels_xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
			. '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
			. '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet"'
			. ' Target="worksheets/sheet1.xml"/>'
			. '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings"'
			. ' Target="sharedStrings.xml"/>'
			. '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles"'
			. ' Target="styles.xml"/>'
			. '</Relationships>';

		// --- Content Types -----------------------------------------------
		$ct_xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
			. '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
			. '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
			. '<Default Extension="xml" ContentType="application/xml"/>'
			. '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
			. '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
			. '<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>'
			. '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
			. '</Types>';

		// --- Assemble ZIP ------------------------------------------------
		$zip = new ZipArchive();
		if ( $zip->open( $tmp, ZipArchive::OVERWRITE ) !== true ) {
			self::redirect_error( 'zip_fail' );
		}

		$zip->addFromString( '[Content_Types].xml',              $ct_xml );
		$zip->addFromString( '_rels/.rels',                      $rels_xml );
		$zip->addFromString( 'xl/workbook.xml',                  $workbook_xml );
		$zip->addFromString( 'xl/_rels/workbook.xml.rels',       $wb_rels_xml );
		$zip->addFromString( 'xl/worksheets/sheet1.xml',         $sheet_xml );
		$zip->addFromString( 'xl/sharedStrings.xml',             $shared_strings_xml );
		$zip->addFromString( 'xl/styles.xml',                    $styles_xml );
		$zip->close();

		// --- Stream to browser -------------------------------------------
		while ( ob_get_level() ) {
			ob_end_clean();
		}
		nocache_headers();
		header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Content-Length: ' . filesize( $tmp ) );

		readfile( $tmp );
		unlink( $tmp );
		exit;
	}

	/* -----------------------------------------------------------------------
	 * Images ZIP export
	 * --------------------------------------------------------------------- */

	private static function send_zip( array $posts, object $cat ): void {
		if ( ! class_exists( 'ZipArchive' ) ) {
			self::redirect_error( 'zip_fail' );
		}

		$filename = 'bilder-' . sanitize_title( $cat->name ) . '-' . gmdate( 'Y-m-d' ) . '.zip';
		$tmp      = tempnam( sys_get_temp_dir(), 'kat_zip_' );

		register_shutdown_function( function() use ( $tmp ) {
			if ( file_exists( $tmp ) ) {
				@unlink( $tmp );
			}
		} );

		$zip = new ZipArchive();
		if ( $zip->open( $tmp, ZipArchive::OVERWRITE ) !== true ) {
			self::redirect_error( 'zip_fail' );
		}

		$added = 0;
		foreach ( $posts as $post ) {
			$thumb_id = get_post_thumbnail_id( $post->ID );
			if ( ! $thumb_id ) {
				continue;
			}

			$file_path = get_attached_file( $thumb_id );

			// Fallback: if file is offloaded (e.g. WP Offload Media), try original URL
			if ( ! $file_path || ! file_exists( $file_path ) ) {
				$file_path = self::download_remote_image( $thumb_id );
				if ( ! $file_path ) {
					continue;
				}
				$zip_name = self::image_zip_name( $post->ID, $thumb_id ) ?: ( $post->ID . '_' . basename( $file_path ) );
				$zip->addFile( $file_path, $zip_name );
				// temp file — we'll clean up after zip->close()
				$added++;
				continue;
			}

			$zip_name = self::image_zip_name( $post->ID, $thumb_id );
			if ( $zip_name ) {
				$zip->addFile( $file_path, $zip_name );
				$added++;
			}
		}

		$zip->close();

		if ( $added === 0 ) {
			unlink( $tmp );
			self::redirect_error( 'no_images' );
		}

		while ( ob_get_level() ) {
			ob_end_clean();
		}
		nocache_headers();
		header( 'Content-Type: application/zip' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Content-Length: ' . filesize( $tmp ) );

		readfile( $tmp );
		unlink( $tmp );
		exit;
	}

	/* -----------------------------------------------------------------------
	 * Utility: download remote image to temp file (offloaded media fallback)
	 * --------------------------------------------------------------------- */

	private static function download_remote_image( int $thumb_id ): string {
		$url = wp_get_attachment_url( $thumb_id );
		if ( ! $url ) {
			return '';
		}

		$response = wp_remote_get( $url, [ 'timeout' => 30, 'sslverify' => false ] );
		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return '';
		}

		$ext  = pathinfo( parse_url( $url, PHP_URL_PATH ), PATHINFO_EXTENSION );
		$tmp  = tempnam( sys_get_temp_dir(), 'kat_img_' ) . ( $ext ? '.' . $ext : '' );
		file_put_contents( $tmp, wp_remote_retrieve_body( $response ) );

		register_shutdown_function( function() use ( $tmp ) {
			if ( file_exists( $tmp ) ) {
				@unlink( $tmp );
			}
		} );

		return $tmp;
	}

	/* -----------------------------------------------------------------------
	 * Utility: safe XML attribute/text escaping
	 * --------------------------------------------------------------------- */

	private static function xml_escape( string $str ): string {
		return htmlspecialchars( $str, ENT_XML1 | ENT_QUOTES, 'UTF-8' );
	}

	/* -----------------------------------------------------------------------
	 * Utility: redirect back to admin page with error code
	 * --------------------------------------------------------------------- */

	private static function redirect_error( string $code ): void {
		wp_safe_redirect(
			add_query_arg(
				[ 'page' => 'kategorie-exporter', 'kat_export_error' => $code ],
				admin_url( 'tools.php' )
			)
		);
		exit;
	}
}
