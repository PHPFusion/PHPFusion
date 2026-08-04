<?php

defined('IN_FUSION') || exit;

function bootstrap_component_escape(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function bootstrap_component_class(mixed $value): string
{
    return trim((string)preg_replace('/[^a-zA-Z0-9_:\-\[\]\/.% ]/', '', (string)$value));
}

function bootstrap_component_tone(mixed $class, string $fallback = 'secondary'): string
{
    $value = strtolower((string)$class);

    return match (TRUE) {
        str_contains($value, 'danger'), str_contains($value, 'destructive'), str_contains($value, 'error') => 'danger',
        str_contains($value, 'warning') => 'warning',
        str_contains($value, 'success') => 'success',
        str_contains($value, 'info') => 'info',
        str_contains($value, 'primary') => 'primary',
        default => $fallback,
    };
}

function bootstrap_render_alert(array $input): string
{
    $options = (array)($input['options'] ?? []);
    $tone = bootstrap_component_tone($options['class'] ?? 'alert-danger', 'danger');
    $dismiss = !empty($options['dismiss']);
    $class = bootstrap_component_class($options['class'] ?? '');
    $html = '<div class="alert alert-'.$tone.($dismiss ? ' alert-dismissible fade show' : '').
        ($class !== '' ? ' '.$class : '').'" role="'.($tone === 'danger' ? 'alert' : 'status').'">';
    $html .= $input['title'] ?? '';
    if ($dismiss) {
        $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="'.
            bootstrap_component_escape($options['close_label'] ?? 'Close').'"></button>';
    }

    return $html.'</div>';
}

function bootstrap_render_badge(array $input): string
{
    $options = (array)($input['options'] ?? []);
    $tone = bootstrap_component_tone($options['class'] ?? '', 'secondary');
    $class = bootstrap_component_class($options['class'] ?? '');
    $rounded = ($input['kind'] ?? 'badge') === 'label' ? ' rounded-2' : ' rounded-pill';

    return '<span class="badge text-bg-'.$tone.$rounded.($class !== '' ? ' '.$class : '').'">'.
        ($options['icon'] ?? '').($input['label'] ?? '').'</span>';
}

function bootstrap_render_progress(array $input): string
{
    $values = $input['value'] ?? 0;
    $title = $input['title'] ?? NULL;
    $options = (array)($input['options'] ?? []);
    $maximum = max(0.00001, (float)($options['max_unit'] ?? 100));
    $height = (string)($options['height'] ?? '20px');
    $width = (string)($options['width'] ?? '100%');
    $height = preg_match('/^\d+(?:\.\d+)?(?:px|rem|em|%)$/', $height) ? $height : '20px';
    $width = preg_match('/^\d+(?:\.\d+)?(?:px|rem|em|%|vw)$/', $width) ? $width : '100%';
    $root_class = bootstrap_component_class($options['class'] ?? '');
    $progress_class = bootstrap_component_class($options['progress_class'] ?? '');

    $display_value = static function(float $value) use ($options, $maximum): string {
        if (!empty($options['disabled'])) return '&#x221e;';
        if (!empty($options['as_unit'])) return bootstrap_component_escape($value.' / '.$maximum);
        if (!empty($options['as_star'])) {
            $rating = round((($value / $maximum) * 5) * 2) / 2;
            return bootstrap_component_escape(rtrim(rtrim(number_format($rating, 1, '.', ''), '0'), '.').' / 5');
        }

        return bootstrap_component_escape($value.(!empty($options['as_percent']) ? '%' : ''));
    };
    $bar_class = static function(float $percent, int $index = 0) use ($options): string {
        if (!empty($options['bar_class'])) {
            $classes = (array)$options['bar_class'];
            return bootstrap_component_class($classes[$index] ?? reset($classes));
        }
        $normal = ['bg-danger', 'bg-warning', 'bg-info', 'bg-success'];
        $reverse = array_reverse($normal);
        $position = $percent > 80 ? 3 : ($percent > 60 ? 2 : ($percent > 40 ? 1 : 0));

        return (!empty($options['reverse']) ? $reverse : $normal)[$position];
    };

    $html = '<div class="'.($root_class !== '' ? $root_class : 'w-100').'" style="width:'.
        bootstrap_component_escape($width).'">';

    if (is_array($values)) {
        $total = !empty($options['stacked']) ? (float)array_sum($values) : $maximum;
        $segments = '';
        $legend = '';
        $index = 0;
        foreach ($values as $key => $raw_value) {
            $value = (float)$raw_value;
            $percent = $total > 0 ? max(0, min(100, ($value / $total) * 100)) : 0;
            $segment_title = !empty($options['stacked'])
                ? ucfirst((string)$key)
                : (is_array($title) ? (string)($title[$index] ?? '') : ucfirst((string)$key));
            $color = $bar_class($percent, $index);
            $segments .= '<div class="progress-bar '.$color.'" style="width:'.
                bootstrap_component_escape($percent).'%" title="'.
                bootstrap_component_escape($segment_title.': '.$display_value($value)).'"></div>';
            $legend .= '<span class="d-inline-flex align-items-center gap-1 me-3 small"><span class="rounded-1 '.
                $color.'" style="width:.625rem;height:.625rem"></span>'.$segment_title.
                ' <span class="text-secondary">('.bootstrap_component_escape($value).')</span></span>';
            $index++;
        }
        if (empty($options['hide_info'])) {
            $main_title = is_array($title) ? 'Progress' : (string)$title;
            $html .= '<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2"><span class="fw-medium">'.
                $main_title.'</span><span>'.$legend.'</span></div>';
        }
        $html .= '<div class="progress '.$progress_class.'" style="height:'.bootstrap_component_escape($height).
            '" role="group" aria-label="'.bootstrap_component_escape(is_array($title) ? 'Progress' : ($title ?: 'Progress')).'">'.
            $segments.'</div>';
    } else {
        $value = (float)$values;
        $percent = max(0, min(100, ($value / $maximum) * 100));
        $visible_value = $display_value($value);
        if (empty($options['hide_info']) && $title !== NULL && $title !== '') {
            $html .= '<div class="d-flex align-items-center justify-content-between gap-2 mb-1"><span>'.
                $title.'</span><span class="text-secondary">'.$visible_value.'</span></div>';
        }
        $html .= '<div class="progress '.$progress_class.'" style="height:'.bootstrap_component_escape($height).
            '" role="progressbar" aria-valuenow="'.bootstrap_component_escape($percent).
            '" aria-valuemin="0" aria-valuemax="100" aria-label="'.
            bootstrap_component_escape(is_scalar($title) && $title !== '' ? $title : 'Progress').'">';
        $html .= '<div class="progress-bar '.$bar_class($percent).'" style="width:'.
            bootstrap_component_escape($percent).'%" title="'.$visible_value.'"></div></div>';
    }

    return $html.'</div>';
}
