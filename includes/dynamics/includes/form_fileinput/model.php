<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Native, framework-neutral file input component.
+--------------------------------------------------------*/

defined('IN_FUSION') || exit;

/**
 * Render a progressively enhanced file upload input.
 *
 * The input always remains a native multipart form control. JavaScript adds
 * previews and calls the core preflight endpoint, but Defender remains the
 * authority that stores and validates files when the PHP form is submitted.
 */
function form_fileinput($input_name, $label = '', $input_value = FALSE, array $options = []) {
    $locale = fusion_get_locale();
    $input_name = trim((string)$input_name);
    $base_name = trim(clean_input_name($input_name));
    $title = $label !== '' ? stripinput((string)$label) : ucfirst(str_replace('_', ' ', $base_name));
    $input_value = clean_input_value($input_value);
    $valid_ext_supplied = array_key_exists('valid_ext', $options);

    $defaults = [
        'input_id'           => $base_name,
        'upload_path'        => IMAGES,
        'required'           => FALSE,
        'safemode'           => FALSE,
        'deactivate'         => FALSE,
        'preview_off'        => FALSE,
        'show_thumbnails'    => TRUE,
        'type'               => 'image',
        'width'              => '',
        'label'              => $locale['browse'] ?? 'Browse',
        'inline'             => FALSE,
        'class'              => '',
        'tip'                => '',
        'ext_tip'            => '',
        'error_text'         => $locale['error_input_file'] ?? 'The selected file could not be uploaded.',
        'button_class'       => '',
        'button_remove_class'=> 'btn-outline-danger',
        'icon'               => '',
        'jsonurl'            => FALSE,
        'dropzone'           => TRUE,
        'valid_ext'          => '.jpg,.jpeg,.png,.gif,.bmp,.webp,.avif',
        'thumbnail'          => FALSE,
        'thumbnail_w'        => 300,
        'thumbnail_h'        => 300,
        'thumbnail_folder'   => '',
        'thumbnail_ratio'    => 0,
        'thumbnail_suffix'   => '_t1',
        'thumbnail2'         => FALSE,
        'thumbnail2_w'       => 600,
        'thumbnail2_h'       => 400,
        'thumbnail2_suffix'  => '_t2',
        'thumbnail2_ratio'   => 0,
        'delete_original'    => FALSE,
        'max_width'          => 1800,
        'max_height'         => 1600,
        'max_byte'           => 15728640,
        'max_count'          => 1,
        'multiple'           => FALSE,
        'template'           => 'classic',
        'media'              => FALSE,
        'placeholder'        => '',
        'form_id'            => '',
        'hide_upload'        => TRUE,
        'hide_remove'        => FALSE,
        'krajee_disabled'    => FALSE,
        'enhance'            => TRUE,
        'replace_upload'     => FALSE,
        'browse_label'       => '',
        'drop_title'         => 'Click to upload or drag and drop',
        'remote_check'       => TRUE,
        'remote_check_url'   => (defined('BASEDIR') ? BASEDIR : '').'api/index.php?api=file-upload-check',
        'remote_required'    => TRUE,
    ];
    $options += $defaults;

    $options['enhance'] = (bool)$options['enhance'] && !$options['krajee_disabled'];
    $options['multiple'] = (bool)$options['multiple'];
    $options['max_count'] = $options['multiple'] ? max(1, (int)$options['max_count']) : 1;
    $options['max_byte'] = max(1, (int)$options['max_byte']);
    if (function_exists('max_server_upload')) {
        $server_upload_limit = (int)max_server_upload();
        if ($server_upload_limit > 0) {
            $options['max_byte'] = min($options['max_byte'], $server_upload_limit);
        }
    }

    $types = array_values(array_filter(array_map(
        static fn($type): string => strtolower(trim((string)$type)),
        is_array($options['type']) ? $options['type'] : preg_split('/\s*,\s*/', (string)$options['type'])
    )));
    if ($types === []) {
        $types = ['file'];
    }
    if (!$valid_ext_supplied) {
        $options['valid_ext'] = fusion_fileinput_extensions_for_types($types);
    }
    $extensions = fusion_fileinput_normalize_extensions($options['valid_ext']);
    $options['valid_ext'] = implode(',', array_map(static fn(string $ext): string => '.'.$ext, $extensions));

    $input_id = preg_replace('/[^A-Za-z0-9_\-:.]/', '-', trim((string)$options['input_id'])) ?: $base_name;
    $input_id = trim($input_id, '-');
    $field_id = $input_id.'-field';
    $error_id = $input_id.'-error';
    $description_id = $input_id.'-description';
    $is_error = \Defender::inputHasError($base_name);
    if ($is_error) {
        $defender_error = \Defender::getErrorText($base_name);
        if ($defender_error !== '') {
            $options['error_text'] = $defender_error;
        }
    }

    $description = trim((string)$options['ext_tip']);
    if ($description === '') {
        $description = fusion_fileinput_acceptance_text($extensions, $options['max_byte'], $options['max_count']);
    }
    $browse_label = trim((string)($options['browse_label'] ?: $options['placeholder'] ?: $options['label']));
    $compact = $options['template'] === 'button' || !$options['dropzone'];
    $input_html_name = $options['multiple'] && !str_contains($input_name, '[') ? $input_name.'[]' : $input_name;
    $initial = fusion_fileinput_initial_files($input_value, (string)$options['upload_path']);

    $config = [
        'id'               => $input_id,
        'multiple'         => $options['multiple'],
        'required'         => (bool)$options['required'],
        'maxCount'         => $options['max_count'],
        'maxBytes'         => $options['max_byte'],
        'maxWidth'         => (int)$options['max_width'],
        'maxHeight'        => (int)$options['max_height'],
        'types'            => $types,
        'extensions'       => $extensions,
        'showThumbnails'   => (bool)$options['show_thumbnails'] && !$options['preview_off'],
        'allowRemove'      => !$options['hide_remove'],
        'remoteCheck'      => (bool)$options['remote_check'],
        'remoteRequired'   => (bool)$options['remote_required'],
        'remoteCheckUrl'   => (string)$options['remote_check_url'],
        'uploadUrl'        => $options['jsonurl'] ? (string)$options['jsonurl'] : '',
        'initialFiles'     => $initial,
        'messages'         => [
            'checking'       => 'Checking file safety…',
            'network'        => 'The file safety check is unavailable. Please try again.',
            'required'       => 'Choose a file to upload.',
            'tooMany'        => 'You can upload up to {count} files.',
            'tooLarge'       => '“{name}” is larger than {size}.',
            'invalidType'    => '“{name}” is not an accepted file type.',
            'unreadable'     => '“{name}” could not be read.',
            'pendingSubmit'  => 'Please wait while the selected files are checked.',
        ],
    ];

    $classes = trim('form-group fusion-fileinput-field '.($options['inline'] && $label ? 'row ' : '').($is_error ? 'has-error ' : '').$options['class']);
    $described_by = trim($description_id.' '.$error_id);
    $html = '<div id="'.fusion_fileinput_escape($field_id).'" class="'.fusion_fileinput_escape($classes).'"'.($options['width'] ? ' style="width:'.fusion_fileinput_escape((string)$options['width']).'"' : '').'>';
    if ($label !== '') {
        $html .= '<label class="form-label'.($options['inline'] ? ' col-xs-12 col-sm-3 col-md-3 col-lg-3' : '').'" for="'.fusion_fileinput_escape($input_id).'">'.fusion_fileinput_escape((string)$label);
        if ($options['required']) {
            $html .= '<span class="required" aria-hidden="true"> *</span>';
        }
        if ($options['tip']) {
            $html .= dynamics_field_help($options['tip']);
        }
        $html .= '</label>';
    }
    if ($options['inline'] && $label) {
        $html .= '<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">';
    }

    if (!$options['enhance']) {
        $html .= '<input class="form-control" type="file" name="'.fusion_fileinput_escape($input_html_name).'" id="'.fusion_fileinput_escape($input_id).'" accept="'.fusion_fileinput_escape(fusion_fileinput_accept_attribute($extensions, $types)).'"'.($options['multiple'] ? ' multiple' : '').($options['required'] && $initial === [] ? ' required' : '').($options['deactivate'] ? ' disabled' : '').' aria-describedby="'.fusion_fileinput_escape($described_by).'">';
    } else {
        $html .= '<div class="fusion-fileinput'.($compact ? ' fusion-fileinput--compact' : '').'" data-fusion-fileinput data-state="empty"'.($is_error ? ' data-invalid="true"' : '').'>';
        $html .= '<input class="fusion-fileinput__native" type="file" name="'.fusion_fileinput_escape($input_html_name).'" id="'.fusion_fileinput_escape($input_id).'" accept="'.fusion_fileinput_escape(fusion_fileinput_accept_attribute($extensions, $types)).'"'.($options['multiple'] ? ' multiple' : '').($options['required'] && $initial === [] ? ' required' : '').($options['deactivate'] ? ' disabled' : '').' aria-describedby="'.fusion_fileinput_escape($described_by).'"'.($is_error ? ' aria-invalid="true"' : '').'>';
        $html .= '<label class="fusion-fileinput__zone" for="'.fusion_fileinput_escape($input_id).'" role="button" tabindex="0">';
        $html .= '<span class="fusion-fileinput__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M12 16V8m0 0-3 3m3-3 3 3M5 15.5A3.5 3.5 0 0 1 7.2 9.3 5 5 0 0 1 17 10.5a3 3 0 0 1 0 6H7.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></span>';
        $html .= '<span class="fusion-fileinput__copy"><strong>'.fusion_fileinput_escape($compact ? $browse_label : (string)$options['drop_title']).'</strong><small id="'.fusion_fileinput_escape($description_id).'">'.fusion_fileinput_escape($description).'</small></span>';
        $html .= '</label>';
        $html .= '<div class="fusion-fileinput__status" role="status" aria-live="polite" aria-atomic="true"></div>';
        $html .= '<div class="fusion-fileinput__files" role="list"'.($config['showThumbnails'] ? '' : ' hidden').'></div>';
        $html .= '<script type="application/json" data-fusion-fileinput-config>'.json_encode($config, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT).'</script>';
        $html .= '</div>';
    }
    if (!$options['enhance']) {
        $html .= '<div id="'.fusion_fileinput_escape($description_id).'" class="fusion-fileinput__description">'.fusion_fileinput_escape($description).'</div>';
    }
    $html .= '<div id="'.fusion_fileinput_escape($error_id).'" class="fusion-fileinput__error" role="alert"'.($is_error ? '' : ' hidden').'>'.($is_error ? fusion_fileinput_escape((string)$options['error_text']) : '').'</div>';

    if ($options['media']) {
        $html .= fusion_fileinput_media_selector($base_name, $input_id, $input_value, $options);
    }
    if ($options['inline'] && $label) {
        $html .= '</div>';
    }
    $html .= '</div>';

    \Defender::getInstance()->add_field_session([
        'input_name'        => $base_name,
        'type'              => $types === ['image'] ? 'image' : 'file',
        'title'             => $title,
        'id'                => $input_id,
        'required'          => (bool)$options['required'],
        'safemode'          => (bool)$options['safemode'],
        'error_text'        => (string)$options['error_text'],
        'path'              => (string)$options['upload_path'],
        'thumbnail_folder'  => rtrim((string)$options['thumbnail_folder'], '/\\'),
        'thumbnail'         => (bool)$options['thumbnail'],
        'thumbnail_suffix'  => (string)$options['thumbnail_suffix'],
        'thumbnail_w'       => (int)$options['thumbnail_w'],
        'thumbnail_h'       => (int)$options['thumbnail_h'],
        'thumbnail_ratio'   => (int)$options['thumbnail_ratio'],
        'thumbnail2'        => (bool)$options['thumbnail2'],
        'thumbnail2_w'      => (int)$options['thumbnail2_w'],
        'thumbnail2_h'      => (int)$options['thumbnail2_h'],
        'thumbnail2_suffix' => (string)$options['thumbnail2_suffix'],
        'thumbnail2_ratio'  => (int)$options['thumbnail2_ratio'],
        'delete_original'   => (bool)$options['delete_original'],
        'max_width'         => (int)$options['max_width'],
        'max_height'        => (int)$options['max_height'],
        'max_count'         => (int)$options['max_count'],
        'max_byte'          => (int)$options['max_byte'],
        'multiple'          => (bool)$options['multiple'],
        'valid_ext'         => (string)$options['valid_ext'],
        'replace_upload'    => (bool)$options['replace_upload'],
    ]);

    fusion_load_script(DYNAMICS.'includes/form_fileinput/component.css', 'css');
    fusion_load_script(DYNAMICS.'assets/fileinput/ajax-upload-preview.js');
    fusion_load_script(DYNAMICS.'includes/form_fileinput/component.js');

    return dynamics_render_component_template('form_fileinput', $html);
}

