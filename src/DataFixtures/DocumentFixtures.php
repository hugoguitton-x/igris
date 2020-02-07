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
        $utilisateur = new Utilisateur();
        $utilisateur->setUsername('tatsuki');

        $password = $this->encoder->encodePassword($utilisateur, '123456789');
        $utilisateur->setPassword($password);
        $utilisateur->setEmail('tatsuki@projectdoc.com');
        $utilisateur->setFirstName('Tatsuki');
        $utilisateur->setLastName('Aisu');
        $manager->persist($utilisateur);
        for ($i = 1; $i <= 50; $i++) {
            $document = new Document();
            $document
                ->setTitre("Document n°$i")
                ->setAuteur($utilisateur)
                ->setDescription("Je suis la description du document n°$i")
                ->setCategorie('Rapport')
                ->setCreatedAt(new \DateTime())
                ->setUpdatedAt(new \DateTime());

            $manager->persist($document);

            for ($j = 1; $j <= mt_rand(1, 4); $j++) {
                $partie = new Partie();
                $partie
                    ->setDocument($document)
                    ->setNumero("$j")
                    ->setNom("$j -")
                    ->setContenu(
                        'Je suis du contenu au hasard et pas intéressant'
                    )
                    ->setCreatedAt(new \DateTime())
                    ->setUpdatedAt(new \DateTime());

                $manager->persist($partie);

                for ($k = 1; $k <= mt_rand(1, 3); $k++) {
                    $ss_partie = new Partie();
                    $ss_partie
                        ->setDocument($document)
                        ->setNumero("$k")
                        ->setNom("$j.$k -")
                        ->setContenu(
                            'Je suis du contenu au hasard et pas intéressant'
                        )
                        ->setCreatedAt(new \DateTime())
                        ->setUpdatedAt(new \DateTime())
                        ->setPartieParent($partie);

                    $manager->persist($ss_partie);

                    for ($l = 1; $l <= mt_rand(1, 3); $l++) {
                        $ss_ss_partie = new Partie();
                        $ss_ss_partie
                            ->setDocument($document)
                            ->setNumero("$l")
                            ->setNom("$j.$k.$l -")
                            ->setContenu(
                                'Je suis du contenu au hasard et pas intéressant'
                            )
                            ->setCreatedAt(new \DateTime())
                            ->setUpdatedAt(new \DateTime())
                            ->setPartieParent($ss_partie);

                        $manager->persist($ss_ss_partie);
                    }
                }
            }
        }

        $manager->flush();
    }
}
