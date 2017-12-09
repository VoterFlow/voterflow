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
 * @file $/web/api/votes.php
 * @brief List of votes.
 * @author Stephan Kreutzer
 * @since 2016-10-06
 */



require_once(dirname(__FILE__)."/../libraries/https.inc.php");
require_once(dirname(__FILE__)."/../libraries/database.inc.php");
require_once(dirname(__FILE__)."/libraries/negotiation.inc.php");

NegotiateContentType(array(CONTENT_TYPE_SUPPORTED_XML,
                           CONTENT_TYPE_SUPPORTED_XHTML,
                           CONTENT_TYPE_SUPPORTED_JSONCOLLECTION));

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

if ($_SERVER['REQUEST_METHOD'] === "GET")
{
    $votes = Database::Get()->QueryUnsecure("SELECT `id`,\n".
                                            "    `handle`,\n".
                                            "    `type`,\n".
                                            "    `name`,\n".
                                            "    `description`,\n".
                                            "    `datetime_created`\n".
                                            "FROM `".Database::Get()->GetPrefix()."votes`\n".
                                            "WHERE 1");

    if (CONTENT_TYPE_REQUESTED === CONTENT_TYPE_SUPPORTED_XML)
    {
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
             "<votes xmlns:xforms=\"http://www.w3.org/2002/xforms\">\n";

        if (is_array($votes) === true)
        {
            foreach ($votes as $vote)
            {
                echo "  <vote>\n".
                     "    <handle>".htmlspecialchars($vote['handle'], ENT_COMPAT | ENT_XML1, "UTF-8")."</handle>\n".
                     "    <name>".htmlspecialchars($vote['name'], ENT_COMPAT | ENT_XML1, "UTF-8")."</name>\n".
                     "    <description>".htmlspecialchars($vote['description'], ENT_COMPAT | ENT_XML1, "UTF-8")."</description>\n".
                     "    <datetime-created>".htmlspecialchars($vote['datetime_created'], ENT_COMPAT | ENT_XML1, "UTF-8")."</datetime-created>\n".
                     "    <xforms:model>\n".
                     "      <xforms:submission action=\"".$baseURL."/vote.php?handle=".htmlspecialchars($vote['handle'], ENT_COMPAT | ENT_XML1, "UTF-8")."\" method=\"get\"/>\n".
                     "    </xforms:model>\n".
                     "  </vote>\n";
            }
        }

        echo "  <xforms:model>\n".
             "    <xforms:instance>\n".
             "      <xforms:input ref=\"name\">\n".
             "        <xforms:label>Name</xforms:label>\n".
             "      </xforms:input>\n".
             "      <xforms:input ref=\"description\">\n".
             "        <xforms:label>Description</xforms:label>\n".
             "      </xforms:input>\n".
             "    </xforms:instance>\n".
             "    <xforms:submission action=\"".$baseURL."/votes.php\" method=\"post\">\n".
             "      <xforms:label>Submit</xforms:label>\n".
             "    </xforms:submission>\n".
             "  </xforms:model>\n".
             "</votes>\n";
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
             "    <title>Votes</title>\n".
             "  </head>\n".
             "  <body>\n".
             "    <ul class=\"votes\">\n";

        if (is_array($votes) === true)
        {
            foreach ($votes as $vote)
            {
                echo "      <li class=\"vote\">\n".
                     "        <span class=\"handle\">".htmlspecialchars($vote['handle'], ENT_COMPAT | ENT_HTML401, "UTF-8")."</span>\n".
                     "        <a href=\"".$baseURL."/vote.php?handle=".htmlspecialchars($vote['handle'], ENT_COMPAT | ENT_HTML401, "UTF-8")."\"><span class=\"name\">".htmlspecialchars($vote['name'], ENT_COMPAT | ENT_HTML401, "UTF-8")."</span></a>\n".
                     "        <span class=\"description\">".htmlspecialchars($vote['description'], ENT_COMPAT | ENT_HTML401, "UTF-8")."</span>\n".
                     "        <span class=\"datetime-created\">".htmlspecialchars($vote['datetime_created'], ENT_COMPAT | ENT_HTML401, "UTF-8")."</span>\n".
                     "      </li>\n";
            }
        }

        echo "    </ul>\n".
             "    <form action=\"".$baseURL."/votes.php\" method=\"post\">\n".
             "      <fieldset>\n".
             "        <label for=\"vote_new_name\">Name</label>\n".
             "        <input name=\"name\" type=\"text\" maxlength=\"255\" id=\"vote_new_name\"/><br/>\n".
             "        <label for=\"vote_new_description\">Description</label>\n".
             "        <textarea name=\"description\" cols=\"80\" rows=\"24\" id=\"vote_new_description\"></textarea><br/>\n".
             "        <input type=\"submit\" value=\"Submit\"/><br/>\n".
             "      </fieldset>\n".
             "    </form>\n".
             "  </body>\n".
             "</html>\n";
    }
    else if (CONTENT_TYPE_REQUESTED === CONTENT_TYPE_SUPPORTED_JSONCOLLECTION)
    {
        /** @todo Is this even necessary when it comes to its current usage? */
        function jsonspecialchars($input)
        {
            return rtrim(ltrim(json_encode($input), "\""), "\"");
        }

        echo "{\n".
             "  \"collection\":\n".
             "  {\n".
             "    \"version\": \"1.0\",\n".
             "    \"href\": \"".$baseURL."/votes.php\",\n".
             "    \"items\":\n".
             "    [\n";

        if (is_array($votes) !== true)
        {
            echo "    ]\n".
                 "  }\n".
                 "}\n";
            exit(0);
        }

        $first = true;

        foreach ($votes as $vote)
        {
            if ($first === true)
            {
                $first = false;
            }
            else
            {
                echo "      },\n";
            }

            echo "      {\n".
                 "        \"href\": \"".$baseURL."/vote.php?handle=".jsonspecialchars($vote['handle'])."\",\n".
                 "        \"data\":\n".
                 "        [\n".
                 "          { \"name\": \"handle\", \"value\": ".json_encode($vote['handle'])." },\n".
                 "          { \"name\": \"name\", \"value\": ".json_encode($vote['name'])." },\n".
                 "          { \"name\": \"description\", \"value\": ".json_encode($vote['description'])." },\n".
                 "          { \"name\": \"datetime_created\", \"value\": ".json_encode($vote['datetime_created'])." }\n".
                 "        ]\n";
        }

        echo "      }\n".
             "    ],\n".
             "    \"template\":\n".
             "    {\n".
             "      \"data\":\n".
             "      [\n".
             "        { \"name\": \"name\", \"value\": \"\", \"prompt\": \"Name\" },\n".
             "        { \"name\": \"description\", \"value\": \"\", \"prompt\": \"Description\" }\n".
             "      ]\n".
             "    }\n".
             "  }\n".
             "}\n";
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

    if (isset($_POST['description']) !== true)
    {
        http_response_code(409);
        echo "'description' is missing.";
        exit(-1);
    }

    $handle = md5(uniqid(rand(), true));

    $id = Database::Get()->Insert("INSERT INTO `".Database::Get()->GetPrefix()."votes` (`id`,\n".
                                  "    `handle`,\n".
                                  "    `type`,\n".
                                  "    `name`,\n".
                                  "    `description`,\n".
                                  "    `datetime_created`)\n".
                                  "VALUES (?, ?, ?, ?, ?, NOW())\n",
                                  array(NULL, $handle, 1, $_POST['name'], $_POST['description']),
                                  array(Database::TYPE_NULL, Database::TYPE_STRING, Database::TYPE_INT, Database::TYPE_STRING, Database::TYPE_STRING));

    if ($id <= 0)
    {
        http_response_code(500);
        exit(-1);
    }

    $link = $baseURL."/vote.php?handle=".$handle;

    header("Location: ".$link, true, 201);

    if (CONTENT_TYPE_REQUESTED === CONTENT_TYPE_SUPPORTED_XML)
    {
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
             "<vote xmlns:xlink=\"http://www.w3.org/1999/xlink\" xlink:type=\"simple\" xlink:href=\"".$link."\">".$link."</vote>\n";
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
             "    <title>Vote</title>\n".
             "  </head>\n".
             "  <body>\n".
             "    <a href=\"".$link."\">".$link."</a>\n".
             "  </body>\n".
             "</html>\n";
    }
    else if (CONTENT_TYPE_REQUESTED === CONTENT_TYPE_SUPPORTED_JSONCOLLECTION)
    {
        echo "{\n".
             "  \"collection\":\n".
             "  {\n".
             "    \"version\": \"1.0\",\n".
             "    \"href\": \"".$baseURL."/votes.php\",\n".
             "    \"items\":\n".
             "    [\n".
             "      {\n".
             "        \"href\": \"".$link."\",\n".
             "        \"data\":\n".
             "        [\n".
             "        ]\n".
             "      }\n".
             "    }\n".
             "  }\n".
             "}\n";
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
