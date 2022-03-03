<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create();
        $users = [];


        for ($i = 0; $i < 2; $i++) {
            $user = new User();
            $user->setUsername($faker->name);
            $user->setEmail($faker->email);
            $user->setPassword($faker->password());
            // $user->setCreatedAt(new \DateTime());
            $manager->persist($user);
            $users[] = $user;
        }
        // $product = new Product();
        // $manager->persist($product);
        for ($i = 0; $i < 20; $i++) {
            $articles = new Article();
            $articles->setPublicationDate(new \DateTime());
            $articles->setLastUpdateDate(new \DateTime());
            $articles->setIsPublished(true);
            $articles->setPicture("https://picsum.photos/640/360?random=" . mt_rand(1, 55000));
            $articles->setTitle('Title-' . $i);
            $manager->persist($articles);
        }

        for ($i = 0; $i < 10; $i++) {
            $categories = new Category();
            $categories->setLabel('Categories-' . $i);
            $manager->persist($categories);
        }


        $manager->flush();
    }
}
