<?php
/* Copyright (C) 2016-2017 Stephan Kreutzer
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

$handle = null;
$handle_voter = null;

if (isset($_GET['handle']) === true)
{
    if (ctype_alnum($_GET['handle']) === true)
    {
        $handle = $_GET['handle'];
    }
}

if ($handle === null)
{
    http_response_code(409);
    echo "'handle' is missing or corrupt.";
    exit(-1);
}

if (isset($_GET['handle_voter']) === true)
{
    if (ctype_alnum($_GET['handle_voter']) === true)
    {
        $handle_voter = $_GET['handle_voter'];
    }
}

if ($handle_voter === null)
{
    http_response_code(409);
    echo "'handle_voter' is missing or corrupt.";
    exit(-1);
}

require_once(dirname(__FILE__)."/../../libraries/languagelib.inc.php");
require_once(getLanguageFile("index"));

$direction = getCurrentLanguageDirection();

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
     "<!DOCTYPE html\n".
     "    PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"\n".
     "    \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n".
     "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"".getCurrentLanguage()."\" lang=\"".getCurrentLanguage()."\">\n".
     "    <head>\n".
     "        <meta http-equiv=\"content-type\" content=\"application/xhtml+xml; charset=UTF-8\"/>\n".
     "        <title>".LANG_PAGETITLE."</title>\n".
     "        <link rel=\"stylesheet\" type=\"text/css\" href=\"../../mainstyle.css\"/>\n".
     "        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/>\n".
     "        <script type=\"text/javascript\" src=\"./client.js\"></script>\n".
     "    </head>\n".
     "    <body onload=\"requestVote('".htmlspecialchars($handle, ENT_COMPAT | ENT_HTML401, "UTF-8")."', '".htmlspecialchars($handle_voter, ENT_COMPAT | ENT_HTML401, "UTF-8")."');\">\n";

if ($direction === LanguageDefinition::DirectionRTL)
{
    echo "        <div id=\"content_rtl\">\n";
}
else
{
    echo "        <div id=\"content\">\n";
}

require_once("../../language_selector.inc.php");
echo getHTMLLanguageSelector("index.php?handle=".htmlspecialchars($handle, ENT_COMPAT | ENT_HTML401, "UTF-8")."&amp;handle_voter=".htmlspecialchars($handle_voter, ENT_COMPAT | ENT_HTML401, "UTF-8"));

echo "<div class=\"mainbox\">\n".
     "  <div class=\"mainbox_header\">\n".
     "    <h1 class=\"mainbox_header_h1\">".LANG_HEADER."</h1>\n".
     "  </div>\n".
     "  <div class=\"mainbox_body\">\n".
     "    <h2 id=\"vote-name\"></h2>\n".
     "    <p id=\"vote-description\"></p>\n".
     "    <div id=\"vote-options\"></div>\n".
     "    <div id=\"confirmed\" style=\"visibility: hidden;\">".LANG_THANKS."</div>\n".
     "  </div>\n".
     "</div>\n";

echo "        </div>\n".
     "    </body>\n".
     "</html>\n".
     "\n";



?>
