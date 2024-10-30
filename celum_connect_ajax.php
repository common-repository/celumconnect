<?php

if (!function_exists('media_handle_upload')) {
    require_once(ABSPATH . "wp-admin" . '/includes/image.php');
    require_once(ABSPATH . "wp-admin" . '/includes/file.php');
    require_once(ABSPATH . "wp-admin" . '/includes/media.php');
}

function celum_connect_assetsToMediaLibrary() {
    if (celum_connect_isRequestValid()) {
        $variables = celum_connect_getPostVariables();
        $result = array();
        $option = $variables['option'];
        $options = (array)get_option('celum_connect-settings');
        if($option == 'download') {
            $attachments = array();
            foreach ($variables['assets'] as $asset) {
                foreach ($asset['selectedDownload'] as $downloadFormat) {
                    $consumer = $options['usage_download'];
                    if (celum_connect_isAssetValid($asset)) {
                        $url = celum_connect_getDownloadUrl($asset, $downloadFormat, $consumer, $options);
                        $attachment = celum_connect_addAssetToMediaLibrary($url, $variables['post_id'], $asset, $downloadFormat);
                        $attachments[] = $attachment;
                    }
                }
                $result['result'] = $attachments;
            }
            if (isset($variables['set_featured_image'] )) {
                if ( is_wp_error( $attachments ) || is_wp_error( $variables ) ) {
                    celum_connect_setFeaturedImage($attachments[0]['attachment_id'], $attachments[0]['path'], $variables['post_id']);
                }
            }
        } else {
            $id_descriptionMap = array();
            $dlf_publicUrl_array = explode(',', $options['dlf_publicUrl_mapping']);
            foreach ($dlf_publicUrl_array as $dlf_publicUrl) {
                $dlf_publicUrl_string_array = explode(':', $dlf_publicUrl);
                $id_descriptionMap[$dlf_publicUrl_string_array[0]] = $dlf_publicUrl_string_array[1];
            }
            foreach ($variables['assets'] as $asstKey => $asset) {
                $publicUrlMap = celum_connect_getPublicUrlMap($asset['id'], $options);
                $result['result'][$asstKey]['downloadFormats'] = $asset['selectedDownload'];
                $result['result'][$asstKey]['asset'] = $asset;
                $result['result'][$asstKey]['urls'] = array();
                foreach ($asset['selectedDownload'] as $downloadFormat) {
                    $consumer = $options['usage_download'];
                    if (celum_connect_isAssetValid($asset)) {
                        $url = celum_connect_getDownloadUrl($asset, $downloadFormat, $consumer, $options);
                        $result['result'][$asstKey]['urls'][$downloadFormat]['url'] =  $url;
                    }
                    $result['result'][$asstKey]['urls'][$downloadFormat]['publicUrl'] = $publicUrlMap[$id_descriptionMap[$downloadFormat]];
                    $result['result'][$asstKey]['urls'][$downloadFormat]['publicUrl_description'] = explode(';', $id_descriptionMap[$downloadFormat])[1];
                }
            }
        }
        $result['option'] = $option;
        echo json_encode($result);
        exit;
    }else {
        echo json_encode(array("error" => __("Invalid request.", 'celum_asset_picker')));
        exit;
    }
}

function celum_connect_isRequestValid() {
    if (isset($_POST["assets"]) && !empty($_POST["assets"]) &&
        isset($_POST["post_id"]) && !empty($_POST["post_id"])) {
        if (empty($_POST["post_id"])) {
            echo json_encode(array("error" => __("No post found.", 'celum_asset_picker')));
            exit;
        } else if (empty($_POST["assets"])) {
            echo json_encode(array("error" => __("No assets found.", 'celum_asset_picker')));
            exit;
        } else {
            return true;
        }
    } else {
        return false;
    }
}

function celum_connect_getPostVariables() {
    $variables = array();
    $variables['post_id'] = sanitize_text_field($_POST["post_id"]);
    $variables['assets'] = $_POST["assets"];
    if (array_key_exists('set_featured_image', $_POST)) {
        $variables['set_featured_image'] = sanitize_text_field($_POST["set_featured_image"]);
    }else {
        $variables['set_featured_image'] = false;
    }
    $variables['option'] = sanitize_text_field($_POST["option"]);
    return $variables;
}

