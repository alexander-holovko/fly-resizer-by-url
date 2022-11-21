public static function flyImageSrcFromUrl($attachment_url = '', $size = '', $crop = null) {
    $fly_images = FlyImages\Core::get_instance();

    $attachment_url = str_replace(' ', '%20', $attachment_url);

    if ( empty($attachment_url) || empty( $size ) ) {
        return array();
    }

    // If size is 'full', we don't need a fly image
    if ( 'full' === $size ) {
        return $attachment_url;
    }

    // Get the attachment image
    $image = self::getImageInfo( $attachment_url );

    if ( false !== $image && $image ) {
        // Determine width and height based on size
        switch ( gettype( $size ) ) {
            case 'string':
                $image_size = $fly_images->get_image_size( $size );
                if ( empty( $image_size ) ) {
                    return [];
                }
                $width  = $image_size['size'][0];
                $height = $image_size['size'][1];
                $crop   = isset( $crop ) ? $crop : $image_size['crop'];
                break;
            case 'array':
                $width  = $size[0];
                $height = $size[1];
                break;
            default:
                return [];
        }

        $image_fly_url = str_replace('%20', '_', $image['url']);

        // Get file path
        $fly_dir       = $fly_images->get_fly_dir();
        $fly_file_path = $fly_dir . DIRECTORY_SEPARATOR . $fly_images->get_fly_file_name( basename( $image_fly_url ), $width, $height, $crop );

        // Check if file exsists
        if ( file_exists( $fly_file_path ) ) {
            $image_size = getimagesize( $fly_file_path );
            if ( ! empty( $image_size ) ) {
                return $fly_images->get_fly_path( $fly_file_path );
            } else {
                return [];
            }
        }

        // Check if images directory is writeable
        if ( ! $fly_images->fly_dir_writable() ) {
            return [];
        }

        // File does not exist, lets check if directory exists
        $fly_images->check_fly_dir();

        // Create new image
        if($image['ext'] == 'jpg' || $image['ext'] == 'jpeg') {
            imagejpeg(self::resizeImage($attachment_url, $width, $height, $crop, $image['ext']), $fly_file_path);
        }
        elseif($image['ext'] == 'png') {
            imagepng(self::resizeImage($attachment_url, $width, $height, $crop, $image['ext']), $fly_file_path);
        }

        $image_dimensions = self::getImageInfo($fly_file_path);
        return $fly_images->get_fly_path( $fly_file_path );
    }

    // Something went wrong
    return [];
}
