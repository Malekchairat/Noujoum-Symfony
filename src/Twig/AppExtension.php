<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('formatSeconds', [$this, 'formatSeconds']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('formatSeconds', [$this, 'formatSeconds']),
        ];
    }

    public function formatSeconds(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;

        return sprintf('%02dh %02dm %02ds', $hours, $minutes, $seconds);
    }
}