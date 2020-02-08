<?php

namespace App\DataFixtures;

use App\Entity\Tache;
use App\Entity\EtatTache;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class RoadMapFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = \Faker\Factory::create('fr_FR');

        $etat_tache = new EtatTache();
        $etat_tache->setLibelle('En attente');
        $etat_tache->setCodeBootsrap('secondary');
        $etat_tache->setNom('waiting');
        $manager->persist($etat_tache);

        for ($i = 0; $i <= mt_rand(2, 5); $i++) {
            $tache = new Tache();
            $tache->setNom($faker->sentence());
            $tache->setDate($faker->dateTimeBetween('-50 months'));
            $tache->setContenu(join($faker->sentences(5), '<br/>'));
            $tache->setEtat($etat_tache);

            $manager->persist($tache);
        }

        $etat_tache = new EtatTache();
        $etat_tache->setLibelle('En cours');
        $etat_tache->setCodeBootsrap('primary');
        $etat_tache->setNom('in_progress');
        $manager->persist($etat_tache);

        for ($i = 0; $i <= mt_rand(2, 5); $i++) {
            $tache = new Tache();
            $tache->setNom($faker->sentence());
            $tache->setDate($faker->dateTimeBetween('-50 months'));
            $tache->setContenu(join($faker->sentences(5), '<br/>'));
            $tache->setEtat($etat_tache);

            $manager->persist($tache);
        }

        $etat_tache = new EtatTache();
        $etat_tache->setLibelle('Complété');
        $etat_tache->setCodeBootsrap('dark');
        $etat_tache->setNom('completed');
        $manager->persist($etat_tache);

        for ($i = 0; $i <= mt_rand(2, 5); $i++) {
            $tache = new Tache();
            $tache->setNom($faker->sentence());
            $tache->setDate($faker->dateTimeBetween('-50 months'));
            $tache->setContenu(join($faker->sentences(5), '<br/>'));
            $tache->setEtat($etat_tache);

            $manager->persist($tache);
        }

        $manager->flush();
    }
}
