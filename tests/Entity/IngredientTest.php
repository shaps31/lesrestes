<?php

namespace App\Tests\Entity;

use App\Entity\Ingredient;
use PHPUnit\Framework\TestCase;

class IngredientTest extends TestCase
{
    public function testGettersSetters(): void
    {
        $ingredient = new Ingredient();
        
        $ingredient->setNom('Tomate');
        $this->assertEquals('Tomate', $ingredient->getNom());
        
        $ingredient->setUnite('g');
        $this->assertEquals('g', $ingredient->getUnite());
    }
    
    public function testRecetteIngredientsCollection(): void
    {
        $ingredient = new Ingredient();
        
        $this->assertCount(0, $ingredient->getRecetteIngredients());
    }
}
