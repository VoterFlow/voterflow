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

class LanguageDefinition
{
    const DirectionLTR = 1;
    const DirectionRTL = 2;

    public function __construct($code, $caption, $direction)
    {
        $this->code = $code;
        $this->caption = $caption;

        if ($direction === LanguageDefinition::DirectionLTR ||
            $direction === LanguageDefinition::DirectionRTL)
        {
            $this->direction = $direction;
        }
        else
        {
            $this->direction = LanguageDefinition::DirectionLTR;
        }
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getCaption()
    {
        return $this->caption;
    }

    public function getDirection()
    {
        return $this->direction;
    }

    protected $code;
    protected $caption;
    protected $direction;
}

function getLanguageFile($caller, $baseDirectory = ".")
{
    if (empty($_SESSION) === true)
    {
        @session_start();
    }

    $language = getDefaultLanguage();
    $languages = getLanguageList();

    if (isset($_GET['lang']) === true &&
        is_array($languages) === true)
    {
        if (count($languages) > 0)
        {
            if (array_key_exists($_GET['lang'], $languages) === true)
            {
                $language = $_GET['lang'];
                $_SESSION['language'] = $language;
                unset($_GET['lang']);
            }
        }
    }

    if (isset($_POST['lang']) === true &&
        is_array($languages) === true)
    {
        // Select another language.

        if (count($languages) > 0)
        {
            if (array_key_exists($_POST['lang'], $languages) === true)
            {
                $language = $_POST['lang'];
                $_SESSION['language'] = $language;
                unset($_POST['lang']);
            }
        }
    }

    if (isset($_SESSION['language']) !== true &&
        isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) === true)
    {
        /** @todo Use $/web/api/libraries/Negotiation after update from
          * https://github.com/willdurand/Negotiation) to determine
          * a reasonable setting on the first encounter. */
    }

    if (isset($_SESSION['language']) === true)
    {
        $language = $_SESSION['language'];
    }

    if (is_string($baseDirectory) === true)
    {
        if (is_dir($baseDirectory) === true)
        {
            $last = substr($baseDirectory, -1);

            if ($last == "/" ||
                $last == "\\")
            {
                $last = substr_replace($last, "", -1);
            }
        }
        else
        {
            $baseDirectory = ".";
        }
    }
    else
    {
        $baseDirectory = ".";
    }

    // If no $baseDirectory is supplied, look relatively to the caller's directory.
    $filePath = $baseDirectory."/lang/".$language."/".$caller.".lang.php";

    if (file_exists($filePath) === true)
    {
        return $filePath;
    }
    else
    {
        $language = getFallbackLanguage();
    }

    // If no $baseDirectory is supplied, look relatively to the caller's directory.
    $filePath = $baseDirectory."/lang/".$language."/".$caller.".lang.php";

    if (file_exists($filePath) === true)
    {
        return $filePath;
    }
    else
    {
        $missingFilePath = dirname($_SERVER['PHP_SELF']);
        $missingFilePath = $missingFilePath."/lang/".$language."/".$caller.".lang.php";

        echo "The language file \$".$missingFilePath." for the fallback language \"".
             getFallbackLanguage()."\" is missing. This shouldn't have happened.\n";
        exit();
    }
}

function getLanguageList()
{
    return array("de" => new LanguageDefinition("de", "Deutsch", LanguageDefinition::DirectionLTR),
                 //"ar" => new LanguageDefinition("ar", "العربية", LanguageDefinition::DirectionRTL),
                 "en" => new LanguageDefinition("en", "English", LanguageDefinition::DirectionLTR));
}

function getDefaultLanguage()
{
    return "de";
}

function getFallbackLanguage()
{
    return "en";
}

function getCurrentLanguage()
{
    if (empty($_SESSION) === true)
    {
        @session_start();
    }

    if (isset($_SESSION['language']) === true)
    {
        return $_SESSION['language'];
    }
    else
    {
        return getDefaultLanguage();
    }
}

function getCurrentLanguageObject()
{
    $currentLanguage = getCurrentLanguage();
    $languages = getLanguageList();

    if (is_array($languages) === true)
    {
        if (count($languages) > 0)
        {
            if (array_key_exists($currentLanguage, $languages) === true)
            {
                return $languages[$currentLanguage];
            }
        }
    }

    return null;
}

function getCurrentLanguageDirection()
{
    $direction = LanguageDefinition::DirectionLTR;
    $languageObject = getCurrentLanguageObject();

    if ($languageObject != null)
    {
        $direction = $languageObject->getDirection();
    }

    return $direction;
}


?>
