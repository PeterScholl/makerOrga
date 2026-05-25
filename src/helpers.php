<?php

// Gibt einen farbigen Bootstrap-Badge für einen Auftragsstatus zurück
function statusBadge(string $status): string
{
    $map = [
        'open'        => ['bg-warning text-dark', 'Offen'],
        'in_progress' => ['bg-primary',           'In Bearbeitung'],
        'done'        => ['bg-success',           'Abgeschlossen'],
        'closed'      => ['bg-secondary',         'Archiviert'],
    ];
    [$class, $label] = $map[$status] ?? ['bg-light text-dark', $status];
    return "<span class=\"badge $class\">$label</span>";
}

// Gibt einen farbigen Bootstrap-Badge für eine Priorität zurück
function priorityBadge(string $priority): string
{
    $map = [
        'low'    => ['bg-light text-dark', 'Niedrig'],
        'normal' => ['bg-info text-dark',  'Normal'],
        'high'   => ['bg-danger',          'Hoch'],
    ];
    [$class, $label] = $map[$priority] ?? ['bg-light text-dark', $priority];
    return "<span class=\"badge $class\">$label</span>";
}

// Formatiert ein Datenbankdatum (Y-m-d oder Y-m-d H:i:s) leserlich
function dateFormat(?string $date, bool $withTime = false): string
{
    if (!$date) {
        return '—';
    }
    $format = $withTime ? 'd.m.Y H:i' : 'd.m.Y';
    return date($format, strtotime($date));
}

// Gibt einen farbigen Bootstrap-Badge für eine Benutzerrolle zurück
function roleBadge(string $role): string
{
    $map = [
        'admin'       => ['bg-danger',  'Admin'],
        'coordinator' => ['bg-warning text-dark', 'Koordinator'],
        'member'      => ['bg-secondary', 'Mitarbeiter'],
    ];
    [$class, $label] = $map[$role] ?? ['bg-light text-dark', $role];
    return "<span class=\"badge $class\">$label</span>";
}

// Gibt einen HTML-String sicher aus (verhindert XSS)
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