function fusion_fileinput_normalize_extensions(array|string $extensions): array {
    $values = is_array($extensions) ? $extensions : preg_split('/[\s,|]+/', $extensions);
    $normalized = [];
    foreach ((array)$values as $extension) {
        $extension = strtolower(ltrim(trim((string)$extension), '.'));
        if ($extension !== '' && preg_match('/^[a-z0-9]{1,12}$/', $extension)) {
            $normalized[$extension] = $extension;
        }
    }
    return array_values($normalized);
}

function fusion_fileinput_extensions_for_types(array $types): string {
    $map = [
        'image' => '.jpg,.jpeg,.png,.gif,.bmp,.webp,.avif',
        'video' => '.mp4,.webm,.mov,.avi,.mpeg,.mpg',
        'audio' => '.mp3,.wav,.ogg,.m4a,.flac,.aac',
        'text'  => '.txt,.csv,.rtf,.md',
        'file'  => '.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.zip',
        'object'=> '.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.zip',
    ];
    $extensions = [];
    foreach ($types as $type) {
        $extensions = array_merge($extensions, fusion_fileinput_normalize_extensions($map[$type] ?? $map['file']));
    }
    return implode(',', array_map(static fn(string $ext): string => '.'.$ext, array_values(array_unique($extensions))));
}

