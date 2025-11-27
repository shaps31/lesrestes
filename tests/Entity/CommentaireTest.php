<?php

namespace App\Tests\Entity;

use App\Entity\Commentaire;
use App\Entity\User;
use App\Entity\Recette;
use PHPUnit\Framework\TestCase;

class CommentaireTest extends TestCase
{
    public function testGettersSetters(): void
    {
        $commentaire = new Commentaire();
        
        $commentaire->setContenu('Tres bonne recette !');
        $this->assertEquals('Tres bonne recette !', $commentaire->getContenu());
        
        $commentaire->setNote(5);
        $this->assertEquals(5, $commentaire->getNote());
    }
    
    public function testUserRelation(): void
    {
        $commentaire = new Commentaire();
        $user = new User();
        $user->setEmail('user@example.com');
        
        $commentaire->setUser($user);
        $this->assertEquals($user, $commentaire->getUser());
    }
    
    public function testRecetteRelation(): void
    {
        $commentaire = new Commentaire();
        $recette = new Recette();
        $recette->setNom('Test Recette');
        
        $commentaire->setRecette($recette);
        $this->assertEquals($recette, $commentaire->getRecette());
    }
    
    public function testDateCreation(): void
    {
        $commentaire = new Commentaire();
        
        $this->assertInstanceOf(\DateTimeImmutable::class, $commentaire->getDateCreation());
    }
}
