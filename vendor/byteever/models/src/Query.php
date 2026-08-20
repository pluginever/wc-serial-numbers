<?php

namespace WooCommerceSerialNumbers\B8\Models;

defined('ABSPATH') || exit;
/**
 * Query builder class.
 *
 * Constructs and executes database queries for models.
 *
 * @since 1.0.0
 * @package \B8\Models
 */
class Query
{
    /**
     * Model instance.
     *
     * @since 1.0.0
     * @var Model
     */
    protected $model;
    /**
     * Model's table name.
     *
     * @since 1.0.0
     * @var string
     */
    protected $table;
    /**
     * Model's primary key column name.
     *
     * @since 1.0.0
     * @var string
     */
    protected $primary_key;
    /**
     * Model's cache group name.
     *
     * @since 1.0.0
     * @var string
     */
    protected $cache_group;
    /**
     * Model's meta type.
     *
     * @since 1.0.0
     * @var string|false
     */
    protected $meta_type;
    /**
     * Model's table columns.
     *
     * @since 1.0.0
     * @var array<string>
     */
    protected $columns;
    /**
     * Hash of the query for caching.
     *
     * @since 1.0.0
     * @var string|null
     */
    protected $hash = null;
    /**
     * Vars used by builder methods.
     *
     * @since 1.0.0
     * @var array<string, mixed>
     */
    protected $sql_vars = array('join' => array(), 'where' => array(), 'groupby' => array(), 'having' => array());
    /**
     * Query vars, after parsing.
     *
     * @since 1.0.0
     * @var array<string, mixed>
     */
    protected $query_vars = array(
        'select' => '*',
        'orderby' => array(),
        'order' => 'DESC',
        'limit' => 100,
        'offset' => 0,
        'search' => '',
        'search_columns' => array(),
        'no_found_rows' => true,
        'count' => false,
        'meta_query' => array(),
        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Meta query is optimized in the main query.
        'meta_key' => '',
        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
        'meta_value' => '',
        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
        'meta_type' => '',
        'meta_compare' => '',
        'tax_query' => array(),
        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Tax query is optimized in the main query.
        'with' => array(),
        // Caching.
        'update_item_cache' => true,
        'update_meta_cache' => true,
    );
    /**
     * SQL clauses.
     *
     * @since 1.0.0
     * @var array<string, string>
     */
    protected $clauses = array('select' => '', 'from' => '', 'join' => '', 'where' => '', 'groupby' => '', 'having' => '', 'orderby' => '', 'limits' => '');
    /**
     * SQL query.
     *
     * @since 1.0.0
     * @var string
     */
    protected $request;
    /**
     * Operator modifiers mapping.
     *
     * @since 1.0.0
     * @var array<string, string>
     */
    protected $modifiers = array(
        // Equality.
        '' => '=',
        'eq' => '=',
        'is' => '=',
        'neq' => '!=',
        'is_not' => '!=',
        'not' => '!=',
        // Comparison.
        'lt' => '<',
        'lte' => '<=',
        'gt' => '>',
        'gte' => '>=',
        'between' => 'BETWEEN',
        'not_between' => 'NOT BETWEEN',
        // String matching.
        'like' => 'LIKE',
        'contains' => 'LIKE',
        'not_like' => 'NOT LIKE',
        'not_contains' => 'NOT LIKE',
        'starts_with' => 'STARTS WITH',
        'ends_with' => 'ENDS WITH',
        'regexp' => 'REGEXP',
        'not_regexp' => 'NOT REGEXP',
        // Set operations.
        'in' => 'IN',
        'includes' => 'IN',
        'not_in' => 'NOT IN',
        'excludes' => 'NOT IN',
        'in_set' => 'FIND IN SET',
        'not_in_set' => 'NOT FIND IN SET',
        // Null checks.
        'null' => 'IS NULL',
        'is_empty' => 'IS NULL',
        'exists' => 'IS NOT NULL',
        'not_exists' => 'IS NULL',
        'not_null' => 'IS NOT NULL',
        'is_not_empty' => 'IS NOT NULL',
        // Boolean.
        'is_true' => 'IS TRUE',
        'is_false' => 'IS FALSE',
    );
    /**
     * Query constructor.
     *
     * @since 1.0.0
     *
     * @param Model $model Model instance.
     */
    public function __construct($model)
    {
        $this->model = $model;
        $this->table = $model->get_table();
        $this->primary_key = $model->get_primary_key();
        $this->cache_group = $model->get_cache_group();
        $this->meta_type = $model->get_meta_type();
        $this->columns = $model->get_columns();
        $this->sql_vars = array_fill_keys(array_keys($this->sql_vars), array());
        $this->query_vars = wp_parse_args($model->get_query_vars(), $this->query_vars);
        $this->set_var('search_columns', $model->get_searchable());
    }
    /**
     * Retrieves query variable.
     *
     * @since 1.0.0
     *
     * @param string $key Query variable key.
     * @param mixed  $fallback Default value if key doesn't exist.
     *
     * @return mixed Query variable value or fallback.
     */
    public function get_var($key, $fallback = null)
    {
        if (isset($this->query_vars[$key])) {
            return $this->query_vars[$key];
        }
        return $fallback;
    }
    /**
     * Sets query variable.
     *
     * @since 1.0.0
     *
     * @param array<string, mixed>|string $key   Query variable key or array of key-value pairs.
     * @param mixed                       $value Value to set if key is string.
     *
     * @return static Query instance for chaining.
     */
    public function set_var($key, $value = null): self
    {
        $query_vars = is_string($key) ? array($key => $value) : $key;
        if (!empty($query_vars)) {
            $this->query_vars = wp_parse_args($query_vars, $this->query_vars);
        }
        return $this;
    }
    /**
     * Set the SELECT clause.
     *
     * @since 1.0.0
     *
     * @param string|array<int, string> $columns Columns to select.
     *
     * @return $this
     */
    public function select($columns): self
    {
        $this->query_vars['select'] = wp_parse_list($columns);
        return $this;
    }
    /**
     * Add search functionality.
     *
     * @since 1.0.0
     *
     * @param string             $search  Search term.
     * @param array<int, string> $columns Columns to search in.
     *
     * @return $this
     */
    public function search($search, $columns = array()): self
    {
        $this->query_vars['search'] = $search;
        if (!empty($columns)) {
            $this->query_vars['search_columns'] = $columns;
        }
        return $this;
    }
    /**
     * Add a JOIN clause.
     *
     * @since 1.0.0
     *
     * @param string $table Table to join.
     * @param string $first First column for join condition.
     * @param string $operator Comparison operator for join condition.
     * @param string $second Second column for join condition.
     * @param string $type Join type (INNER, LEFT, RIGHT, FULL).
     *
     * @return $this
     */
    public function join($table, $first, $operator = '=', $second = null, $type = 'INNER'): self
    {
        if (3 === func_num_args()) {
            $second = $operator;
            $operator = '=';
        }
        $operator = $this->parse_operator($operator);
        $condition = $this->parse_column($first) . " {$operator} " . $this->parse_column((string) $second, $table);
        $this->sql_vars['join'][] = array('table' => $this->parse_identifier((string) $table), 'condition' => $condition, 'type' => $type);
        return $this;
    }
    /**
     * Add a WHERE clause.
     *
     * @since 1.0.0
     *
     * @param string|callable $column Column name or callback for nested conditions.
     * @param string          $operator Comparison operator or relation for nested groups.
     * @param mixed           $value Value to compare.
     * @param string          $boolean Boolean operator to combine with previous conditions (AND/OR).
     *
     * @return $this
     */
    public function where($column, $operator = '=', $value = null, $boolean = 'AND'): self
    {
        if (is_callable($column)) {
            $query = new self($this->model);
            $column($query);
            $boolean = is_string($operator) ? strtoupper($operator) : 'AND';
            $conditions = $query->sql_vars['where'];
            if (!empty($conditions)) {
                $conditions['boolean'] = $boolean;
                $this->sql_vars['where'][] = $conditions;
            }
            return $this;
        }
        $op_key = strtolower($operator);
        if (2 === func_num_args() && !isset($this->modifiers[$op_key])) {
            $value = $operator;
            $operator = '=';
        } elseif (isset($this->modifiers[$op_key])) {
            $operator = $this->modifiers[$op_key];
        }
        $this->sql_vars['where'][] = array('column' => $column, 'value' => $value, 'operator' => $operator, 'boolean' => $boolean);
        return $this;
    }
    /**
     * Add a WHERE clause with casting.
     *
     * @since 1.0.0
     *
     * @param string $column Column name.
     * @param mixed  $value Value to compare.
     * @param string $cast Data type to cast to (e.g. 'UNSIGNED, CHAR').
     * @param string $operator Comparison operator.
     * @param string $boolean Boolean operator to combine with previous conditions.
     *
     * @return $this
     */
    public function where_cast($column, $value, $cast, $operator = '=', $boolean = 'AND'): self
    {
        $this->sql_vars['where'][] = array('column' => $column, 'value' => $value, 'operator' => $operator, 'cast' => $cast, 'boolean' => $boolean);
        return $this;
    }
    /**
     * Add meta query condition.
     *
     * @since 1.0.0
     *
     * @param string $key Meta key.
     * @param string $compare Comparison operator.
     * @param mixed  $value Meta value.
     *
     * @return $this
     */
    public function where_meta($key, $compare = '=', $value = null): self
    {
        if (is_null($value)) {
            $value = $compare;
            $compare = '=';
        }
        $this->query_vars['meta_query'][] = array('key' => $key, 'value' => $value, 'compare' => $compare);
        return $this;
    }
    /**
     * Add a group of conditions from a filter set.
     *
     * @since 1.0.0
     *
     * @param array<string, mixed> $filters Filter set as `[ 'match' => 'all|any', 'filters' => [ ... ] ]`, or a bare list of rows.
     *
     * @return static Query instance for chaining.
     */
    public function where_filters(array $filters): self
    {
        $rows = isset($filters['filters']) && is_array($filters['filters']) ? $filters['filters'] : $filters;
        $match = isset($filters['match']) ? strtolower((string) $filters['match']) : 'all';
        $rows = array_filter($rows, static function ($row) {
            return is_array($row) && !empty($row['field']) && !empty($row['operator']);
        });
        if (!empty($rows)) {
            $this->where(function ($query) use ($rows) {
                foreach ($rows as $filter) {
                    $value = $this->parse_filter_value($filter['value'] ?? null);
                    $query->where($filter['field'], $filter['operator'], $value);
                }
            }, 'any' === $match ? 'OR' : 'AND');
        }
        /**
         * Filters the query after a filter set is applied.
         *
         * A consumer can add a join and its conditions here for a filter that
         * targets a table this query has not joined.
         *
         * @since 1.0.0
         *
         * @param Query             $query Query instance.
         * @param array<int, mixed> $rows  Applied filter rows.
         * @param string            $match Match mode, `all` or `any`.
         */
        return $this->model->apply_filters('where_filters', $this, $rows, $match);
    }
    /**
     * Set the GROUP BY clause.
     *
     * @since 1.0.0
     *
     * @param string|array<int, string> $columns Columns to group by.
     *
     * @return $this
     */
    public function group_by($columns): self
    {
        if (is_array($columns)) {
            $this->sql_vars['groupby'] = $columns;
        } else {
            $this->sql_vars['groupby'] = array($columns);
        }
        return $this;
    }
    /**
     * Set the HAVING clause.
     *
     * @since 1.0.0
     *
     * @param string $condition Having condition.
     *
     * @return $this
     */
    public function having($condition): self
    {
        $this->sql_vars['having'][] = $condition;
        return $this;
    }
    /**
     * Set the ORDER BY clause.
     *
     * @since 1.0.0
     *
     * @param string $column Column to order by.
     * @param string $direction Order direction (ASC or DESC).
     *
     * @return $this
     */
    public function order_by($column, $direction = 'ASC'): self
    {
        $direction = $this->parse_order($direction, 'ASC');
        $this->query_vars['orderby'][] = array('column' => $column, 'direction' => $direction);
        return $this;
    }
    /**
     * Set the LIMIT clause.
     *
     * @since 1.0.0
     *
     * @param int $limit Number of records to limit.
     * @param int $offset Optional offset.
     *
     * @return $this
     */
    public function limit($limit, $offset = 0): self
    {
        $this->query_vars['limit'] = (int) $limit;
        if ($offset > 0) {
            $this->query_vars['offset'] = (int) $offset;
        }
        return $this;
    }
    /**
     * Set the page number.
     *
     * @since 1.0.0
     *
     * @param int $page Page number.
     *
     * @return $this
     */
    public function page($page): self
    {
        $this->query_vars['page'] = max(1, (int) $page);
        return $this;
    }
    /**
     * Set relations to eager load.
     *
     * @since 1.0.0
     *
     * @param string|array<int, string> $relations The relation name(s) to eager load.
     *
     * @return static
     */
    public function with($relations): self
    {
        return $this->set_var('with', $relations);
    }
    /**
     * Main method to get results from the query.
     * Get the queried items or count of items.
     *
     * @since 1.0.0
     * @return array<int, mixed>|int List of items or number of items found.
     */
    public function get()
    {
        global $wpdb;
        $this->prepare_query();
        $clauses = $this->clauses;
        $qv = $this->query_vars;
        $found_rows = $qv['no_found_rows'] ? '' : 'SQL_CALC_FOUND_ROWS';
        $query_key = md5($clauses['join'] . $clauses['where'] . $clauses['having'] . $clauses['groupby'] . $clauses['orderby'] . $clauses['limits']);
        $last_changed = wp_cache_get_last_changed($this->cache_group);
        $cache_key = $this->cache_group . ':' . $query_key . ':' . $last_changed;
        $cache_value = wp_cache_get($cache_key, $this->cache_group);
        $this->request = "SELECT COUNT(*)\n\t\t\t\t{$clauses['from']}\n\t\t\t\t{$clauses['join']}\n\t\t\t\t{$clauses['where']}\n\t\t\t\t{$clauses['groupby']}\n\t\t\t\t{$clauses['having']}";
        $this->request = (string) preg_replace('/\s+/', ' ', $this->request);
        if ($qv['count'] && (false === $cache_value || !isset($cache_value['count']))) {
            $cache_value = is_array($cache_value) ? $cache_value : array();
            if (!empty($clauses['groupby'])) {
                $cache_value['count'] = count($wpdb->get_results($this->request));
                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Query prepared in prepare_query method.
            } else {
                $cache_value['count'] = (int) $wpdb->get_var($this->request);
                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Query prepared in prepare_query method.
            }
            wp_cache_set($cache_key, $cache_value, $this->cache_group);
        }
        // if count is requested, then return the count.
        if ($qv['count']) {
            return $cache_value['count'];
        }
        $this->request = "SELECT {$found_rows} {$clauses['select']}\n\t\t\t\t{$clauses['from']}\n\t\t\t\t{$clauses['join']}\n\t\t\t\t{$clauses['where']}\n\t\t\t\t{$clauses['groupby']}\n\t\t\t\t{$clauses['having']}\n\t\t\t\t{$clauses['orderby']}\n\t\t\t\t{$clauses['limits']}";
        $this->request = (string) preg_replace('/\s+/', ' ', $this->request);
        if (false === $cache_value || !isset($cache_value['items'])) {
            $cache_value = is_array($cache_value) ? $cache_value : array();
            $cache_value['items'] = $wpdb->get_results($this->request);
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Query prepared in prepare_query method.
            if ($cache_value['items'] && !$qv['no_found_rows'] && !empty($clauses['limits'])) {
                /**
                 * Filters the query used to retrieve the found object count.
                 *
                 * @since 1.0.0
                 *
                 * @param Query  $query The current query instance.
                 *
                 * @param string $sql The query string.
                 */
                $count_request = $this->model->apply_filters('count_sql', 'SELECT FOUND_ROWS()', $this);
                $cache_value['count'] = (int) $wpdb->get_var($count_request);
                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Query prepared in prepare_query method.
            }
            wp_cache_set($cache_key, $cache_value, $this->cache_group);
        }
        $items = $cache_value['items'];
        // Return empty array if no items found.
        if (empty($items)) {
            return array();
        }
        // If only ids are requested, then return the ids.
        if (in_array('ids', $qv['select'], true)) {
            $ids = wp_list_pluck($items, $this->primary_key);
            return array_map('intval', $ids);
        }
        // Prime the caches .
        if ($qv['update_item_cache']) {
            $this->prime_cache($items, $qv['update_meta_cache']);
        }
        // Prepare the items.
        $items = array_map(function ($item) {
            $data = array_map('maybe_unserialize', wp_unslash(get_object_vars($item)));
            /**
             * Filters all data after reading from the database.
             *
             * @since 1.0.0
             *
             * @param array $data The data array.
             */
            $data = $this->model->apply_filters('data', $data);
            foreach ($data as $key => $value) {
                /**
                 * Filters a single field value after reading from the database.
                 *
                 * The dynamic portion of the hook name, `$key`, refers to the column name.
                 *
                 * @since 1.0.0
                 *
                 * @param mixed $value The field value.
                 * @param array $data  All data for the current item.
                 */
                $data[$key] = $this->model->apply_filters($key, $value, $data);
            }
            return $data;
        }, $items);
        // If specific select are requested, then return only those select.
        $columns = array_intersect($qv['select'], $this->columns);
        if (!empty($columns) && is_array($columns)) {
            return array_map(function ($item) use ($columns) {
                $data = wp_array_slice_assoc($item, $columns);
                return (new $this->model())->hydrate($data, true)->load_metadata();
            }, $items);
        }
        // Finally, return the items.
        $items = array_map(function ($item) {
            return (new $this->model())->hydrate($item, true)->load_metadata();
        }, $items);
        // Eager load relations if requested.
        if (!empty($qv['with'])) {
            $relations = is_array($qv['with']) ? $qv['with'] : array($qv['with']);
            foreach ($relations as $relation_name) {
                if (!$this->model->has_relation($relation_name)) {
                    continue;
                }
                $relation = $this->model->{$relation_name}();
                $relation->load($items, $relation_name);
            }
        }
        return $items;
    }
    /**
     * Find a record by ID.
     *
     * @since 1.0.0
     *
     * @param mixed $id Primary key value or array of conditions.
     *
     * @return Model|null The model instance or null if not found.
     */
    public function find($id): ?Model
    {
        global $wpdb;
        if (empty($id) || !is_scalar($id) && !is_array($id)) {
            return null;
        }
        $model = $this->model;
        if (is_scalar($id)) {
            $id = array($model->get_primary_key() => $id);
        }
        $where = array_filter($id, fn($value) => !empty($value) || is_null($value));
        if (empty($where)) {
            return null;
        }
        $where = $model->resolve_alias($where);
        $where = wp_array_slice_assoc(wp_parse_args($where, $model->get_query_vars()), $model->get_columns());
        if (empty($where)) {
            return null;
        }
        $pk = $model->get_primary_key();
        $_args = array_diff_key($where, $model->get_query_vars());
        // Pure primary key lookups share the bare key used when query results are primed.
        if (array($pk) === array_keys($_args) && is_scalar($_args[$pk])) {
            $cache_key = (string) $_args[$pk];
        } else {
            ksort($where);
            $pairs = array();
            foreach ($where as $column => $value) {
                // Values are encoded so separator characters inside data cannot alias two different lookups.
                $pairs[] = $column . ':' . implode(',', array_map('rawurlencode', (array) $value));
            }
            $cache_key = implode(',', $pairs);
        }
        $data = $model->get_cache($cache_key);
        if (false === $data) {
            $conditions = array();
            foreach ($where as $key => $value) {
                if (is_array($value)) {
                    $conditions[] = $wpdb->prepare("`{$key}` IN (" . implode(',', array_fill(0, count($value), '%s')) . ')', $value);
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Column name is from model property.
                } elseif (is_null($value)) {
                    $conditions[] = "`{$key}` IS NULL";
                } elseif (is_numeric($value)) {
                    $conditions[] = $wpdb->prepare("`{$key}` = %d", $value);
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Column name is from model property.
                } else {
                    $conditions[] = $wpdb->prepare("`{$key}` = %s", $value);
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Column name is from model property.
                }
            }
            $table = $wpdb->prefix . $model->get_table();
            $query = "SELECT * FROM `{$table}` WHERE " . implode(' AND ', $conditions);
            $data = $wpdb->get_row($query);
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Query built from prepared statements.
            if (null === $data) {
                return null;
            }
            $model->set_cache($cache_key, $data);
            $model->set_cache($data->{$model->get_primary_key()}, $data);
        }
        $data = array_map('maybe_unserialize', wp_unslash(get_object_vars($data)));
        /**
         * Filters all data after reading from the database.
         *
         * @since 1.0.0
         *
         * @param array $data The data array.
         */
        $data = $model->apply_filters('data', $data);
        foreach ($data as $key => $value) {
            /**
             * Filters a single field value after reading from the database.
             *
             * The dynamic portion of the hook name, `$key`, refers to the column name.
             *
             * @since 1.0.0
             *
             * @param mixed $value The field value.
             * @param array $data  All data for the current item.
             */
            $data[$key] = $model->apply_filters($key, $value, $data);
        }
        return $model->hydrate($data, true)->load_metadata();
    }
    /**
     * Get the first item matching the query vars.
     *
     * @since 1.0.0
     * @return object|null The first item or null if not found.
     */
    public function first(): ?object
    {
        $items = $this->limit(1)->get();
        if (!is_array($items) || empty($items)) {
            return null;
        }
        $first = reset($items);
        return is_object($first) ? $first : null;
    }
    /**
     * Get the last item matching the query vars.
     *
     * @since 1.0.0
     * @return object|null The last item or null if not found.
     */
    public function last(): ?object
    {
        $items = $this->order_by($this->primary_key, 'DESC')->limit(1)->get();
        if (!is_array($items) || empty($items)) {
            return null;
        }
        $last = end($items);
        return is_object($last) ? $last : null;
    }
    /**
     * Get the count of matching records.
     *
     * @since 1.0.0
     * @return int The count of matching records.
     */
    public function count(): int
    {
        $this->query_vars['count'] = true;
        $count = $this->get();
        $this->query_vars['count'] = false;
        return (int) $count;
    }
    /**
     * Get aggregated value from matching records.
     *
     * @since 1.0.0
     *
     * @param string $func Aggregation function: 'sum', 'avg', 'min', 'max', 'count', 'count_distinct'.
     * @param string $column Column to aggregate (optional for count).
     *
     * @return float|null The aggregated value or null if no results.
     */
    public function aggregate($func, $column = null): ?float
    {
        global $wpdb;
        $this->prepare_query();
        $clauses = $this->clauses;
        $func = strtoupper($func);
        $valid_functions = array('SUM', 'AVG', 'MIN', 'MAX', 'COUNT', 'COUNT_DISTINCT');
        if (!in_array($func, $valid_functions, true)) {
            return null;
        }
        $qualified = $this->parse_column((string) $column);
        $select = 'COUNT' === $func ? 'COUNT(*)' : ('COUNT_DISTINCT' === $func ? "COUNT(DISTINCT {$qualified})" : $func . '(' . $qualified . ')');
        $query_key = md5($clauses['join'] . $clauses['where'] . $clauses['having'] . $clauses['groupby'] . $func . $column);
        $last_changed = wp_cache_get_last_changed($this->cache_group);
        $cache_key = $this->cache_group . ':aggregate:' . $query_key . ':' . $last_changed;
        $cached_result = wp_cache_get($cache_key, $this->cache_group);
        if (false !== $cached_result) {
            return $cached_result;
        }
        $query = "SELECT {$select} as result {$clauses['from']} {$clauses['join']} {$clauses['where']} {$clauses['groupby']} {$clauses['having']}";
        $result = $wpdb->get_var($query);
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Query prepared in prepare_query method.
        $final_result = null !== $result ? (float) $result : null;
        wp_cache_set($cache_key, $final_result, $this->cache_group);
        return $final_result;
    }
    /**
     * Get all built SQL clauses.
     *
     * @since 1.0.0
     * @return array<string, string> All built clauses.
     */
    public function clauses(): array
    {
        $this->prepare_query();
        return $this->clauses;
    }
    /**
     * Get the built SQL query.
     *
     * @since 1.0.0
     * @return string The SQL query string.
     */
    public function sql(): string
    {
        $this->get();
        return $this->request;
    }
    /**
     * Prepare the query.
     *
     * @since 1.0.0
     * @return self
     */
    protected function prepare_query(): self
    {
        global $wpdb;
        $qv =& $this->query_vars;
        $qv = wp_parse_args($qv, $this->model->get_query_vars());
        $clauses =& $this->clauses;
        $clauses = array_fill_keys(array_keys($clauses), '');
        $sql_vars =& $this->sql_vars;
        /**
         * Fires before the query is executed.
         *
         * @since 1.0.0
         *
         * @param static $query Query instance.
         *
         * @param array  $qv Query vars.
         */
        $qv = $this->model->apply_filters('query_args', $qv, $this);
        $hash = md5(maybe_serialize(array_merge($qv, $sql_vars)));
        if ($this->hash === $hash) {
            return $this;
        }
        $qv['select'] = wp_parse_list($qv['select']);
        $qv['count'] = filter_var($qv['count'], FILTER_VALIDATE_BOOLEAN);
        $qv['no_found_rows'] = filter_var($qv['no_found_rows'], FILTER_VALIDATE_BOOLEAN);
        $qv['with'] = array_unique(array_filter(wp_parse_list($qv['with'])));
        foreach ($this->columns as $column) {
            foreach ($this->modifiers as $modifier => $operator) {
                $arg_name = '' === $modifier ? $column : $column . '__' . $modifier;
                if (isset($qv[$arg_name]) && '' !== $qv[$arg_name]) {
                    $compare_value = $qv[$arg_name];
                    $this->where($column, '' === $modifier && is_array($compare_value) ? 'IN' : $operator, $compare_value);
                    unset($qv[$arg_name]);
                }
            }
        }
        if (!empty($qv['include'])) {
            $this->where($this->primary_key, 'IN', wp_parse_list($qv['include']));
        }
        if (!empty($qv['exclude'])) {
            $this->where($this->primary_key, 'NOT IN', wp_parse_list($qv['exclude']));
        }
        // Fields.
        $clauses['select'] = $qv['count'] ? 'COUNT(*)' : $this->parse_column('*');
        // From.
        $clauses['from'] = "FROM `{$wpdb->prefix}{$this->table}` AS `{$this->table}`";
        // Join.
        if (!empty($this->sql_vars['join'])) {
            foreach ($this->sql_vars['join'] as $join) {
                if (empty($join['table']) || empty($join['condition'])) {
                    continue;
                }
                $type = strtoupper($join['type'] ?? 'INNER');
                if (!in_array($type, array('INNER', 'LEFT', 'RIGHT', 'FULL'), true)) {
                    $type = 'INNER';
                }
                $table = $wpdb->prefix . $join['table'];
                $alias = esc_sql($join['table']);
                $alias = is_string($alias) ? $alias : '';
                $condition = $join['condition'];
                $clauses['join'] .= " {$type} JOIN `{$table}` AS `{$alias}` ON {$condition}";
            }
        }
        // Where.
        if (!empty($this->sql_vars['where'])) {
            $where_clause = $this->parse_where($this->sql_vars['where']);
            if (!empty($where_clause)) {
                $clauses['where'] .= ' AND ' . $where_clause;
            }
        }
        if (!empty($qv['search'])) {
            $searchable = is_array($qv['search_columns']) ? $qv['search_columns'] : array();
            $search_columns = array_intersect($searchable, $this->columns);
            $search = '%' . $wpdb->esc_like($qv['search']) . '%';
            $search_clauses = array();
            foreach ($search_columns as $search_column) {
                $search_clauses[] = $wpdb->prepare("{$this->parse_column($search_column)} LIKE %s", $search);
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Column name qualified via parse_column method.
            }
            // Search declared metadata keys.
            if ($this->meta_type) {
                $metadata_keys = array_keys($this->model->get_metadata());
                $search_meta_keys = array_intersect($searchable, $metadata_keys);
                $meta_table = $wpdb->prefix . $this->meta_type . 'meta';
                foreach ($search_meta_keys as $meta_key) {
                    $search_clauses[] = $wpdb->prepare(
                        "EXISTS (SELECT 1 FROM `{$meta_table}` WHERE `{$this->meta_type}_id` = {$this->parse_column($this->primary_key)} AND `meta_key` = %s AND `meta_value` LIKE %s)",
                        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table and column names derived from model properties.
                        $meta_key,
                        $search
                    );
                }
            }
            if (!empty($search_clauses)) {
                $clauses['where'] .= ' AND (' . implode(' OR ', $search_clauses) . ')';
            }
        }
        if ($this->meta_type) {
            $meta_query = new \WP_Meta_Query();
            $meta_query->parse_query_vars($qv);
            if (!empty($meta_query->queries) && $this->meta_type) {
                $meta_clauses = $meta_query->get_sql((string) $this->meta_type, $this->table, $this->primary_key, $this);
                if (is_array($meta_clauses)) {
                    $clauses['join'] .= $meta_clauses['join'];
                    $clauses['where'] .= $meta_clauses['where'];
                    if ($meta_query->has_or_relation()) {
                        $this->group_by($this->primary_key);
                    }
                }
            }
        }
        // Tax query.
        if (!empty($qv['tax_query'])) {
            $tax_query = new \WP_Tax_Query($qv['tax_query']);
            $tax_clauses = $tax_query->get_sql($this->table, $this->primary_key);
            $clauses['join'] .= ' ' . $tax_clauses['join'];
            $clauses['where'] .= ' ' . $tax_clauses['where'];
        }
        // Having.
        foreach ($this->sql_vars['having'] as $having) {
            if (!empty(trim($having))) {
                $clauses['having'] .= ' AND ' . $having;
            }
        }
        // Group By.
        if (!empty($this->sql_vars['groupby'])) {
            $this->sql_vars['groupby'] = array_map(array($this, 'parse_column'), wp_parse_list($this->sql_vars['groupby']));
            $clauses['groupby'] = empty($this->sql_vars['groupby']) ? '' : implode(',', $this->sql_vars['groupby']);
        }
        if (!empty($qv['orderby'])) {
            if (is_array($qv['orderby'])) {
                $orderby_clauses = array();
                foreach ($qv['orderby'] as $order_item) {
                    $column = in_array($order_item['column'], $this->columns, true) ? $order_item['column'] : $this->primary_key;
                    $direction = $this->parse_order($order_item['direction'] ?? '', 'ASC');
                    $orderby_clauses[] = "{$this->parse_column($column)} {$direction}";
                }
                $clauses['orderby'] = 'ORDER BY ' . implode(', ', $orderby_clauses);
            } else {
                $order = $this->parse_order($qv['order']);
                $orderby = in_array($qv['orderby'], $this->columns, true) ? $qv['orderby'] : $this->primary_key;
                $clauses['orderby'] = "ORDER BY {$this->parse_column($orderby)} {$order}";
            }
        } else {
            $order = $this->parse_order($qv['order']);
            $clauses['orderby'] = "ORDER BY {$this->parse_column($this->primary_key)} {$order}";
        }
        // Pagination.
        if (isset($qv['per_page']) && $qv['per_page'] > 0) {
            $qv['limit'] = (int) $qv['per_page'];
        }
        if (!empty($qv['paged']) && empty($qv['page'])) {
            $qv['page'] = $qv['paged'];
        }
        // If offset is explicitly set, it takes precedence over page calculation.
        if (!isset($qv['offset']) || 0 === $qv['offset']) {
            $page = empty($qv['page']) ? 1 : absint(trim($qv['page'], '/'));
            $page = max(1, $page);
            $qv['offset'] = absint(($page - 1) * $qv['limit']);
        }
        if ((int) $qv['limit'] > 0) {
            $clauses['limits'] = $wpdb->prepare('LIMIT %d OFFSET %d', (int) $qv['limit'], (int) $qv['offset']);
        }
        /**
         * Fires after the query is prepared.
         *
         * @since 1.0.0
         *
         * @param array $args The query arguments.
         * @param Query $query The query object.
         *
         * @param array $clauses The query clauses.
         */
        $clauses = $this->model->apply_filters('query_clauses', $clauses, $qv, $this);
        $clauses['where'] = preg_replace('/^ AND /', '', $clauses['where'], 1);
        $clauses['where'] = empty($clauses['where']) ? '' : 'WHERE ' . $clauses['where'];
        $clauses['groupby'] = empty($clauses['groupby']) ? '' : 'GROUP BY ' . $clauses['groupby'];
        $clauses['having'] = preg_replace('/^ AND /', '', $clauses['having'], 1);
        $clauses['having'] = empty($clauses['having']) ? '' : 'HAVING ' . $clauses['having'];
        $this->hash = $hash;
        return $this;
    }
    /**
     * Builds WHERE clause from query conditions (supports recursive nesting).
     *
     * @since 1.0.0
     *
     * @param array<int|string, mixed> $conditions Array of WHERE conditions.
     * @param int                      $depth      Current recursion depth.
     *
     * @return string The WHERE clause string.
     */
    protected function parse_where($conditions, $depth = 0): string
    {
        $where_parts = array();
        $group_boolean = isset($conditions['boolean']) ? strtoupper($conditions['boolean']) : null;
        foreach ($conditions as $key => $where) {
            if ('boolean' === $key) {
                continue;
            }
            if (!is_array($where)) {
                continue;
            }
            if (isset($where['column'])) {
                $where = wp_parse_args($where, array('column' => '', 'value' => '', 'operator' => '=', 'cast' => '', 'boolean' => 'AND'));
                $clause = $this->parse_where_clause($where['column'], $where['value'], $where['operator'], $where['cast']);
                if (!empty($clause)) {
                    $boolean = strtoupper($where['boolean']);
                    $boolean = 'OR' === $boolean ? 'OR' : 'AND';
                    $where_parts[] = array('clause' => $clause, 'boolean' => $boolean);
                }
            } else {
                $nested = $this->parse_where($where, $depth + 1);
                if (!empty($nested)) {
                    $where_parts[] = array('clause' => '(' . $nested . ')', 'boolean' => 'AND');
                }
            }
        }
        if (empty($where_parts)) {
            return '';
        }
        if (null !== $group_boolean) {
            $group_boolean = 'OR' === $group_boolean ? 'OR' : 'AND';
            $clauses = wp_list_pluck($where_parts, 'clause');
            return implode(' ' . $group_boolean . ' ', $clauses);
        }
        $sql = array();
        $prev_boolean = null;
        foreach ($where_parts as $part) {
            if (null === $prev_boolean) {
                $sql[] = $part['clause'];
            } else {
                $sql[] = $part['boolean'] . ' ' . $part['clause'];
            }
            $prev_boolean = $part['boolean'];
        }
        return implode(' ', $sql);
    }
    /**
     * Generates individual WHERE condition SQL.
     *
     * @since 1.0.0
     *
     * @param string $column Column name.
     * @param mixed  $value Value to compare.
     * @param string $operator Comparison operator.
     * @param string $cast Data type to cast the column.
     *
     * @return string
     */
    protected function parse_where_clause($column, $value, $operator = '=', $cast = ''): string
    {
        global $wpdb;
        if (empty($column)) {
            return '';
        }
        if (!str_contains($column, '.') && !in_array(strtolower($column), array_map('strtolower', $this->columns), true)) {
            return '';
        }
        $operator = $this->parse_operator($operator);
        $cast = empty($cast) ? 'CHAR' : strtoupper($cast);
        $table_column = $this->parse_column($column);
        $where = '';
        switch ($operator) {
            case 'IN':
            case 'NOT IN':
                $value = is_array($value) ? $value : (array) preg_split('/[,\s]+/', (string) $value);
                // An empty list (e.g. an unselected UI filter) is ignored rather than constraining the query.
                if (array() === $value) {
                    return '';
                }
                $where = $wpdb->prepare('(' . substr(str_repeat(',%s', count($value)), 1) . ')', $value);
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
                break;
            case 'BETWEEN':
            case 'NOT BETWEEN':
                $value = is_array($value) ? $value : (array) preg_split('/[,\s]+/', (string) $value);
                $value = count($value) < 2 ? array($value[0], $value[0]) : $value;
                $where = $wpdb->prepare('%s AND %s', min($value), max($value));
                break;
            case 'LIKE':
            case 'NOT LIKE':
                $where = $wpdb->prepare('%s', '%' . $wpdb->esc_like((string) $value) . '%');
                break;
            case 'STARTS WITH':
                $operator = 'LIKE';
                $where = $wpdb->prepare('%s', $wpdb->esc_like((string) $value) . '%');
                break;
            case 'ENDS WITH':
                $operator = 'LIKE';
                $where = $wpdb->prepare('%s', '%' . $wpdb->esc_like((string) $value));
                break;
            case 'FIND IN SET':
                if (is_array($value)) {
                    $operator = 'REGEXP';
                    $where = $wpdb->prepare('%s', implode('|', $value));
                } else {
                    $prepared_where = $wpdb->prepare("FIND_IN_SET( %s, {$table_column} ) > 0", $value);
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table column is sanitized via parse_column method.
                    return "({$prepared_where})";
                }
                break;
            case 'NOT FIND IN SET':
                if (is_array($value)) {
                    $operator = 'NOT REGEXP';
                    $where = $wpdb->prepare('%s', implode('|', $value));
                } else {
                    $prepared_where = $wpdb->prepare("FIND_IN_SET( %s, {$table_column} ) = 0", $value);
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table column is sanitized via parse_column method.
                    return "({$prepared_where})";
                }
                break;
            case 'IS NULL':
            case 'IS NOT NULL':
                return "({$table_column} {$operator})";
            case 'IS TRUE':
                return "({$table_column} = 1)";
            case 'IS FALSE':
                return "({$table_column} = 0)";
            case 'REGEXP':
            case 'NOT REGEXP':
                $where = $wpdb->prepare('%s', $value);
                break;
            default:
                if (is_null($value) && in_array($operator, array('=', '!=', '<>'), true)) {
                    return '=' === $operator ? "({$table_column} IS NULL)" : "({$table_column} IS NOT NULL)";
                }
                if (is_numeric($value)) {
                    $where = is_float($value + 0) ? $wpdb->prepare('%f', $value) : $wpdb->prepare('%d', $value);
                } else {
                    $where = $wpdb->prepare('%s', $value);
                }
                break;
        }
        if ('' === $where) {
            return '';
        }
        $cast_pattern = '/^(?:BINARY|SIGNED|UNSIGNED|NUMERIC(?:\(\d+(?:,\s?\d+)?\))?|DECIMAL(?:\(\d+(?:,\s?\d+)?\))?|YEAR|DATE|DATETIME|TIME|TIMESTAMP|DATETIME(?:\(\d+\))?)$/D';
        if (!preg_match($cast_pattern, $cast)) {
            return "({$table_column} {$operator} {$where})";
        }
        $cast_column = 'NUMERIC' === $cast ? "CAST( {$table_column} AS SIGNED )" : "CAST( {$table_column} AS {$cast} )";
        return "({$cast_column} {$operator} {$where})";
    }
    /**
     * Parse an order direction into ASC or DESC.
     *
     * @since 1.0.0
     *
     * @param mixed  $direction Requested direction.
     * @param string $fallback  Direction used when the requested one is invalid.
     *
     * @return string Sanitized direction.
     */
    protected function parse_order($direction, string $fallback = 'DESC'): string
    {
        $direction = strtoupper((string) $direction);
        return in_array($direction, array('ASC', 'DESC'), true) ? $direction : $fallback;
    }
    /**
     * Qualify a column name with the table name.
     *
     * @since 1.0.0
     *
     * @param string      $column Column name.
     * @param string|null $table Optional table name. Defaults to model's table.
     *
     * @return string Qualified column name.
     */
    protected function parse_column($column, $table = null): string
    {
        if (empty($table)) {
            $table = $this->table;
        }
        $table = $this->parse_identifier($table);
        if ('*' === $column) {
            return "`{$table}`.*";
        }
        if (str_contains($column, '.')) {
            $parts = explode('.', $column, 2);
            $prefix = $this->parse_identifier($parts[0]);
            $name = $this->parse_identifier($parts[1]);
            return "`{$prefix}`.`{$name}`";
        }
        $column = $this->parse_identifier($column);
        return "`{$table}`.`{$column}`";
    }
    /**
     * Reduce a value to a bare SQL identifier.
     *
     * @since 1.0.0
     *
     * @param string $identifier Raw identifier.
     *
     * @return string Sanitized identifier.
     */
    protected function parse_identifier(string $identifier): string
    {
        return (string) preg_replace('/[^A-Za-z0-9_]/', '', $identifier);
    }
    /**
     * Reduce an operator to a known SQL operator.
     *
     * @since 1.0.0
     *
     * @param string $operator Requested operator.
     *
     * @return string Known SQL operator.
     */
    protected function parse_operator(string $operator): string
    {
        $operator = strtoupper($operator);
        $allowed = array_merge(array_values($this->modifiers), array('<>'));
        return in_array($operator, $allowed, true) ? $operator : '=';
    }
    /**
     * Normalize a filter value to what the database stores.
     *
     * @since 1.0.0
     *
     * @param mixed $value Raw filter value.
     *
     * @return mixed Normalized value.
     */
    protected function parse_filter_value($value)
    {
        if (is_array($value)) {
            return array_map(array($this, 'parse_filter_value'), $value);
        }
        // Only an RFC 3339 string is normalized; a stored value is left as is.
        if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}([T ]\d{2}:\d{2}(:\d{2})?)?([Zz]|[+-]\d{2}:?\d{2})?$/', $value)) {
            $timestamp = strtotime($value);
            if (false !== $timestamp) {
                return gmdate('Y-m-d H:i:s', $timestamp);
            }
        }
        return $value;
    }
    /**
     * Update caches.
     *
     * @since 1.0.0
     *
     * @param array<int, object>|object $items             List of items to update.
     * @param bool                      $update_meta_cache Optional. Whether to update the meta cache. Default true.
     *
     * @return void
     */
    protected function prime_cache($items, $update_meta_cache = true): void
    {
        if (empty($items)) {
            return;
        }
        $data = array();
        $items = is_array($items) ? $items : array($items);
        foreach ($items as $item) {
            $id = $item->{$this->primary_key};
            $data[$id] = $item;
        }
        /**
         * Fires before the cache is updated.
         *
         * @since 1.0.0
         *
         * @param object[] $items List of items.
         */
        $this->model->do_action('pre_prime_cache', $items);
        wp_cache_add_multiple($data, $this->cache_group);
        if ($update_meta_cache && $this->meta_type) {
            $ids = wp_list_pluck($items, $this->primary_key);
            update_meta_cache($this->meta_type, $ids);
        }
        /**
         * Fires after the item cache has been primed.
         *
         * @since 1.0.0
         *
         * @param object[] $items List of items.
         */
        $this->model->do_action('cache_primed', $items);
    }
}