function celum_connect_isAssetValid($asset) {
    if (!is_array($asset)) {
        echo json_encode(array("error" => "Unexpected object in asset list: " . var_export($asset, true)));
        exit;
    }else {
        return true;
    }
}

function celum_connect_getDownloadUrl($asset, $downloadFormat, $consumer, $options) {
    if (strpos(celum_connect_decrypt($options['license_key']), '_') !== false) {
        list($url) = explode("_", celum_connect_decrypt($options['license_key']));
    } else {
        $url = celum_connect_decrypt($options['license_key']);
    }
    $consumerParameter = '';
    if($consumer) {
        $consumerParameter = '&consumer=' . $consumer;
    }
    $url .= '/direct/download?id=' . $asset['id'] . '&format=' . $downloadFormat . $consumerParameter;
    if ($options['directdownload_secret'] != '') {
        $url .= '&token=' . hash('sha256', $asset['id'] . $options['directdownload_secret']);
    }
    return $url;
}

function celum_connect_getPublicUrlMap($assetId, $options) {
    $request = array(
        'headers' => array(
            'Authorization' => 'celumApiKey '.$options['api_key'],
        ),
        'method'  =>  'GET',
    );
    if(strpos(celum_connect_decrypt($options['license_key']), '_') !== false){
        list($publicURLRequest, $t) = explode("_", celum_connect_decrypt($options['license_key']));
    }else{
        $publicURLRequest =  celum_connect_decrypt($options['license_key']);
    }
    $publicURLRequest .= '/cora/Assets('.$assetId.')?$select=id&$expand=publicUrls';
    $publicUrlsResponse = wp_remote_request( $publicURLRequest, $request );
    $publicUrls = json_decode($publicUrlsResponse['body'])->publicUrls;
    $publicUrlDescription_publicUrl_map = array();

    foreach( $publicUrls as $i){
        $publicUrlDescription_publicUrl_map[$i->provider.';'.$i->description] = $i->url;
    }

    return $publicUrlDescription_publicUrl_map;
}

function celum_connect_addAssetToMediaLibrary($url, $post_id, $asset, $downloadformat) {
    $assetId = $asset['id'];
    $assetName = $asset['name'];
    add_filter('upload_dir', function ($dir) use ($assetId) {
        return array(
                'path' => $dir['basedir'] . '/celum/' . $assetId,
                'url' => $dir['baseurl'] . '/celum/' . $assetId,
                'subdir' => '/celum/' . $assetId,
            ) + $dir;
    });
    $tmpfile = celum_connect_download_url_with_content_disposition($url . '&.jpg');
    if (is_wp_error($tmpfile)) {
        echo json_encode(array("error" => $tmpfile->get_error_messages()));
        exit;
    }
    $tmp = $tmpfile['url'];
    $mime_type = mime_content_type($tmp);
    $filename = celum_connect_get_filename_from_disposition($tmpfile);
    if (empty($filename)) {
        return new WP_Error( 'rest_upload_invalid_disposition', __( 'Invalid Content-Disposition supplied. Content-Disposition needs to be formatted as "filename=image.png" or similar. url: '.$url . '\n'. file_get_contents($url)), array( 'status' => 400 ) );
    }
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $file_array = array(
        'name' => $assetId . '_' . $asset['version'] . '_' . $downloadformat . '.' . $ext,
        'tmp_name' => $tmp
    );
    if (is_wp_error($tmp)) {
        @unlink($file_array['tmp_name']);
        echo json_encode(array("error" => $tmp->get_error_messages()));
        exit;
    }
    $upload_dir = wp_upload_dir();
    $path = $upload_dir['path'] . '/' . $file_array['name'];
    if (!file_exists($path)) {
        $attachment_id = media_handle_sideload($file_array, $post_id, $assetName);
        if (is_wp_error($attachment_id)) {
            @unlink($file_array['tmp_name']);
            echo json_encode(array("error" => $attachment_id->get_error_messages()));
            exit;
        }
        $url = wp_get_attachment_url($attachment_id);
        return array('attachment_id' => $attachment_id, 'path' => $path, 'url' => ($url), 'type' => celum_connect_getTypeFromMime($mime_type), 'name' => $assetName);
    } else {
        $attachment_id = find_post_id_from_path($path);
        return array(
            'downloadUrl' => $url,
            'attachmentUrl' => getAttachmentUrl($path),
            'path' => $path,
            'url' => ($upload_dir['url'] . '/' . $file_array['name']),
            'attachment_id' => $attachment_id,
            'type' => celum_connect_getTypeFromMime($mime_type), 'name' => $assetName);
    }
}

