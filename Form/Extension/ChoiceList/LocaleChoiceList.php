<?php

/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */

namespace Lunetics\LocaleBundle\Form\Extension\ChoiceList;

use Lunetics\LocaleBundle\LocaleInformation\LocaleInformation;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Intl\Intl;

/**
 * Locale Choicelist Class
 */
class LocaleChoiceList extends ArrayChoiceList
{
    private $localeChoices;
    private $preferredChoices;

    /**
     * Construct the LocaleChoiceList
     *
     * @param LocaleInformation $information   LocaleInformation Service
     * @param bool              $languagesOnly If only Languages should be displayed
     * @param bool              $strictMode    If strict mode
     */
    public function __construct(LocaleInformation $information, $languagesOnly = true, $strictMode = false)
    {
        $this->localeChoices = array();
        $allowedLocales = $strictMode
            ? $information->getAllowedLocalesFromConfiguration()
            : $information->getAllAllowedLanguages();

        foreach ($allowedLocales as $locale) {
            if ($languagesOnly && strlen($locale) == 2 || !$languagesOnly) {
                $this->localeChoices[$locale] = Intl::getLanguageBundle()->getLanguageName($locale, $locale);
            }
        }

        $this->preferredChoices = $information->getPreferredLocales();

        parent::__construct($this->localeChoices);
    }

    /**
     * Returns the preferred views, sorted by the ->preferredChoices list
     *
     * @return array|void
     */
    public function getPreferredViews()
    {
        $preferredViews = parent::getPreferredViews();
        $result = array();
        foreach ($this->preferredChoices as $pchoice) {
            foreach ($preferredViews as $view) {
                if ($pchoice == $view->data) {
                    $result[] = $view;
                }
            }
        }

        return $result;
    }

    /**
     * Returns the remaining locales sorted by language name
     *
     * @return array
     */
    public function getRemainingViews()
    {
        $remainingViews = parent::getRemainingViews();
        usort($remainingViews, function (ChoiceView $choiceView1, ChoiceView $choiceView2) {
            return \Collator::create(null)->compare($choiceView1->label, $choiceView2->label);
        });

        return $remainingViews;
    }
}
