<?php

if (!defined('SW_HERO_DIAGNOSTICS_SUPPORTED_SCHEMAS')) {
    define('SW_HERO_DIAGNOSTICS_SUPPORTED_SCHEMAS', [
        'active_layers.hero_candidates_stage2d.v1',
    ]);
}

function sw_hero_diagnostics_json_path(): string
{
    return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'tools-dev' . DIRECTORY_SEPARATOR . 'image-analysis' . DIRECTORY_SEPARATOR . 'out' . DIRECTORY_SEPARATOR . 'hero_candidates_stage1.json';
}

function sw_load_hero_diagnostics_payload(): array
{
    $path = sw_hero_diagnostics_json_path();

    if (!is_file($path)) {
        return sw_hero_diagnostics_unavailable('missing_file', 'Diagnostics unavailable. Manual selection remains available.');
    }

    $json = @file_get_contents($path);
    if ($json === false) {
        return sw_hero_diagnostics_unavailable('invalid_json', 'Diagnostics unavailable. Manual selection remains available.');
    }

    $payload = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($payload)) {
        return sw_hero_diagnostics_unavailable('invalid_json', 'Diagnostics unavailable. Manual selection remains available.');
    }

    $schema = $payload['schema'] ?? null;
    if (!is_string($schema) || $schema === '') {
        return sw_hero_diagnostics_unavailable('invalid_payload_shape', 'Diagnostics unavailable. Manual selection remains available.');
    }

    if (!in_array($schema, SW_HERO_DIAGNOSTICS_SUPPORTED_SCHEMAS, true)) {
        return [
            'available' => false,
            'status' => 'unsupported_schema',
            'message' => 'Diagnostics unavailable. Manual selection remains available.',
            'schema' => $schema,
            'payload' => null,
        ];
    }

    if (!isset($payload['records']) || !is_array($payload['records'])) {
        return [
            'available' => false,
            'status' => 'invalid_payload_shape',
            'message' => 'Diagnostics unavailable. Manual selection remains available.',
            'schema' => $schema,
            'payload' => null,
        ];
    }

    return [
        'available' => true,
        'status' => 'ready',
        'message' => '',
        'schema' => $schema,
        'payload' => $payload,
    ];
}

function sw_normalize_hero_diagnostic_path(string $path): string
{
    $normalized = trim(str_replace('\\', '/', $path));

    $queryPos = strpos($normalized, '?');
    if ($queryPos !== false) {
        $normalized = substr($normalized, 0, $queryPos);
    }

    $fragmentPos = strpos($normalized, '#');
    if ($fragmentPos !== false) {
        $normalized = substr($normalized, 0, $fragmentPos);
    }

    if (preg_match('~^[a-z][a-z0-9+.-]*://~i', $normalized)) {
        $urlPath = parse_url($normalized, PHP_URL_PATH);
        $normalized = is_string($urlPath) ? $urlPath : '';
    }

    $imagesPos = stripos($normalized, 'images/');
    if ($imagesPos !== false) {
        $normalized = substr($normalized, $imagesPos);
    }

    return ltrim($normalized, '/');
}

function sw_index_hero_diagnostics_by_image_path(array $payload): array
{
    $records = [];
    $duplicates = [];

    foreach (($payload['records'] ?? []) as $record) {
        if (!is_array($record) || !isset($record['image_path']) || !is_string($record['image_path'])) {
            continue;
        }

        $path = sw_normalize_hero_diagnostic_path($record['image_path']);
        if ($path === '') {
            continue;
        }

        if (array_key_exists($path, $records)) {
            $duplicates[] = $path;
            continue;
        }

        $records[$path] = $record;
    }

    return [
        'records' => $records,
        'duplicates' => array_values(array_unique($duplicates)),
    ];
}

function sw_get_hero_diagnostic_for_image(string $imagePath): array
{
    $payloadResult = sw_load_hero_diagnostics_payload();

    if (!$payloadResult['available']) {
        return sw_hero_diagnostics_unavailable_record(
            $payloadResult['status'],
            $payloadResult['message'],
            $payloadResult['schema']
        );
    }

    $index = sw_index_hero_diagnostics_by_image_path($payloadResult['payload']);
    $normalizedPath = sw_normalize_hero_diagnostic_path($imagePath);

    if ($normalizedPath === '' || !isset($index['records'][$normalizedPath])) {
        return sw_hero_diagnostics_unavailable_record('no_record_for_image', 'Diagnostics unavailable. Manual selection remains available.', $payloadResult['schema']);
    }

    return sw_sanitize_hero_diagnostic_record($index['records'][$normalizedPath]);
}