function fusion_fileinput_accept_attribute(array $extensions, array $types): string {
    if ($extensions !== []) {
        return implode(',', array_map(static fn(string $ext): string => '.'.$ext, $extensions));
    }
    return implode(',', array_filter(array_map(static fn(string $type): string => in_array($type, ['image', 'video', 'audio'], TRUE) ? $type.'/*' : '', $types)));
}

function fusion_fileinput_acceptance_text(array $extensions, int $max_bytes, int $max_count): string {
    $names = array_map('strtoupper', array_slice($extensions, 0, 5));
    $type_text = $names === [] ? 'Files' : implode(', ', $names);
    if (count($extensions) > 5) {
        $type_text .= ' +'.(count($extensions) - 5).' more';
    }
    $count_text = $max_count > 1 ? ', up to '.$max_count.' files' : '';
    return $type_text.' (max. '.parsebytesize($max_bytes).$count_text.')';
}

function fusion_fileinput_initial_files(mixed $value, string $upload_path): array {
    $values = is_array($value) ? $value : ($value !== FALSE && $value !== '' ? [$value] : []);
    $files = [];
    foreach ($values as $item) {
        $item = trim((string)$item);
        if ($item === '') {
            continue;
        }
        $url = file_exists(rtrim($upload_path, '/\\').DIRECTORY_SEPARATOR.$item)
            ? rtrim(str_replace('\\', '/', $upload_path), '/').'/'.$item
            : $item;
        $files[] = ['name' => basename(str_replace('\\', '/', $item)), 'url' => $url];
    }
    return $files;
}

