<?php
/* Copyright (C) 2014-2017  Stephan Kreutzer
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
/**
 * @file $/web/api/vote_options.php
 * @brief List of options that can be selected for a vote.
 * @author Stephan Kreutzer
 * @since 2017-12-08
 */



require_once(dirname(__FILE__)."/../libraries/https.inc.php");
require_once(dirname(__FILE__)."/../libraries/database.inc.php");
require_once(dirname(__FILE__)."/libraries/negotiation.inc.php");

NegotiateContentType(array(CONTENT_TYPE_SUPPORTED_XML,
                           CONTENT_TYPE_SUPPORTED_XHTML));

$protocol = "https://";

if (HTTPS_ENABLED !== true)
{
    $protocol = "http://";
}

$baseURL = $protocol.$_SERVER['HTTP_HOST'].dirname($_SERVER['REQUEST_URI']);

if (Database::Get()->IsConnected() !== true)
{
    http_response_code(500);
    exit(-1);
}

$handle = null;

if (isset($_GET['handle']) === true)
{
    $handle = $_GET['handle'];
}

if ($handle === null)
{
    http_response_code(409);
    echo "'handle' is missing or corrupt.";
    exit(-1);
}

if ($_SERVER['REQUEST_METHOD'] === "GET")
{
    $options = Database::Get()->Query("SELECT `id`,\n".
                                      "    `position`,\n".
                                      "    `caption`\n".
                                      "FROM `".Database::Get()->GetPrefix()."vote_options`\n".
                                      "WHERE `handle_vote` LIKE ?\n".
                                      "ORDER BY `position`",
                                      array($handle),
                                      array(Database::TYPE_STRING));

    if (CONTENT_TYPE_REQUESTED === CONTENT_TYPE_SUPPORTED_XML)
    {
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
             "<options xmlns:xforms=\"http://www.w3.org/2002/xforms\">\n";

        if (is_array($options) === true)
        {
            foreach ($options as $option)
            {
                echo "  <option>\n".
                     "    <id>".htmlspecialchars($option['id'], ENT_COMPAT | ENT_XML1, "UTF-8")."</id>\n".
                     "    <position>".htmlspecialchars($option['position'], ENT_COMPAT | ENT_XML1, "UTF-8")."</position>\n".
                     "    <caption>".htmlspecialchars($option['caption'], ENT_COMPAT | ENT_XML1, "UTF-8")."</caption>\n".
                     "  </option>\n";
            }
        }

        echo "  <xforms:model>\n".
             "    <xforms:instance>\n".
             "      <xforms:input ref=\"position\">\n".
             "        <xforms:label>Position</xforms:label>\n".
             "      </xforms:input>\n".
             "      <xforms:input ref=\"caption\">\n".
             "        <xforms:label>Caption</xforms:label>\n".
             "      </xforms:input>\n".
             // Hidden field equivalent:
             // "      <xforms:data xmlns=\"\"><handle>".htmlspecialchars($handle, ENT_COMPAT | ENT_XML1, "UTF-8")."</handle></xforms:data>\n".
             "    </xforms:instance>\n".
             "    <xforms:submission action=\"".$baseURL."/vote_options.php?handle=".htmlspecialchars($handle, ENT_COMPAT | ENT_XML1, "UTF-8")."\" method=\"post\">\n".
             "      <xforms:label>Submit</xforms:label>\n".
             "    </xforms:submission>\n".
             "  </xforms:model>\n".
             "</options>\n";
    }
    else if (CONTENT_TYPE_REQUESTED === CONTENT_TYPE_SUPPORTED_XHTML)
    {
        /** @todo XHTML 1.1 + XForms doesn't validate: waiting for XHTML 2.0. */

        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
             "<!DOCTYPE html\n".
             "    PUBLIC \"-//W3C//DTD XHTML 1.1//EN\"\n".
             "    \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n".
             "<html version=\"-//W3C//DTD XHTML 1.1//EN\" xmlns=\"http://www.w3.org/1999/xhtml\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://www.w3.org/1999/xhtml http://www.w3.org/MarkUp/SCHEMA/xhtml11.xsd\" xml:lang=\"en\" lang=\"en\">\n".
             "  <head>\n".
             "    <meta http-equiv=\"content-type\" content=\"".CONTENT_TYPE_SUPPORTED_XHTML."\"/>\n".
             "    <title>Vote Options</title>\n".
             "  </head>\n".
             "  <body>\n".
             "    <ul class=\"vote-options\">\n";

        if (is_array($options) === true)
        {
            foreach ($options as $option)
            {
                echo "      <li class=\"option\">\n".
                     "        <span class=\"id\">".htmlspecialchars($option['id'], ENT_COMPAT | ENT_HTML401, "UTF-8")."</span>\n".
                     "        <span class=\"position\">".htmlspecialchars($option['position'], ENT_COMPAT | ENT_HTML401, "UTF-8")."</span>\n".
                     "        <span class=\"caption\">".htmlspecialchars($option['caption'], ENT_COMPAT | ENT_HTML401, "UTF-8")."</span>\n".
                     "      </li>\n";
            }
        }

        echo "    </ul>\n".
             "    <form action=\"".$baseURL."/vote_options.php?handle=".htmlspecialchars($handle, ENT_COMPAT | ENT_XML1, "UTF-8")."\" method=\"post\">\n".
             "      <fieldset>\n".
             "        <label for=\"option_position\">Position</label>\n".
             "        <input name=\"position\" type=\"text\" maxlength=\"255\" id=\"option_position\"/><br/>\n".
             "        <label for=\"option_caption\">Caption</label>\n".
             "        <input name=\"caption\" type=\"text\" maxlength=\"255\" id=\"option_caption\"/><br/>\n".
             "        <input type=\"submit\" value=\"Submit\"/><br/>\n".
             //"        <input type=\"hidden\" name=\"handle\" value=\"".htmlspecialchars($handle, ENT_COMPAT | ENT_HTML401, "UTF-8")."\"/>\n".
             "      </fieldset>\n".
             "    </form>\n".
             "  </body>\n".
             "</html>\n";
    }
    else
    {
        http_response_code(501);
        exit(-1);
    }
}
else if ($_SERVER['REQUEST_METHOD'] === "POST")
{
    if (isset($_POST['position']) !== true)
    {
        http_response_code(409);
        echo "'position' is missing.";
        exit(-1);
    }

    if (isset($_POST['caption']) !== true)
    {
        http_response_code(409);
        echo "'caption' is missing.";
        exit(-1);
    }

    $id = Database::Get()->Insert("INSERT INTO `".Database::Get()->GetPrefix()."vote_options` (`id`,\n".
                                  "    `position`,\n".
                                  "    `caption`,\n".
                                  "    `handle_vote`)\n".
                                  "VALUES (?, ?, ?, ?)\n",
                                  array(NULL, (int)$_POST['position'], $_POST['caption'], $handle),
                                  array(Database::TYPE_NULL, Database::TYPE_INT, Database::TYPE_STRING, Database::TYPE_STRING));

    if ($id <= 0)
    {
        http_response_code(500);
        exit(-1);
    }

    http_response_code(201);

    if (CONTENT_TYPE_REQUESTED === CONTENT_TYPE_SUPPORTED_XML)
    {
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
             "<vote-option>".$id."</vote-option>\n";
    }
    else if (CONTENT_TYPE_REQUESTED === CONTENT_TYPE_SUPPORTED_XHTML)
    {
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
             "<!DOCTYPE html\n".
             "    PUBLIC \"-//W3C//DTD XHTML 1.1//EN\"\n".
             "    \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n".
             "<html version=\"-//W3C//DTD XHTML 1.1//EN\" xmlns=\"http://www.w3.org/1999/xhtml\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://www.w3.org/1999/xhtml http://www.w3.org/MarkUp/SCHEMA/xhtml11.xsd\" xml:lang=\"en\" lang=\"en\">\n".
             "  <head>\n".
             "    <meta http-equiv=\"content-type\" content=\"".CONTENT_TYPE_SUPPORTED_XHTML."\"/>\n".
             "    <title>Vote Option</title>\n".
             "  </head>\n".
             "  <body>\n".
             "    <div id=\"id\">".$id."</div>\n".
             "  </body>\n".
             "</html>\n";
    }
    else
    {
        exit(-1);
    }
}
else
{
    http_response_code(405);
    exit(-1);
}


?>