function getAttachmentUrl( $path ) {
    if ( preg_match( '/(-\d{1,4}x\d{1,4})\.(jpg|jpeg|png|gif)$/i', $path, $matches ) ) {
        $path = str_ireplace( $matches[1], '', $path );
    }
    if ( preg_match( '/uploads\/(\d{1,4}\/)?(\d{1,2}\/)?(.+)$/i', $path, $matches ) ) {
        unset( $matches[0] );
        $path = implode( '', $matches );
    }
    return $path ;
}

function find_post_id_from_path( $path ) {
    if ( preg_match( '/(-\d{1,4}x\d{1,4})\.(jpg|jpeg|png|gif)$/i', $path, $matches ) ) {
        $path = str_ireplace( $matches[1], '', $path );
    }
    if ( preg_match( '/uploads\/(\d{1,4}\/)?(\d{1,2}\/)?(.+)$/i', $path, $matches ) ) {
        unset( $matches[0] );
        $path = implode( '', $matches );
    }
    return attachment_url_to_postid( $path );
}

function celum_connect_setFeaturedImage ($attachmentId, $path, $postId) {
    $attachment_data = wp_generate_attachment_metadata($attachmentId, $path);
    wp_update_attachment_metadata($attachmentId, $attachment_data);
    set_post_thumbnail($postId, $attachmentId);
    $thumbnail_url = get_the_post_thumbnail_url($postId, 'thumbnail');
    return array('thumbnail_url' => $thumbnail_url, 'attachment_data' => $attachment_data, 'attachment_id' => $attachmentId);
}


function celum_connect_media_save_asset($url, $post_id, $assetId, $version, $downloadformat, $assetName, $picker_type) {
	if (!function_exists('media_handle_upload')) {
		require_once(ABSPATH . "wp-admin" . '/includes/image.php');
		require_once(ABSPATH . "wp-admin" . '/includes/file.php');
		require_once(ABSPATH . "wp-admin" . '/includes/media.php');
	}
	add_filter('upload_dir', function ($dir) use ($assetId) {
		return array(
			       'path' => $dir['basedir'] . '/celum/' . $assetId,
			       'url' => $dir['baseurl'] . '/celum/' . $assetId,
			       'subdir' => '/celum/' . $assetId,
		       ) + $dir;
	});
	$tmpfile = celum_connect_download_url_with_content_disposition($url . '&.jpg');
	if (is_wp_error($tmpfile)) {
		echo json_encode(array("error" => $tmpfile->get_error_messages()));
		exit;
	}
	$tmp = $tmpfile['url'];
	$mime_type = mime_content_type($tmp);
	$filename = celum_connect_get_filename_from_disposition($tmpfile);
	if ( empty( $filename ) ) {
		return new WP_Error( 'rest_upload_invalid_disposition', __( 'Invalid Content-Disposition supplied. Content-Disposition needs to be formatted as "filename=image.png" or similar. url: '.$url . '\n'. file_get_contents($url)), array( 'status' => 400 ) );
	}
	$ext = pathinfo($filename, PATHINFO_EXTENSION);
	if ($picker_type == 'download') {
		$file_array = array(
			'name' => $assetId . '_' . $version . '_' . $downloadformat . '.' . $ext,
			'tmp_name' => $tmp
		);
		if (is_wp_error($tmp)) {
			@unlink($file_array['tmp_name']);
			echo json_encode(array("error" => $tmp->get_error_messages()));
			exit;
		}
		$upload_dir = wp_upload_dir();
		if (!file_exists($upload_dir['path'] . '/' . $file_array['name'])) {
			$id = media_handle_sideload($file_array, $post_id, $assetName);
			if (is_wp_error($id)) {
				@unlink($file_array['tmp_name']);
				echo json_encode(array("error" => $id->get_error_messages()));
				exit;
			}
			$value = wp_get_attachment_url($id);
			return array('url' => ($value), 'type' => celum_connect_getTypeFromMime($mime_type), 'name' => $assetName);
		} else {
			return array('url' => ($upload_dir['url'] . '/' . $file_array['name']), 'type' => celum_connect_getTypeFromMime($mime_type), 'name' => $assetName);
		}
	} else {
		return array('url' => $url, 'type'=> celum_connect_getTypeFromMime($mime_type), 'name' => $assetName);
	}
}