function fusion_fileinput_media_selector(string $input_name, string $input_id, mixed $input_value, array $options): string {
    $files = makefilelist((string)$options['upload_path'], '.|..|index.php|', TRUE, 'files', 'psd|txt|md|php|exe|bat|js');
    if ($files === []) {
        return '';
    }
    $html = form_hidden($input_name.'-mediaSelector', '', $input_value, ['input_id' => $input_id.'-mediaSelector']);
    $html .= '<div class="fusion-fileinput__media" data-fusion-fileinput-media><p>Select an existing file</p><div class="fusion-fileinput__media-grid">';
    foreach ($files as $file) {
        $url = rtrim(str_replace('\\', '/', (string)$options['upload_path']), '/').'/'.$file;
        $html .= '<button type="button" class="fusion-fileinput__media-item" data-file="'.fusion_fileinput_escape($file).'" aria-label="Select '.fusion_fileinput_escape($file).'"><img src="'.fusion_fileinput_escape($url).'" alt=""><span>'.fusion_fileinput_escape($file).'</span></button>';
    }
    $html .= '</div></div>';
    \Defender::getInstance()->add_field_session([
        'input_name' => $input_name.'-mediaSelector',
        'title'      => trim(str_replace('_', ' ', $input_name)),
        'id'         => $input_id.'-mediaSelector',
        'type'       => 'mediaSelect',
        'path'       => (string)$options['upload_path'],
        'required'   => (bool)$options['required'],
        'safemode'   => (bool)$options['safemode'],
    ]);
    return $html;
}

function fusion_fileinput_escape(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
