<?php

class JPEGMarkersHelper {

	public function isUniMarker($byte) {
		return ($byte >= 0xd0 && $byte <= 0xd9) || $byte == 0x01;
	}

	public function isDataMarker($byte) {
		return ($byte >= 0xda && $byte <= 0xfe) || ($byte >= 0x02 && $byte <= 0xcf);
	}

	public function extractMarkers($contents) {
		$i = 0;
		$bufferSize = strlen($contents);
		$currentByte = null;
		$lastMarker = null;
		$markers = array();

		while ($i < $bufferSize) {
			$currentByte = ord($contents[$i]);
			$i++;

			if ($currentByte == 0xff) {
				//check if this is the first byte of a marker

				while($currentByte == 0xff && $i < $bufferSize) {
					$currentByte = ord($contents[$i]);
					$i++;
				}

				if ($lastMarker == 0xD9) {
					if ($currentByte != 0xD8) {
						continue;
					}
				}

				if ($this->isUniMarker($currentByte)) {
					//we've found an uni-marker, and we really don't care
					$lastMarker = $currentByte;
					$markers[] = array('position' => $i, 'marker' => $currentByte, 'size' => 0);
					continue;
				}

				if ($this->isDataMarker($currentByte)) {
					if ($bufferSize <= $i + 2) {
						//there are no enough bytes left to read the size of this IDF
						//this should never happen, just ignore if it does
						continue;
					}
				}

				$sizeByte1 = ord($contents[$i]);
				$i++;
				$sizeByte2 = ord($contents[$i]);
				$i++;

				$size = ($sizeByte1 << 8) + $sizeByte2 - 2;

				//note: at this point i points to the start of the data block
				if ($bufferSize <= $size + i) {
					//there are no enough bytes, ignore this corrupted tag and stop processing
					break;
				}

				$markers[] = array('position' => $i, 'marker' => $currentByte, 'size' => $size);


				$i += $size;
				$lastMarker = $currentByte;
			}

		}
		return $markers;
	}

	public function extractFirstMarker($contents, $markerID, $markerString) {
		$markerStringSize = strlen($markerString);

		$i = 0;
		$bufferSize = strlen($contents);
		$currentByte = null;
		$lastMarker = null;

		while ($i < $bufferSize) {
			$currentByte = ord($contents[$i]);
			$i++;

			if ($currentByte == 0xff) {
				//check if this is the first byte of a marker
				while($currentByte == 0xff && $i < $bufferSize) {
					$currentByte = ord($contents[$i]);
					$i++;
				}

				if ($lastMarker == 0xD9) {
					if ($currentByte != 0xD8) {
						continue;
					}
				}

				if ($this->isUniMarker($currentByte)) {
					//we've found an uni-marker, and we really don't care
					$lastMarker = $currentByte;
					continue;
				}

				if ($this->isDataMarker($currentByte)) {
					if ($bufferSize <= $i + 2) {
						//there are no enough bytes left to read the size of this IDF
						//this should never happen, just ignore if it does
						continue;
					}
				}

				$sizeByte1 = ord($contents[$i]);
				$i++;
				$sizeByte2 = ord($contents[$i]);
				$i++;

				$size = ($sizeByte1 << 8) + $sizeByte2 - 2;

				//note: at this point i points to the start of the data block
				if ($bufferSize <= $size + i) {
					//there are no enough bytes, ignore this corrupted tag and stop processing
					break;
				}

				if ($currentByte == $markerID && substr($contents, $i, $markerStringSize) == $markerString) {
					return array(
						'position' => $i,
						'marker' => $currentByte,
						'size' => $size,
						'contents' => substr($contents, $i + $markerStringSize, $size - $markerStringSize),
					);
				}

				$i += $size;
				$lastMarker = $currentByte;
			}

		}
		return null;
	}
}