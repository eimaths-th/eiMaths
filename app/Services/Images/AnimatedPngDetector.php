<?php

namespace App\Services\Images;

final class AnimatedPngDetector
{
    private const PNG_SIGNATURE = "\x89PNG\r\n\x1A\n";
    private const MAX_CHUNK_BYTES = 16777216;

    public function IsAnimated(string $path): bool
    {
        $handle = @fopen($path, 'rb');
        if ($handle === false) {
            return false;
        }

        try {
            if (fread($handle, 8) !== self::PNG_SIGNATURE) {
                return false;
            }

            while (!feof($handle)) {
                $length_bytes = fread($handle, 4);
                if ($length_bytes === '' || strlen($length_bytes) !== 4) {
                    return false;
                }

                $chunk_length = unpack('Nlength', $length_bytes)['length'];
                if ($chunk_length > self::MAX_CHUNK_BYTES) {
                    return false;
                }

                $chunk_type = fread($handle, 4);
                if ($chunk_type === false || strlen($chunk_type) !== 4) {
                    return false;
                }

                if ($chunk_type === 'acTL') {
                    return true;
                }

                if (fseek($handle, $chunk_length + 4, SEEK_CUR) !== 0) {
                    return false;
                }

                if ($chunk_type === 'IEND') {
                    return false;
                }
            }

            return false;
        } finally {
            fclose($handle);
        }
    }
}
