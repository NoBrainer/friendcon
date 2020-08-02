<?php
namespace dao;

use Constants as Constants;
use http\Exception\RuntimeException;
use util\Param as Param;
use util\Sql as Sql;

class Uploads {

	public static function add(int $teamIndex, int $challengeIndex, string $file): bool {
		$query = "INSERT INTO uploads (teamIndex, challengeIndex, file) VALUES (?, ?, ?)";
		return Sql::executeSql($query, 'iis', $teamIndex, $challengeIndex, $file);
	}

	public static function delete(string $file): bool {
		throw new RuntimeException("Uploads::delete() NOT YET IMPLEMENTED."); //TODO
	}

	public static function exists(string $file): bool {
		$fullPath = sprintf('%s/%s', Constants::uploadsDir(), $file);
		return file_exists($fullPath);
	}

	public static function get(string $file): ?array {
		$query = "SELECT u.*, s.state, c.published FROM uploads u " .
				"JOIN uploadState s ON u.state = s.value " .
				"JOIN challenges c ON u.challengeIndex = c.challengeIndex " .
				"WHERE u.file = ?";
		$result = Sql::executeSqlForResult($query, 's', $file);
		if (!Sql::hasRows($result, 1)) return null;
		$row = Sql::getNextRow($result);
		return [
				'file'           => Param::asString($row['file']),
				'challengeIndex' => Param::asInteger($row['challengeIndex']),
				'teamIndex'      => Param::asInteger($row['teamIndex']),
				'state'          => Param::asString($row['state']),
				'rotation'       => Param::asInteger($row['rotation']),
				'uploadTime'     => Param::asTimestamp($row['uploadTime']),
				'published'      => Param::asBoolean($row['published'])
		];
	}

	public static function getAll(bool $publishedOnly = true): array {
		$condition = ($publishedOnly ? " WHERE u.state > 0 AND c.published = 1" : "");
		$query = <<< SQL
			SELECT u.*, s.state, c.published
			FROM uploads u
			JOIN uploadState s ON u.state = s.value
			JOIN challenges c ON u.challengeIndex = c.challengeIndex
			$condition
		SQL;
		$result = Sql::executeSqlForResult($query);

		// Build the data array
		$uploads = [];
		while ($row = Sql::getNextRow($result)) {
			// Build and append the entry
			$uploads[] = [
					'file'           => Param::asString($row['file']),
					'challengeIndex' => Param::asInteger($row['challengeIndex']),
					'teamIndex'      => Param::asInteger($row['teamIndex']),
					'state'          => Param::asString($row['state']),
					'rotation'       => Param::asInteger($row['rotation']),
					'uploadTime'     => Param::asTimestamp($row['uploadTime']),
					'published'      => Param::asBoolean($row['published'])
			];
		}
		return $uploads;
	}

	public static function getStateValue(string $stateStr): ?int {
		$result = Sql::executeSqlForResult("SELECT * FROM uploadState WHERE state = ?", 's', $stateStr);
		if (!Sql::hasRows($result, 1)) {
			return null;
		}
		$row = Sql::getNextRow($result);
		return Param::asInteger($row['value']);
	}

	public static function rotate(string $file): bool {
		// Create the full path
		$fullPath = sprintf('%s/%s', Constants::uploadsDir(), $file);

		// Create the file in memory , rotate it, and replace it
		if (preg_match('/\.gif$/i', $fullPath)) {
			$sourceImage = imagecreatefromgif($fullPath);
			$bgColor = imagecolorallocatealpha($sourceImage, 0, 0, 0, 127);
			$rotatedImage = imagerotate($sourceImage, 270, $bgColor);
			$rotationSuccess = imagegif($rotatedImage, $fullPath);
		} else if (preg_match('/\.png$/i', $fullPath)) {
			$sourceImage = imagecreatefrompng($fullPath);
			$bgColor = imagecolorallocatealpha($sourceImage, 0, 0, 0, 127);
			$rotatedImage = imagerotate($sourceImage, 270, $bgColor);
			$rotationSuccess = imagepng($rotatedImage, $fullPath);
		} else if (preg_match('/\.jpe?g$/i', $fullPath)) {
			$sourceImage = imagecreatefromjpeg($fullPath);
			$bgColor = imagecolorallocatealpha($sourceImage, 0, 0, 0, 127);
			$rotatedImage = imagerotate($sourceImage, 270, $bgColor);
			$rotationSuccess = imagejpeg($rotatedImage, $fullPath);
		} else {
			throw new RuntimeException("Unsupported file extension [" . $file . "].");
		}

		// Free the memory
		imagedestroy($sourceImage);
		imagedestroy($rotatedImage);
		if (!$rotationSuccess) return false;

		// Update the rotation index and return whether or not it succeeded
		return Uploads::updateRotationIndex($file);
	}

	public static function updateRotationIndex(string $file): bool {
		// Get the rotation index
		$result = Sql::executeSqlForResult("SELECT * FROM uploads WHERE file = ?", 's', $file);
		if (!Sql::hasRows($result, 1)) return false;
		$row = Sql::getNextRow($result);
		$rotationIndex = Param::asInteger($row['rotation']);

		// Rotate the rotation index 0->1->2->3->0
		if (++$rotationIndex > 3) $rotationIndex = 0;

		// Update the rotation index
		return Sql::executeSql("UPDATE uploads SET rotation = ? WHERE file = ?", 'is', $rotationIndex, $file);
	}

	public static function updateState(string $file, int $stateValue): bool {
		$query = "UPDATE uploads SET state = ? WHERE file = ?";
		return Sql::executeSql($query, 'is', $stateValue, $file);
	}
}
