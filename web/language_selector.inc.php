<?php
/* Copyright (C) 2013-2017 Stephan Kreutzer
 *
 * This file is part of VoterFlow.
 *
 * VoterFlow is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License version 3 or any later version,
 * as published by the Free Software Foundation.
 *
 * VoterFlow is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License 3 for more details.
 *
 * You should have received a copy of the GNU Affero General Public License 3
 * along with VoterFlow. If not, see <http://www.gnu.org/licenses/>.
 */


 
require_once(dirname(__FILE__)."/libraries/languagelib.inc.php");



function getHTMLLanguageSelector($targetPage,
                                 $cssClassLanguageselector = "",
                                 $cssClassLanguageselectorText = "",
                                 $cssClassLanguageselectorForm = "",
                                 $cssClassLanguageselectorFormFieldset = "",
                                 $cssClassLanguageselectorFormSelect = "",
                                 $cssClassLanguageselectorFormSubmitbutton = "")
{
    $html = "";
    $currentLanguage = getDefaultLanguage();
    $languages = getLanguageList();

    if (isset($_POST['lang']) === true &&
        is_array($languages) === true)
    {
        // Select another language.

        if (count($languages) > 0)
        {
            if (array_key_exists($_POST['lang'], $languages) === true)
            {
                $currentLanguage = $_POST['lang'];
                $_SESSION['language'] = $currentLanguage;
                unset($_POST['lang']);
            }
        }
    }

    require_once(getLanguageFile("language_selector", dirname(__FILE__)));

    if (is_array($languages) === true)
    {
        if (count($languages) > 0)
        {
            if (isset($_SESSION['language']) === true)
            {
                $currentLanguage = $_SESSION['language'];
            }
            else
            {
                $currentLanguage = getDefaultLanguage();
            }

            $direction = getCurrentLanguageDirection();

            if ($cssClassLanguageselector == "")
            {
                if ($direction === LanguageDefinition::DirectionRTL)
                {
                    $cssClassLanguageselector = "languageselector_rtl";
                }
                else
                {
                    $cssClassLanguageselector = "languageselector";
                }
            }

            if ($cssClassLanguageselectorText == "")
            {
                $cssClassLanguageselectorText = "languageselector_text";
            }

            if ($cssClassLanguageselectorForm == "")
            {
                $cssClassLanguageselectorForm = "languageselector_form";
            }

            if ($cssClassLanguageselectorFormFieldset == "")
            {
                $cssClassLanguageselectorFormFieldset = "languageselector_form_fieldset";
            }

            if ($cssClassLanguageselectorFormSelect == "")
            {
                $cssClassLanguageselectorFormSelect = "languageselector_form_select";
            }

            if ($cssClassLanguageselectorFormSubmitbutton == "")
            {
                $cssClassLanguageselectorFormSubmitbutton = "languageselector_form_submitbutton";
            }

            $html .= "<div class=\"".$cssClassLanguageselector."\">\n".
                     "  <span class=\"".$cssClassLanguageselectorText."\">".LANG_LANGUAGESELECTOR_DESCRIPTION."</span><br/>\n".
                     "  <form action=\"".$targetPage."\" method=\"post\" class=\"".$cssClassLanguageselectorForm."\">\n".
                     "    <fieldset class=\"".$cssClassLanguageselectorFormFieldset."\">\n".
                     "      <select name=\"lang\" size=\"1\" class=\"".$cssClassLanguageselectorFormSelect."\">\n";

            foreach ($languages as $language)
            {
                $languageCode = $language->getCode();
                $displayName = $language->getCaption();

                if ($languageCode == $currentLanguage)
                {
                    // Doesn't work on Mozilla Firefox, but is XHTML standard. Standard
                    // wins over browser implementation.
                    $html .= "        <option value=\"".$languageCode."\" selected=\"selected\">".$displayName."</option>\n";
                }
                else
                {
                    $html .= "        <option value=\"".$languageCode."\">".$displayName."</option>\n";
                }
            }

            $html .= "      </select>\n".
                     "      <input type=\"submit\" value=\"".LANG_LANGUAGESELECTOR_SUBMITBUTTON."\" class=\"".$cssClassLanguageselectorFormSubmitbutton."\"/>\n".
                     "    </fieldset>\n".
                     "  </form>\n".
                     "</div>\n";
        }
    }

    return $html;
}



?>
