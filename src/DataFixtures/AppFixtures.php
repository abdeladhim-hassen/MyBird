<?php

namespace App\DataFixtures;

use App\Entity\Question;
use App\Entity\Subject;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('en_US');

        for ($i=1; $i<=5; $i++){
            $Subject = new Subject();
            $Subject->setTitle($faker->word);
            $manager->persist($Subject);
        }
        $manager->flush();
    }
}
