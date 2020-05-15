<?php

namespace dao;

use Constants as Constants;
use http\Exception\RuntimeException;
use util\General as General;
use util\Sql as Sql;

class Uploads {

	public static function add($teamIndex, $challengeIndex, $file) {
		$query = "INSERT INTO uploads (teamIndex, challengeIndex, file) VALUES (?, ?, ?)";
		return Sql::executeSql($query, 'iis', $teamIndex, $challengeIndex, $file);
	}

	public static function delete($file) {
		//TODO
		throw new RuntimeException("Uploads::delete() NOT YET IMPLEMENTED.");
	}

	public static function exists($file) {
		$fullPath = sprintf('%s/%s', Constants::uploadsDir(), $file);
		return file_exists($fullPath);
	}

	public static function get($file) {
		$query = "SELECT u.*, s.state, c.published FROM uploads u " .
				"JOIN uploadState s ON u.state = s.value " .
				"JOIN challenges c ON u.challengeIndex = c.challengeIndex " .
				"WHERE u.file = ?";
		$result = Sql::executeSqlForResult($query, 's', $file);
		if (!Sql::hasRows($result, 1)) return null;
		$row = Sql::getNextRow($result);
		return [
				'file'           => "" . $row['file'],
				'challengeIndex' => intval($row['challengeIndex']),
				'teamIndex'      => intval($row['teamIndex']),
				'state'          => "" . $row['state'],
				'rotation'       => intval($row['rotation']),
				'uploadTime'     => General::stringToDate($row['uploadTime']),
				'published'      => boolval($row['published'])
		];
	}

	public static function getAll($publishedOnly = true) {
		$query = "SELECT u.*, s.state, c.published FROM uploads u " .
				"JOIN uploadState s ON u.state = s.value " .
				"JOIN challenges c ON u.challengeIndex = c.challengeIndex" .
				($publishedOnly ? " WHERE u.state > 0 AND c.published = 1" : "");
		$result = Sql::executeSqlForResult($query);

		// Build the data array
		$uploads = [];
		while ($row = Sql::getNextRow($result)) {
			// Build and append the entry
			$uploads[] = [
					'file'           => "" . $row['file'],
					'challengeIndex' => intval($row['challengeIndex']),
					'teamIndex'      => intval($row['teamIndex']),
					'state'          => "" . $row['state'],
					'rotation'       => intval($row['rotation']),
					'uploadTime'     => General::stringToDate($row['uploadTime']),
					'published'      => boolval($row['published'])
			];
		}
		return $uploads;
	}

	public static function getStateValue($stateStr) {
		$result = Sql::executeSqlForResult("SELECT * FROM uploadState WHERE state = ?", 's', $stateStr);
		if (!Sql::hasRows($result, 1)) {
			return null;
		}
		$row = Sql::getNextRow($result);
		return intval($row['value']);
	}

	public static function rotate($file) {
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

	public static function updateRotationIndex($file) {
		// Get the rotation index
		$result = Sql::executeSqlForResult("SELECT * FROM uploads WHERE file = ?", 's', $file);
		if (!Sql::hasRows($result, 1)) return false;
		$row = Sql::getNextRow($result);
		$rotationIndex = intval($row['rotation']);

		// Rotate the rotation index 0->1->2->3->0
		if (++$rotationIndex > 3) $rotationIndex = 0;

		// Update the rotation index
		return Sql::executeSql("UPDATE uploads SET rotation = ? WHERE file = ?", 'is', $rotationIndex, $file);
	}

	public static function updateState($file, $stateValue) {
		$query = "UPDATE uploads SET state = ? WHERE file = ?";
		$info = Sql::executeSqlForInfo($query, 'is', $stateValue, $file);
		return $info['matched'] === 1;
	}
}
