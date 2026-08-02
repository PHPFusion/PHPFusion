# PHPFusion DataTable SDK

Path: `includes/datatable_include.php`

The DataTable SDK wraps jQuery DataTables for PHPFusion pages. It has two main parts:

- `fusiontable()` renders and initializes the browser DataTable.
- `build_fusiontable_query()` builds DataTables-compatible server-side JSON responses.

Use this SDK for large listings, AJAX tables, column sorting, global search, pagination, filters, and repeated admin/frontend tables.

## Basic Remote Table

```php
$table_id = fusiontable('topic_table', [
    'retrieve'     => TRUE,
    'remote_file'  => BASEDIR . 'api/?api=subjects',
    'server_side'  => TRUE,
    'processing'   => TRUE,
    'pagination'   => TRUE,
    'page_length'  => 50,
    'ajax_filters' => [
        'filter_program',
        'filter_stream',
        'filter_level',
    ],
    'columns'      => [
        ['data' => 'topic_id', 'title' => 'ID', 'visible' => FALSE, 'searchable' => FALSE],
        ['data' => 'topic_program', 'title' => 'Program'],
        ['data' => 'topic_name', 'title' => 'Topic Name'],
        ['data' => 'topic_status', 'title' => 'Status'],
    ],
    'order'        => [[0, 'asc']],
]);
```

Render the matching table with the generated ID:

```php
?>
<table id="<?= $table_id ?>" class="table">
    <thead></thead>
    <tbody></tbody>
</table>
<?php
```

## `fusiontable()` Options

Common options:

| Option | Type | Purpose |
|---|---:|---|
| `remote_file` | string | AJAX endpoint URL. When set, the SDK initializes a remote DataTable. |
| `columns` | array | DataTables column definitions. Each item usually contains `data` and `title`. |
| `server_side` | bool | Enables DataTables server-side mode. |
| `processing` | bool | Shows DataTables processing state. |
| `page_length` | int | Initial page length. |
| `pagination` | bool | Set `FALSE` to disable paging. |
| `ordering` | bool | Set `FALSE` to disable ordering. |
| `order` | array | Default DataTables ordering, for example `[[0, 'asc']]`. |
| `ajax_filters` | array | Element IDs whose values are sent to the AJAX endpoint and redraw the table on change. |
| `hide_search_input` | bool | Hides the built-in search input. |
| `responsive` | bool | Enables responsive behavior. |
| `toolbar` | bool | Renders the SDK `slot-1` and `slot-2` toolbar mounts without enabling export buttons. Use with `fusiontable_append_button()`. |
| `buttons` | bool | Enables copy, Excel, PDF, and column-visibility buttons. |
| `state_save` | bool | Enables DataTables local state storage. |
| `row_reorder` | bool | Enables drag sorting with the configured reorder URL. |
| `row_reorder_url` | string | Endpoint for row reorder submissions. |
| `col_resize` | bool | Enables column resize plugin behavior. |
| `col_reorder` | bool | Enables column reorder plugin behavior. |
| `fixed_header` | bool | Enables fixed header plugin behavior. |
| `js_script` | string | Extra JavaScript appended after initialization. |
| `debug` | bool | Prints generated JavaScript. |

`ajax_filters` expects IDs without `#`. For example, `filter_program` reads `$('#filter_program').val()` and sends it to the endpoint as `filter_program`.

## Server-Side API

Server-side endpoints should return:

```php
[
    'draw'            => (int)($_GET['draw'] ?? 0),
    'recordsTotal'    => $recordsTotal,
    'recordsFiltered' => $recordsFiltered,
    'data'            => $rows,
]
```

The helper `build_fusiontable_query()` handles this shape.

```php
$baseSql = "SELECT r.room_id, r.room_name, r.room_status
            FROM " . DB_SCHOOL_ROOMS . " r";

$options = [
    'columns_map' => [
        0 => 'r.room_id',
        1 => 'r.room_name',
        2 => 'r.room_status',
    ],
    'sql_filter'  => [
        'filter_status' => 'r.room_status',
    ],
    'request'     => '_GET',
];

$response = build_fusiontable_query($baseSql, $options);

header('Content-Type: application/json');
echo json_encode($response);
exit;
```

## `build_fusiontable_query()` Options

| Option | Type | Purpose |
|---|---:|---|
| `columns_map` | array | Maps DataTables column indexes to SQL columns or expressions. Used for ordering and, by default, global search. |
| `search_map` | array | Optional SQL columns or expressions used only for global search. Falls back to `columns_map` when omitted. |
| `sql_filter` | array | Maps request filter keys to SQL conditions. |
| `request` | string | `_GET`, `_POST`, or `_REQUEST`. Defaults to `_REQUEST`. |
| `render` | array | Field callbacks applied after fetching rows. Useful for HTML formatting. |
| `debug` | bool | Prints SQL and parameters. |

