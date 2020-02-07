<?php

namespace App\DataFixtures;

use App\Entity\Partie;
use App\Entity\Document;
use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class DocumentFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = \Faker\Factory::create('fr_FR');

        for ($v = 1; $v <= 5; $v++) {
            $utilisateur = new Utilisateur();
            $utilisateur->setUsername("tatsuki$v");
            $password = $this->encoder->encodePassword(
                $utilisateur,
                '123456789'
            );
            $utilisateur->setPassword($password);
            $utilisateur->setEmail($faker->email());
            $utilisateur->setFirstName($faker->firstName());
            $utilisateur->setLastName($faker->lastName());

            $manager->persist($utilisateur);

            for ($i = 1; $i <= 50; $i++) {
                $document = new Document();
                $document
                    ->setTitre("Document n°$i")
                    ->setAuteur($utilisateur)
                    ->setDescription($faker->sentence())
                    ->setCategorie('Rapport')
                    ->setCreatedAt($faker->dateTimeBetween('-100 months'))
                    ->setUpdatedAt($faker->dateTimeBetween('-100 months'));

                $manager->persist($document);

                for ($j = 1; $j <= mt_rand(1, 4); $j++) {
                    $partie = new Partie();
                    $partie
                        ->setDocument($document)
                        ->setNumero("$j")
                        ->setNom("$j -")
                        ->setContenu(join($faker->paragraphs(mt_rand(1, 10))))
                        ->setCreatedAt($faker->dateTimeBetween('-100 months'))
                        ->setUpdatedAt($faker->dateTimeBetween('-100 months'));

                    $manager->persist($partie);

                    for ($k = 1; $k <= mt_rand(1, 3); $k++) {
                        $ss_partie = new Partie();
                        $ss_partie
                            ->setDocument($document)
                            ->setNumero("$k")
                            ->setNom("$j.$k -")
                            ->setContenu(
                                join($faker->paragraphs(mt_rand(1, 10)))
                            )
                            ->setCreatedAt(
                                $faker->dateTimeBetween('-100 months')
                            )
                            ->setUpdatedAt(
                                $faker->dateTimeBetween('-100 months')
                            )
                            ->setPartieParent($partie);

                        $manager->persist($ss_partie);

                        for ($l = 1; $l <= mt_rand(1, 3); $l++) {
                            $ss_ss_partie = new Partie();
                            $ss_ss_partie
                                ->setDocument($document)
                                ->setNumero("$l")
                                ->setNom("$j.$k.$l -")
                                ->setContenu(
                                    join($faker->paragraphs(mt_rand(1, 10)))
                                )
                                ->setCreatedAt(
                                    $faker->dateTimeBetween('-100 months')
                                )
                                ->setUpdatedAt(
                                    $faker->dateTimeBetween('-100 months')
                                )
                                ->setPartieParent($ss_partie);

                            $manager->persist($ss_ss_partie);
                        }
                    }
                }
            }
        }
        $manager->flush();
    }
}
