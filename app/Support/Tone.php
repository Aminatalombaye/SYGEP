<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

class Tone
{
    private const RULES = [
        'good'     => ['termin', 'clôtur', 'clotur', 'achev', 'fini', 'livr', 'résolu', 'resolu', 'traité', 'traite', 'réalis', 'realis', 'closed', 'done', 'complet', 'disponible', 'available', 'bon état', 'fonctionnel', 'opérationnel', 'operationnel', 'valid'],
        'critical' => ['retard', 'urgent', 'panne', 'broken', 'bloqu', 'annul', 'rejet', 'hors service', 'critique', 'dégrad', 'degrad', 'repair', 'réparation', 'suspend'],
        'warning'  => ['attente', 'pending', 'planif', 'prévu', 'prevu', 'à faire', 'a faire', 'soumis', 'nouveau', 'nouvelle', 'open', 'ouvert'],
        'info'     => ['cours', 'progress', 'assigned', 'affect', 'démarr', 'demarr', 'construction', 'réhabilit', 'rehabilit'],
    ];

    public const DONE_KEYWORDS = ['termin', 'clôtur', 'clotur', 'achev', 'fini', 'livr', 'résolu', 'resolu', 'traité', 'traite', 'réalis', 'realis', 'closed', 'done', 'complet'];

    public static function of(?string $text): string
    {
        $value = mb_strtolower(trim((string) $text));

        if ($value === '') {
            return 'neutral';
        }

        foreach (self::RULES as $tone => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($value, $keyword)) {
                    return $tone;
                }
            }
        }

        return 'neutral';
    }

    public static function isDone(?string $text): bool
    {
        $value = mb_strtolower((string) $text);

        foreach (self::DONE_KEYWORDS as $keyword) {
            if (str_contains($value, $keyword)) {
                return true;
            }
        }

        return false;
    }

    public static function translate(?string $name): ?string
    {
        if ($name === null) {
            return null;
        }

        $tasks = ['open' => 'Ouvert', 'in progress' => 'En cours', 'closed' => 'Clôturé'];

        return $tasks[mb_strtolower($name)] ?? \App\Models\AssetStatus::label($name);
    }

    public static function status(?string $name): HtmlString
    {
        return self::pill($name, self::translate($name));
    }

    public static function pill(?string $text, ?string $label = null): HtmlString
    {
        if ($text === null || trim($text) === '') {
            return new HtmlString('<span class="muted">—</span>');
        }

        return new HtmlString(sprintf(
            '<span class="pill pill-%s">%s</span>',
            self::of($text),
            e($label ?? $text)
        ));
    }
}