function celum_connect_block_save_asset($url, $post_id, $assetId, $version, $downloadformat, $assetName, $picker_type, $options) {
	if (!function_exists('media_handle_upload')) {
		require_once(ABSPATH . "wp-admin" . '/includes/image.php');
		require_once(ABSPATH . "wp-admin" . '/includes/file.php');
		require_once(ABSPATH . "wp-admin" . '/includes/media.php');
	}
	add_filter('upload_dir', function ($dir) use ($assetId) {
		return array(
			       'path' => $dir['basedir'] . '/celum/' . $assetId,
			       'url' => $dir['baseurl'] . '/celum/' . $assetId,
			       'subdir' => '/celum/' . $assetId,
		       ) + $dir;
	});
	$tmpfile = celum_connect_download_url_with_content_disposition($url . '&.jpg');
	if (is_wp_error($tmpfile)) {
		echo json_encode(array("error" => $tmpfile->get_error_messages(), "url", $url));
		exit;
	}
	$tmp = $tmpfile['url'];
	$mime_type = mime_content_type($tmp);
	$filename = celum_connect_get_filename_from_disposition($tmpfile);
	if ( empty( $filename ) ) {
        return new WP_Error( 'rest_upload_invalid_disposition', __( 'Invalid Content-Disposition supplied. Content-Disposition needs to be formatted as "filename=image.png" or similar. url: '.$url . '\n'. file_get_contents($url)), array( 'status' => 400 ) );
	}
	$ext = pathinfo($filename, PATHINFO_EXTENSION);
	if ($picker_type == 'download') {
		$file_array = array(
			'name' => $assetId . '_' . $version . '_' . $downloadformat . '.' . $ext,
			'tmp_name' => $tmp
		);
		if (is_wp_error($tmp)) {
			@unlink($file_array['tmp_name']);
			echo json_encode(array("error" => $tmp->get_error_messages()));
			exit;
		}
		$upload_dir = wp_upload_dir();
		if (!file_exists($upload_dir['path'] . '/' . $file_array['name'])) {
			$id = media_handle_sideload($file_array, $post_id, $assetName);
			if (is_wp_error($id)) {
				@unlink($file_array['tmp_name']);
				echo json_encode(array("error" => $id->get_error_messages()));
				exit;
			}
			$value = wp_get_attachment_url($id);
			return array('url' => ($value), 'type' => celum_connect_getTypeFromMime($mime_type), 'name' => $assetName);
		} else {
			return array('url' => ($upload_dir['url'] . '/' . $file_array['name']), 'type' => celum_connect_getTypeFromMime($mime_type), 'name' => $assetName);
		}
	} else {
		/*$request = array(
			'headers' => array(
				'Authorization' => 'celumApiKey '.$options['api_key'],
			),
			'method'  =>  'GET',
		);
		if(strpos(celum_connect_decrypt($options['license_key']), '_') !== false){
			list($publicURLRequest, $t) = explode("_", celum_connect_decrypt($options['license_key']));
		}else{
			$publicURLRequest =  celum_connect_decrypt($options['license_key']);
		}
		$publicURLRequest .= '/cora/Assets('.$assetId.')?$select=id&$expand=publicUrls';
		$publicUrlsResponse = wp_remote_request( $publicURLRequest, $request );
		$publicUrls = json_decode($publicUrlsResponse['body'])->publicUrls;
		$type = celum_connect_getTypeFromMime($mime_type);
		$stageHandler = $type.'_stagehandler';
		$description = $type.'_description';

        if(isset($options[$stageHandler]) && !empty($options[$stageHandler])  && !empty($options[$description]) && isset($options[$description])){
            foreach( $publicUrls as $i){
                if($i->provider == $options[$stageHandler]&& $i->description == $options[$description]){
                    $url = $i->url;
                }
            }
        }*/

        $publicUrlMap = celum_connect_getPublicUrlMap($assetId, $options);
        $id_descriptionMap = array();
        $dlf_publicUrl_array = explode(',', $options['dlf_publicUrl_mapping']);
        foreach ($dlf_publicUrl_array as $dlf_publicUrl) {
            $dlf_publicUrl_string_array = explode(':', $dlf_publicUrl);
            $id_descriptionMap[$dlf_publicUrl_string_array[0]] = $dlf_publicUrl_string_array[1];
        }

        if($id_descriptionMap[$downloadFormat] != null && $publicUrlMap[$id_descriptionMap[$downloadFormat]] != null) {
            $url = $publicUrlMap[$id_descriptionMap[$downloadFormat]];
        };

		return array('url' => $url, 'type'=> $type, 'name' => $assetName);
	}
}

