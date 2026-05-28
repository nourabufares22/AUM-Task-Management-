<?php
function status_badge(string $status): string {
    $map = [
        'Pending'     => 'warning text-dark',
        'In Progress' => 'info text-dark',
        'Completed'   => 'success',
        'Rejected'    => 'danger',
    ];
    $cls = $map[$status] ?? 'secondary';
    return '<span class="badge bg-' . $cls . '">' . htmlspecialchars($status) . '</span>';
}

function priority_badge(string $priority): string {
    $map = [
        'Low'    => 'success',
        'Medium' => 'warning text-dark',
        'High'   => 'danger',
    ];
    $cls = $map[$priority] ?? 'secondary';
    return '<span class="badge bg-' . $cls . '">' . htmlspecialchars($priority) . '</span>';
}

function building_name_from_file(string $filename): string {
    $name = pathinfo($filename, PATHINFO_FILENAME);
    $name = str_replace(['_', '-'], ' ', $name);
    return ucwords(strtolower($name));
}
