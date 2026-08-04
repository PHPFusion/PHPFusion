<?php

namespace PHPFusion\Administration\Page;

use RuntimeException;

final class AdminPage
{
    private array $definition;

    public function __construct(array $definition)
    {
        $this->definition = $definition;
    }

    public function render(): void
    {
        if (!empty($this->definition['access'])) {
            pageaccess((string)$this->definition['access']);
        }

        if (!empty($this->definition['breadcrumb'])) {
            add_breadcrumb($this->definition['breadcrumb']);
        }

        $viewName = $this->resolveView();
        $view = (array)($this->definition['views'][$viewName] ?? []);
        if ($view === []) {
            throw new RuntimeException('Admin page view is not configured: '.$viewName);
        }

        $this->loadAssets(array_merge(
            (array)($this->definition['assets'] ?? []),
            (array)($view['assets'] ?? [])
        ));

        $data = $this->invokeController($view['controller'] ?? NULL, $viewName);
        $content = self::template((string)($view['template'] ?? ''), $data + [
            'page' => $this,
            'definition' => $this->definition,
            'view_name' => $viewName,
        ]);

        if (!empty($view['layout'])) {
            $content = self::template((string)$view['layout'], $data + [
                'page' => $this,
                'definition' => $this->definition,
                'view_name' => $viewName,
                'content' => $content,
            ]);
        }

        $title = (string)($this->definition['title'] ?? '');
        $description = (string)($this->definition['description'] ?? '');
        if ($title !== '') {
            opentable($title.($description !== ''
                ? '<div class="'.framework_css('small text-muted').'">'.$description.'</div>'
                : ''));
        }

        echo $content;
        echo '<div class="fusion-admin-toast-region" data-fusion-toast-region aria-live="polite" aria-atomic="true"></div>';

        if ($title !== '') {
            closetable();
        }
    }

    public function endpoint(string $key): string
    {
        $endpoint = (string)($this->definition['endpoints'][$key] ?? $key);

        return BASEDIR.'api/index.php?api='.rawurlencode($endpoint);
    }

    public function openApiForm(
        string $formId,
        string $endpointKey,
        array $options = []
    ): string {
        $method = strtoupper((string)($options['method'] ?? 'POST'));
        $classes = framework_css((string)($options['class'] ?? 'm-0'));
        $validateOnChange = !empty($options['validate_on_change']);
        $html = openform($formId, 'post', FUSION_REQUEST, [
            'form_id' => $formId,
            'class' => $classes,
            'enctype' => !empty($options['enctype']),
            'honeypot' => !array_key_exists('honeypot', $options) || !empty($options['honeypot']),
        ]);
        $attributes = 'data-fusion-api-form data-endpoint="'.htmlspecialchars($this->endpoint($endpointKey), ENT_QUOTES).'"'.
            ' data-method="'.htmlspecialchars($method, ENT_QUOTES).'"'.
            ($validateOnChange ? ' data-validate-on-change' : '');

        return preg_replace('/<form\s/', '<form '.$attributes.' ', $html, 1) ?: $html;
    }

    public static function template(string $file, array $data = []): string
    {
        $resolved = realpath($file);
        if ($resolved === FALSE || !is_file($resolved)) {
            throw new RuntimeException('Admin page template was not found: '.$file);
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $resolved;

        return (string)ob_get_clean();
    }

    private function resolveView(): string
    {
        $views = (array)($this->definition['views'] ?? []);
        $default = (string)($this->definition['default_view'] ?? array_key_first($views) ?? 'index');
        $parameter = (string)($this->definition['view_parameter'] ?? 'view');
        $requested = trim((string)get($parameter));

        if ($requested !== '' && isset($views[$requested])) {
            return $requested;
        }
        if (check_get('id') && isset($views['edit'])) {
            return 'edit';
        }
        if (in_array((string)get('action'), ['new', 'add'], TRUE) && isset($views['new'])) {
            return 'new';
        }

        return isset($views[$default]) ? $default : (string)array_key_first($views);
    }

    private function invokeController(mixed $controller, string $viewName): array
    {
        if ($controller === NULL) {
            return [];
        }
        if (!is_callable($controller)) {
            throw new RuntimeException('Admin page controller is not callable for view: '.$viewName);
        }

        $result = call_user_func($controller, [
            'page' => $this,
            'view' => $viewName,
            'definition' => $this->definition,
        ]);

        return is_array($result) ? $result : [];
    }

    private function loadAssets(array $assets): void
    {
        fusion_load_script(THEMES.'templates/styles/fusion-admin-page.css', 'css');
        fusion_load_script(INCLUDES.'jscripts/fusion-admin-page.js', 'js');
        foreach ($assets as $asset) {
            if (is_string($asset)) {
                fusion_load_script($asset, str_ends_with(strtolower($asset), '.css') ? 'css' : 'js');
            } elseif (is_array($asset) && !empty($asset['path'])) {
                fusion_load_script((string)$asset['path'], (string)($asset['type'] ?? 'js'));
            }
        }
    }
}