function celum_connect_media_save_assets() {
	if(isset($_POST["assets"]) && !empty($_POST["assets"]) &&
	   isset($_POST["post_id"]) && !empty($_POST["post_id"]) &&
	   isset($_POST["picker_type"]) && !empty($_POST["picker_type"]) &&
	   isset($_POST["wpnonce"]) && !empty($_POST["wpnonce"]) && wp_verify_nonce($_POST["wpnonce"], 'asset_picker_security_nonce')){
		$assets = celum_connect_sanitizeAssetsArray($_POST["assets"]);
		$post_id = sanitize_text_field($_POST["post_id"]);
		$picker_type = sanitize_text_field($_POST["picker_type"]);
		$options = (array)get_option('celum_connect-settings');
		if (empty($post_id)) {
			echo json_encode(array("error" => __("No post found. This feature requires that an auto-save or draft of the current post was saved first.", 'celum_asset_picker')));
			exit;
		}
		$newassets = array();
		$result = array();
		foreach ($assets as $asset) {
			foreach ($asset['selectedDownload'] as $downloadFormat) {
				if($picker_type == 'link'){
					$consumer = $options['usage_link'];
				}else{
					$consumer = $options['usage_download'];
				}
				if (!is_array($asset)) {
					echo json_encode(array("error" => "Unexpected object in asset list: ".var_export($asset, true)));
					exit;
				}
				if(strpos(celum_connect_decrypt($options['license_key']), '_') !== false){
					list($url, $t) = explode("_", celum_connect_decrypt($options['license_key']));
				}else{
					$url =  celum_connect_decrypt($options['license_key']);
				}
                $consumerParameter = '';
                if($consumer) {
                    $consumerParameter = '&consumer=' . $consumer;
                }
				$url .= '/direct/download?id=' . $asset['id'] . '&format=' . $downloadFormat . $consumerParameter;
				if($options['directdownload_secret'] != ''){
					$url .= '&token=' . hash('sha256', $asset['id'] . $options['directdownload_secret']);
				}
				$newsrc = celum_connect_media_save_asset($url, $post_id, $asset['id'], $asset['version'], $downloadFormat, $asset['name'], $picker_type);
				if (is_wp_error($newsrc)) {
					echo json_encode(array("error" => $newsrc->get_error_message(), "url" => $url));
					exit;
				} else if (is_array($newsrc) && array_key_exists('error', $newsrc)) {
					echo json_encode(array("error" => $newsrc["error"], "url" => $url));
					exit;
				} else {
					$newassets[] = $newsrc;
				}
			}
			$result['result'] = $newassets;
			echo json_encode($result);
			exit;
		}
	}else{
		echo json_encode(array("error" => __("Invalid request.", 'celum_asset_picker')));
		exit;
	}
}

