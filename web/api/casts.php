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
 * @file $/web/api/casts.php
 * @brief List of casts for a vote.
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
    $casts = Database::Get()->Query("SELECT `id`,\n".
                                    "    `handle_voter`,\n".
                                    "    `id_vote_option`\n".
                                    "FROM `".Database::Get()->GetPrefix()."votes`\n".
                                    "WHERE `handle_vote` LIKE ?",
                                    array($handle),
                                    array(Database::TYPE_STRING));

    if (CONTENT_TYPE_REQUESTED === CONTENT_TYPE_SUPPORTED_XML)
    {
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
             "<casts xmlns:xforms=\"http://www.w3.org/2002/xforms\">\n";

        if (is_array($casts) === true)
        {
            foreach ($casts as $cast)
            {
                echo "  <cast>\n".
                     "    <handle-voter>".htmlspecialchars($cast['handle_voter'], ENT_COMPAT | ENT_XML1, "UTF-8")."</handle-voter>\n".
                     "    <id-vote-option>".htmlspecialchars($cast['id_vote_option'], ENT_COMPAT | ENT_XML1, "UTF-8")."</id-vote-option>\n".
                     "  </cast>\n";
            }
        }

        echo "  <xforms:model>\n".
             "    <xforms:instance>\n".
             "      <xforms:input ref=\"id_vote_option\">\n".
             "        <xforms:label>Option Id</xforms:label>\n".
             "      </xforms:input>\n".
             // Hidden field equivalent:
             "      <xforms:data xmlns=\"\"><handle_voter/></xforms:data>\n".
             "    </xforms:instance>\n".
             "    <xforms:submission action=\"".$baseURL."/casts.php?handle=".htmlspecialchars($handle, ENT_COMPAT | ENT_XML1, "UTF-8")."\" method=\"post\">\n".
             "      <xforms:label>Submit</xforms:label>\n".
             "    </xforms:submission>\n".
             "  </xforms:model>\n".
             "</casts>\n";
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
             "    <title>Casts</title>\n".
             "  </head>\n".
             "  <body>\n".
             "    <ul class=\"casts\">\n";

        if (is_array($casts) === true)
        {
            foreach ($casts as $cast)
            {
                echo "      <li class=\"cast\">\n".
                     "        <span class=\"handle-voter\">".htmlspecialchars($cast['handle_voter'], ENT_COMPAT | ENT_HTML401, "UTF-8")."</span>\n".
                     "        <span class=\"id-vote-option\">".htmlspecialchars($cast['id_vote_option'], ENT_COMPAT | ENT_HTML401, "UTF-8")."</span>\n".
                     "      </li>\n";
            }
        }

        echo "    </ul>\n".
             "    <form action=\"".$baseURL."/casts.php?handle=".htmlspecialchars($handle, ENT_COMPAT | ENT_HTML401, "UTF-8")."\" method=\"post\">\n".
             "      <fieldset>\n".
             "        <label for=\"id_vote_option\">Option Id</label>\n".
             "        <input name=\"name\" type=\"text\" maxlength=\"255\" id=\"id_vote_option\"/><br/>\n".
             "        <input type=\"hidden\" name=\"handle_voter\"/>\n".
             "        <input type=\"submit\" value=\"Submit\"/><br/>\n".
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
    if (isset($_POST['handle_voter']) !== true)
    {
        http_response_code(409);
        echo "'handle_voter' is missing.";
        exit(-1);
    }

    if (isset($_POST['id_vote_option']) !== true)
    {
        http_response_code(409);
        echo "'id_vote_option' is missing.";
        exit(-1);
    }

    $id = Database::Get()->Insert("INSERT INTO `".Database::Get()->GetPrefix()."casts` (`id`,\n".
                                  "    `handle_vote`,\n".
                                  "    `handle_voter`,\n".
                                  "    `id_vote_option`)\n".
                                  "VALUES (?, ?, ?, ?)\n",
                                  array(NULL, $handle, $_POST['handle_voter'], (int)$_POST['id_vote_option']),
                                  array(Database::TYPE_NULL, Database::TYPE_STRING, Database::TYPE_STRING, Database::TYPE_INT));

    if ($id <= 0)
    {
        http_response_code(500);
        exit(-1);
    }

    http_response_code(201);
}
else
{
    http_response_code(405);
    exit(-1);
}


?>
