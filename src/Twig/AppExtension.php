<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Twig;

use Twig\TwigFunction;
use Symfony\Component\Intl\Locales;
use Twig\Extension\AbstractExtension;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;

/**
 * See https://symfony.com/doc/current/templating/twig_extension.html.
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 * @author Julien ITARD <julienitard@gmail.com>
 */
class AppExtension extends AbstractExtension
{
    private $localeCodes;
    private $locales;

    public function __construct(string $locales, ContainerInterface $container, string $publicDir,  EntrypointLookupInterface $encoreEntrypointLookup)
    {
        $localeCodes = explode('|', $locales);
        sort($localeCodes);
        $this->localeCodes = $localeCodes;


        $this->encoreEntrypointLookup = $encoreEntrypointLookup;
        $this->container = $container;
        $this->publicDir = $publicDir;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('locales', [$this, 'getLocales']),
            new TwigFunction('encore_entry_css_source', [$this, 'getEncoreEntryCssSource']),
        ];
    }

    /**
     * Takes the list of codes of the locales (languages) enabled in the
     * application and returns an array with the name of each locale written
     * in its own language (e.g. English, Français, Español, etc.).
     */
    public function getLocales(): array
    {
        if (null !== $this->locales) {
            return $this->locales;
        }

        $this->locales = [];
        foreach ($this->localeCodes as $localeCode) {
            $this->locales[] = ['code' => $localeCode, 'name' => Locales::getName($localeCode, $localeCode)];
        }

        return $this->locales;
    }

    public function getEncoreEntryCssSource(string $entryName): string
    {
        $files = $this->encoreEntrypointLookup
            ->getCssFiles($entryName);
        $source = '';
        foreach ($files as $file) {
            $source .= file_get_contents($this->publicDir . '/' . $file);
        }
        return $source;
    }
}