function celum_connect_block_save_assets() {
	if(isset($_POST["assets"]) && !empty($_POST["assets"]) &&
	   isset($_POST["post_id"]) && !empty($_POST["post_id"])){
		$assets = celum_connect_sanitizeAssetsArray($_POST["assets"]);
		$post_id = sanitize_text_field($_POST["post_id"]);
		$picker_type = $_POST["isDownload"] == 'true' ? 'download' : 'linking';
		$options = (array)get_option('celum_connect-settings');
		if (empty($post_id)) {
			echo json_encode(array("error" => __("No post found. This feature requires that an auto-save or draft of the current post was saved first.", 'celum_asset_picker')));
			exit;
		}
		$result = array();
		$result['result'] = array();
		foreach ($assets as &$asset) {
			foreach ($asset['selectedDownload'] as $downloadFormat) {
				if($picker_type == 'link'){
					$consumer = $options['usage_link'];
				}else{
					$consumer = $options['usage_download'];
				}
				if (!is_array($asset)) {
					echo json_encode(array("error" => "Unexpected object in asset list: ".var_export($asset, true)));
					exit;
				}
				if(strpos(celum_connect_decrypt($options['license_key']), '_') !== false){
					list($url, $t) = explode("_", celum_connect_decrypt($options['license_key']));
				}else{
					$url =  celum_connect_decrypt($options['license_key']);
				}
                $consumerParameter = '';
                if($consumer) {
                    $consumerParameter = '&consumer=' . $consumer;
                }
				$url .= '/direct/download?id=' . $asset['id'] . '&format=' . $downloadFormat . $consumerParameter;
				if($options['directdownload_secret'] != ''){
					$url .= '&token=' . hash('sha256', $asset['id'] . $options['directdownload_secret']);
				}
				$newsrc = celum_connect_block_save_asset($url, $post_id, $asset['id'], $asset['version'], $downloadFormat, $asset['name'], $picker_type, $options);
				if (is_wp_error($newsrc)) {
					echo json_encode(array("error" => $newsrc->get_error_message(), "url" => $url));
					exit;
				} else if (is_array($newsrc) && array_key_exists('error', $newsrc)) {
					echo json_encode(array("error" => $newsrc["error"], "url" => $url));
					exit;
				} else {
					$request = array(
						'headers' => array(
							'Authorization' => 'celumApiKey '.$options['api_key'],
						),
						'method'  =>  'GET',
					);
					if(!empty($options['description_infofield'])){
						if(strpos(celum_connect_decrypt($options['license_key']), '_') !== false){
							list($url, $t) = explode("_", celum_connect_decrypt($options['license_key']));
						}else{
							$url =  celum_connect_decrypt($options['license_key']);
						}
						$url  .= '/cora/Assets('.$asset['id'].')?$select=informationFieldValues/'.camelCase($options['description_infofield']);
						$description = wp_remote_request( $url, $request );
						$newsrc['description'] = json_decode($description['body'])->informationFieldValues->{camelCase($options['description_infofield'])};
					}
					array_push($result['result'], $newsrc);
				}

			}
		}
		echo json_encode($result);
		exit;
	}else{
		echo json_encode(array("error" => __("Invalid request.", 'celum_asset_picker')));
		exit;
	}
}

function celum_connect_sanitizeAssetsArray( $input ) {
	$new_input = array();
	foreach($input as $j => $item) {
		if(is_array($input[$j])){
			$new_input[$j] = celum_connect_sanitizeAssetsArray($input[$j]);
		}else{
			switch ( $j ) {
				case 'id':
					$new_input[$j] = sanitize_text_field( $input[$j] );
					break;
				case 'selectedDownload':
					$new_input[$j] = celum_connect_sanitizeAssetsArray($input[$j]);
					break;
				case 'fileCategory':
					$new_input[$j] = sanitize_text_field( $input[$j] );
					break;
				case 'version':
					$new_input[$j] = sanitize_text_field( $input[$j] );
					break;
				case 'name':
					$new_input[$j] = sanitize_text_field( $input[$j] );
					break;
				case 'fileExtension':
					$new_input[$j] = sanitize_text_field( $input[$j] );
					break;
			}
		}

	}
	return $new_input;
}