## `columns_map`

`columns_map` must match the DataTables column indexes from `fusiontable()`.

```php
'columns_map' => [
    0 => 't.topic_id',
    1 => "CONCAT_WS('/', CONCAT(pg.program_alias, '-', t.topic_year), CONCAT_WS(' ', t.topic_cat, t.topic_unit))",
    2 => 't.topic_name',
    3 => 'topic_milestones',
    4 => 'topic_questions',
]
```

The helper uses this map for:

- Global search, unless `search_map` is provided.
- `ORDER BY` when DataTables sends column ordering.

Do not map a visible column to a value that is only created in PHP after the SQL query. Search and ordering happen in SQL before render callbacks run.

## `search_map`

Use `search_map` when the displayed value is composed, formatted, or includes child records. For complex values, expose a SQL-visible search column in a wrapped base query and point `search_map` to that column.

Example: a visible Program column renders as:

- `SC-S2/Tema 1` for Tema rows.
- `Unit 1.5` for Unit rows.

The table display is generated after SQL, so the search query needs a SQL field that represents the searchable text:

```php
$baseSql = "SELECT * FROM (
    SELECT t.topic_id,
           t.topic_name,
           CONCAT(pg.program_alias, '-', t.topic_year) AS topic_program_sort,
           CONCAT_WS(' ',
               t.topic_id,
               CONCAT(pg.program_alias, '-', t.topic_year),
               CONCAT_WS('/', CONCAT(pg.program_alias, '-', t.topic_year), CONCAT_WS(' ', t.topic_cat, t.topic_unit)),
               CONCAT_WS(' ', t.topic_cat, t.topic_unit),
               t.topic_name,
               COALESCE((
                   SELECT GROUP_CONCAT(CONCAT_WS(' ', child.topic_id, child.topic_cat, child.topic_unit, child.topic_name) SEPARATOR ' ')
                   FROM " . DB_PROGRAM_TOPICS . " child
                   WHERE child.topic_parent = t.topic_id
               ), '')
           ) AS topic_search
    FROM " . DB_PROGRAM_TOPICS . " t
    LEFT JOIN " . DB_PROGRAMS . " pg ON pg.program_id = t.program_id
    WHERE t.topic_parent = 0
) _root WHERE 1=1";

$options = [
    'columns_map' => [
        0 => '_root.topic_id',
        1 => '_root.topic_program_sort',
        2 => '_root.topic_name',
    ],
    'search_map'  => [
        1 => '_root.topic_search',
    ],
    'request'     => '_GET',
];
```

This allows searches like `SC-S2`, `SC-S2/Tema`, `Tema 1`, `Unit 1.5`, topic names, and visible IDs to match even when the table shows a flattened parent/child tree.

Keep `WHERE 1=1` on the outer query when using this wrapped pattern. The helper appends global search and filter conditions to the end of the base SQL.

## Filters

Simple equality filter:

```php
'sql_filter' => [
    'filter_level' => 't.topic_year',
]
```

`find_in_set` filter:

```php
'sql_filter' => [
    'filter_stream' => [
        'column'   => 't.stream_id',
        'operator' => 'find_in_set',
    ],
]
```

Hierarchy filters are also supported when configured with `table`, `primary_key`, and `parent_column`.

## Render Callbacks

Use `render` for final HTML labels, badges, buttons, links, and icons.

```php
'render' => [
    'room_status' => function ($row) {
        return $row['room_status']
            ? "<span class='badge bg-success'>Active</span>"
            : "<span class='badge bg-danger'>Inactive</span>";
    },
]
```

Render callbacks do not affect SQL search or ordering. Add equivalent SQL expressions to `search_map` when the rendered value must be searchable.

## Practical Rules

- Keep `columns` in `fusiontable()` aligned with `columns_map` in the API.
- Hidden columns may still be used for ordering, such as hidden IDs.
- Use `search_map` for composed display values, HTML-rendered values, aliases, child-row text, and labels that do not exist as a single SQL column.
- Avoid putting aggregate aliases such as `topic_milestones` into global search unless the base SQL supports them in `WHERE`.
- Keep search expressions text-based and MySQL-compatible.
- Always return JSON and call `exit` after output in API handlers.
- Run `php -l` after editing endpoints or the SDK helper.