function sw_sanitize_hero_diagnostic_record(array $record): array
{
    $classification = is_array($record['classification_diagnostics'] ?? null) ? $record['classification_diagnostics'] : [];
    $roiDiagnostics = is_array($record['roi_diagnostics'] ?? null) ? $record['roi_diagnostics'] : [];
    $scores = is_array($record['scores'] ?? null) ? $record['scores'] : [];
    $warnings = sw_hero_diagnostics_array_values($record['warnings'] ?? []);
    $categoryWarnings = sw_hero_diagnostics_array_values($record['category_specific_warnings'] ?? []);

    return [
        'available' => true,
        'status' => 'ready',
        'schema' => SW_HERO_DIAGNOSTICS_SUPPORTED_SCHEMAS[0],
        'product_type' => sw_hero_diagnostics_string_or_null($record['product_type'] ?? null),
        'inferred_roi_type' => sw_hero_diagnostics_string_or_null($record['inferred_roi_type'] ?? null),
        'path_classification' => [
            'confidence' => sw_hero_diagnostics_string_or_null($classification['path_classification_confidence'] ?? null),
            'reason' => sw_hero_diagnostics_string_or_null($classification['path_classification_reason'] ?? null),
        ],
        'roi' => [
            'specificity' => sw_hero_diagnostics_string_or_null($roiDiagnostics['roi_specificity'] ?? null),
            'confidence' => sw_hero_diagnostics_string_or_null($roiDiagnostics['roi_confidence'] ?? null),
            'is_body_region_specific' => (bool)($roiDiagnostics['roi_is_body_region_specific'] ?? false),
            'is_garment_specific' => (bool)($roiDiagnostics['roi_is_garment_specific'] ?? false),
        ],
        'review' => [
            'needs_manual_review' => (bool)($record['needs_manual_review'] ?? false),
            'warnings' => $warnings,
            'category_specific_warnings' => $categoryWarnings,
            'warning_count' => count($warnings),
            'category_warning_count' => count($categoryWarnings),
        ],
        'diagnostic_vocabulary' => sw_hero_diagnostics_assoc_or_empty($record['diagnostic_vocabulary'] ?? []),
        'score' => [
            'final_advisory_score' => is_numeric($scores['final_advisory_score'] ?? null) ? (float)$scores['final_advisory_score'] : null,
            'score_scope' => sw_hero_diagnostics_string_or_null($record['score_scope'] ?? null) ?: 'diagnostic_within_category_not_global_rank',
            'display_score' => false,
        ],
    ];
}

function sw_get_hero_diagnostics_summary(): array
{
    $payloadResult = sw_load_hero_diagnostics_payload();
    $path = sw_hero_diagnostics_json_path();
    $fileModified = is_file($path) ? @filemtime($path) : false;
    $fileModifiedTime = $fileModified ? date('c', $fileModified) : null;

    if (!$payloadResult['available']) {
        return [
            'available' => false,
            'status' => $payloadResult['status'],
            'message' => $payloadResult['message'],
            'schema' => $payloadResult['schema'],
            'image_count' => null,
            'run_summary' => null,
            'file_modified_time' => $fileModifiedTime,
        ];
    }

    $payload = $payloadResult['payload'];
    $index = sw_index_hero_diagnostics_by_image_path($payload);
    $runSummary = is_array($payload['run_summary'] ?? null) ? $payload['run_summary'] : null;

    if ($runSummary !== null && !empty($index['duplicates'])) {
        $runSummary['duplicate_record_warning'] = $index['duplicates'];
    }

    return [
        'available' => true,
        'status' => !empty($index['duplicates']) ? 'duplicate_record_warning' : 'ready',
        'message' => !empty($index['duplicates']) ? 'Diagnostics loaded with duplicate image_path records. Manual selection remains available.' : '',
        'schema' => $payloadResult['schema'],
        'image_count' => isset($payload['image_count']) && is_numeric($payload['image_count']) ? (int)$payload['image_count'] : count($index['records']),
        'run_summary' => $runSummary,
        'file_modified_time' => $fileModifiedTime,
    ];
}

function sw_hero_diagnostics_unavailable(string $status, string $message, ?string $schema = null): array
{
    return [
        'available' => false,
        'status' => $status,
        'message' => $message,
        'schema' => $schema,
        'payload' => null,
    ];
}

function sw_hero_diagnostics_unavailable_record(string $status, string $message, ?string $schema = null): array
{
    return [
        'available' => false,
        'status' => $status,
        'message' => $message,
        'schema' => $schema,
    ];
}

function sw_hero_diagnostics_string_or_null($value): ?string
{
    return is_string($value) && $value !== '' ? $value : null;
}

function sw_hero_diagnostics_array_values($value): array
{
    if (!is_array($value)) {
        return [];
    }

    return array_values(array_filter($value, static function ($item): bool {
        return is_string($item) || is_numeric($item) || is_bool($item);
    }));
}

function sw_hero_diagnostics_assoc_or_empty($value): array
{
    return is_array($value) ? $value : [];
}