function celum_connect_download_url_with_content_disposition($url, $timeout = 300) {
	if (!$url)
		return new WP_Error('http_no_url', __('Invalid URL provided.'));

	$url_filename = basename(parse_url($url, PHP_URL_PATH));
	$tmpfname = wp_tempnam($url_filename);
	if (!$tmpfname)
		return new WP_Error('http_no_file', __('Could not create temporary file: '.$tmpfname));
	$response = wp_safe_remote_get($url, array('timeout' => $timeout, 'stream' => true, 'filename' => $tmpfname));
	if (is_wp_error($response)) {
		unlink($tmpfname);
		return $response;
	}
	if (200 != wp_remote_retrieve_response_code($response)) {
		unlink($tmpfname);
		return new WP_Error('http_404', trim(wp_remote_retrieve_response_message($response)));
	}
	$content_md5 = wp_remote_retrieve_header($response, 'content-md5');
	if ($content_md5) {
		$md5_check = verify_file_md5($tmpfname, $content_md5);
		if (is_wp_error($md5_check)) {
			unlink($tmpfname);
			return $md5_check;
		}
	}
	$tmpfile['url'] = $tmpfname;
	$tmpfile['content-disposition'] = wp_remote_retrieve_header($response, 'Content-Disposition');
	$tmpfile['location'] = wp_remote_retrieve_header($response, 'url');
	return $tmpfile;
}

function celum_connect_getTypeFromMime($mimeType) {
	if (substr($mimeType, 0, 5) === "image") return "image";
	else if (substr($mimeType, 0, 5) === "video") return "video";
	else if (substr($mimeType, 0, 5) === "audio") return "audio";
	switch ($mimeType) {
		case "application/msword":
		case "application/vnd.openxmlformats-officedocument.wordprocessingml.document":
		case "application/vnd.oasis.opendocument.presentation":
		case "application/vnd.oasis.opendocument.spreadsheet":
		case "application/vnd.oasis.opendocument.text":
		case "application/vnd.openxmlformats-officedocument.presentationml.presentation":
		case "application/pdf":
		case "application/rtf":
			return "document";
		default:
			return "unknown";
	}
}

function celum_connect_get_filename_from_disposition( $disposition_header ) {
	// Get the filename
	$filename = null;
	foreach ( $disposition_header as $value ) {
		$value = trim( $value );
		if ( strpos( $value, 'filename' ) === false ) {
			continue;
		}
		list( $type, $attr_parts ) = explode( ';', $value, 2 );
		$attr_parts = explode( ';', $attr_parts );
		$attributes = array();
		foreach ( $attr_parts as $part ) {
			if ( strpos( $part, '=' ) === false ) {
				continue;
			}
			list( $key, $value ) = explode( '=', $part, 2 );
			$attributes[ trim( $key ) ] = trim( $value );
		}
		if ( empty( $attributes['filename'] ) ) {
			continue;
		}
		$filename = trim( $attributes['filename'] );
		// Unquote quoted filename, but after trimming.
		if ( substr( $filename, 0, 1 ) === '"' && substr( $filename, -1, 1 ) === '"' ) {
			$filename = substr( $filename, 1, -1 );
		}
	}
	return $filename;
}

function camelCase($str, array $noStrip = [])
{
	// non-alpha and non-numeric characters become spaces
	$str = preg_replace('/[^a-z0-9' . implode("", $noStrip) . ']+/i', ' ', $str);
	$str = trim($str);
	// uppercase the first character of each word
	$str = ucwords($str);
	$str = str_replace(" ", "", $str);
	$str = lcfirst($str);

	return $str;
}
