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
 * @file $/web/api/voters.php
 * @brief List of voters.
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
    $voters = Database::Get()->Query("SELECT `id`,\n".
                                     "    `handle`,\n".
                                     "    `name`\n".
                                     "FROM `".Database::Get()->GetPrefix()."voters`\n".
                                     "WHERE `handle_vote` LIKE ?",
                                     array($handle),
                                     array(Database::TYPE_STRING));

    if (CONTENT_TYPE_REQUESTED === CONTENT_TYPE_SUPPORTED_XML)
    {
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
             "<voters xmlns:xforms=\"http://www.w3.org/2002/xforms\">\n";

        if (is_array($voters) === true)
        {
            foreach ($voters as $voter)
            {
                echo "  <voter>\n".
                     "    <voter>\n".
                     "      <xforms:model>\n".
                     "        <xforms:submission action=\"".$baseURL."/voter.php?handle=".htmlspecialchars($voter['handle'], ENT_COMPAT | ENT_XML1, "UTF-8")."\" method=\"get\"/>\n".
                     "      </xforms:model>\n".
                     "    </voter>\n".
                     "    <name>".htmlspecialchars($voter['name'], ENT_COMPAT | ENT_XML1, "UTF-8")."</name>\n".
                     "    <vote>\n".
                     "      <xforms:model>\n".
                     "        <xforms:submission action=\"".$baseURL."/vote.php?handle=".htmlspecialchars($handle, ENT_COMPAT | ENT_XML1, "UTF-8")."\" method=\"get\"/>\n".
                     "      </xforms:model>\n".
                     "    </vote>\n".
                     "  </voter>\n";
            }
        }

        echo "  <xforms:model>\n".
             "    <xforms:instance>\n".
             "      <xforms:input ref=\"name\">\n".
             "        <xforms:label>Name</xforms:label>\n".
             "      </xforms:input>\n".
             "    </xforms:instance>\n".
             "    <xforms:submission action=\"".$baseURL."/voters.php?handle=".htmlspecialchars($handle, ENT_COMPAT | ENT_XML1, "UTF-8")."\" method=\"post\">\n".
             "      <xforms:label>Submit</xforms:label>\n".
             "    </xforms:submission>\n".
             "  </xforms:model>\n".
             "</voters>\n";
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
             "    <title>Voters</title>\n".
             "  </head>\n".
             "  <body>\n".
             "    <ul class=\"voters\">\n";

        if (is_array($voters) === true)
        {
            foreach ($voters as $voter)
            {
                echo "      <li class=\"voter\">\n".
                     "        <a href=\"".$baseURL."/voter.php?handle=".htmlspecialchars($voter['handle'], ENT_COMPAT | ENT_HTML401, "UTF-8")."\"><span class=\"handle\">Voter</span></a>\n".
                     "        <span class=\"name\">".htmlspecialchars($voter['name'], ENT_COMPAT | ENT_HTML401, "UTF-8")."</span>\n".
                     "        <a href=\"".$baseURL."/vote.php?handle=".htmlspecialchars($handle, ENT_COMPAT | ENT_HTML401, "UTF-8")."\"><span class=\"handle-vote\">Vote</span></a>\n".
                     "      </li>\n";
            }
        }

        echo "    </ul>\n".
             "    <form action=\"".$baseURL."/voters.php?handle=".htmlspecialchars($handle, ENT_COMPAT | ENT_HTML401, "UTF-8")."\" method=\"post\">\n".
             "      <fieldset>\n".
             "        <label for=\"voter_name\">Name</label>\n".
             "        <input name=\"name\" type=\"text\" maxlength=\"255\" id=\"voter_name\"/><br/>\n".
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
    if (isset($_POST['name']) !== true)
    {
        http_response_code(409);
        echo "'name' is missing.";
        exit(-1);
    }

    $handleNew = md5(uniqid(rand(), true));
/*echo "INSERT INTO `".Database::Get()->GetPrefix()."voters` (`id`,\n".
                                  "    `handle`,\n".
                                  "    `name`,\n".
                                  "    `handle_vote`) VALUES (NULL, '".$handleNew."', '".$_POST['name']."', '".$handle."')";*/
    $id = Database::Get()->Insert("INSERT INTO `".Database::Get()->GetPrefix()."voters` (`id`,\n".
                                  "    `handle`,\n".
                                  "    `name`,\n".
                                  "    `handle_vote`)\n".
                                  "VALUES (?, ?, ?, ?)\n",
                                  array(NULL, $handleNew, $_POST['name'], $handle),
                                  array(Database::TYPE_NULL, Database::TYPE_STRING, Database::TYPE_STRING, Database::TYPE_STRING));

    if ($id <= 0)
    {
        http_response_code(500);
        exit(-1);
    }

    $link = $baseURL."/voter.php?handle=".$handleNew;

    header("Location: ".$link, true, 201);

    if (CONTENT_TYPE_REQUESTED === CONTENT_TYPE_SUPPORTED_XML)
    {
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
             "<voter xmlns:xlink=\"http://www.w3.org/1999/xlink\" xlink:type=\"simple\" xlink:href=\"".$link."\">".$link."</voter>\n";
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
             "    <title>Voter</title>\n".
             "  </head>\n".
             "  <body>\n".
             "    <a href=\"".$link."\">".$link."</a>\n".
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
