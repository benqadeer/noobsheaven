<?php
/**
 * @package    RG_Content_Engine
 * @author     Abdur-Rehman Qadeer
 * @copyright  2024 Abdur-Rehman Qadeer
 * @license    Proprietary
 */

namespace RG\ContentEngine\Keywords;

/**
 * Keyword Importer Class.
 *
 * @package RG\ContentEngine\Keywords
 */
class Importer {

	/**
	 * Import keywords from CSV.
	 *
	 * @param string $file_path Path to the CSV file.
	 * @return int Number of imported keywords.
	 */
	public function import_from_csv( string $file_path ): int {
		if ( ! file_exists( $file_path ) || ! is_readable( $file_path ) ) {
			return 0;
		}

		$handle = fopen( $file_path, 'r' );
		if ( ! $handle ) {
			return 0;
		}

		$header = fgetcsv( $handle );
		$count = 0;

		while ( ( $row = fgetcsv( $handle ) ) !== false ) {
			$data = array_combine( $header, $row );

			$keyword = new Keyword( [
				'keyword'       => $data['Keyword'] ?? $data['keyword'] ?? '',
				'search_volume' => $data['Search Volume'] ?? $data['volume'] ?? 0,
				'difficulty'    => $data['Difficulty'] ?? $data['kd'] ?? 0,
				'intent'        => $data['Intent'] ?? $data['intent'] ?? '',
				'priority'      => $data['Priority'] ?? $data['priority'] ?? 0,
				'notes'         => $data['Notes'] ?? $data['notes'] ?? '',
			] );

			if ( ! empty( $keyword->keyword ) ) {
				if ( $keyword->save() ) {
					$count++;
				}
			}
		}

		fclose( $handle );
		return $count;
	}
}
