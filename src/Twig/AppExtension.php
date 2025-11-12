<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('stars', [$this, 'renderStars'], ['is_safe' => ['html']]),
        ];
    }

    public function renderStars(float $rating): string
    {
        $fullStars = floor($rating);
        $hasHalfStar = ($rating - $fullStars) >= 0.5;
        $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);

        $html = '';
        
        // Étoiles pleines
        for ($i = 0; $i < $fullStars; $i++) {
            $html .= '<i class="bi bi-star-fill text-warning"></i>';
        }
        
        // Demi-étoile
        if ($hasHalfStar) {
            $html .= '<i class="bi bi-star-half text-warning"></i>';
        }
        
        // Étoiles vides
        for ($i = 0; $i < $emptyStars; $i++) {
            $html .= '<i class="bi bi-star text-muted"></i>';
        }

        return $html;
    }
}