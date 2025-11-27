<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testGettersSetters(): void
    {
        $user = new User();
        
        $user->setEmail('test@example.com');
        $this->assertEquals('test@example.com', $user->getEmail());
        
        $user->setNom('Dupont');
        $this->assertEquals('Dupont', $user->getNom());
        
        $user->setPrenom('Jean');
        $this->assertEquals('Jean', $user->getPrenom());
        
        $user->setPassword('hashedpassword');
        $this->assertEquals('hashedpassword', $user->getPassword());
    }
    
    public function testRoles(): void
    {
        $user = new User();
        
        // Par defaut ROLE_USER
        $this->assertContains('ROLE_USER', $user->getRoles());
        
        // Ajout ROLE_ADMIN
        $user->setRoles(['ROLE_ADMIN']);
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
        $this->assertContains('ROLE_USER', $user->getRoles());
    }
    
    public function testUserIdentifier(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        
        $this->assertEquals('test@example.com', $user->getUserIdentifier());
    }
}
