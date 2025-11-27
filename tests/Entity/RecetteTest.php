<?php

namespace App\Tests\Entity;

use App\Entity\Recette;
use App\Entity\User;
use App\Entity\Commentaire;
use PHPUnit\Framework\TestCase;

class RecetteTest extends TestCase
{
    public function testGettersSetters(): void
    {
        $recette = new Recette();
        
        $recette->setNom('Tarte aux pommes');
        $this->assertEquals('Tarte aux pommes', $recette->getNom());
        
        $recette->setDescription('Une delicieuse tarte');
        $this->assertEquals('Une delicieuse tarte', $recette->getDescription());
        
        $recette->setTempsCuisson(45);
        $this->assertEquals(45, $recette->getTempsCuisson());
        
        $recette->setNombrePersonnes(6);
        $this->assertEquals(6, $recette->getNombrePersonnes());
        
        $recette->setDifficulte(2);
        $this->assertEquals(2, $recette->getDifficulte());
    }
    
    public function testUserRelation(): void
    {
        $recette = new Recette();
        $user = new User();
        $user->setEmail('chef@example.com');
        
        $recette->setUser($user);
        $this->assertEquals($user, $recette->getUser());
    }
    
    public function testDateCreation(): void
    {
        $recette = new Recette();
        
        $this->assertInstanceOf(\DateTimeImmutable::class, $recette->getDateCreation());
    }
    
    public function testMoyenneNotes(): void
    {
        $recette = new Recette();
        
        // Sans commentaires
        $this->assertEquals(0, $recette->getMoyenneNotes());
    }
    
    public function testVues(): void
    {
        $recette = new Recette();
        
        $recette->setVue(100);
        $this->assertEquals(100, $recette->getVue());
    }
}
