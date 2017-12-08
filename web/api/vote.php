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
 * @file $/web/api/vote.php
 * @author Stephan Kreutzer
 * @since 2016-10-06
 */



require_once(dirname(__FILE__)."/../libraries/https.inc.php");
require_once(dirname(__FILE__)."/../libraries/database.inc.php");
require_once(dirname(__FILE__)."/libraries/negotiation.inc.php");

NegotiateContentType(array(CONTENT_TYPE_SUPPORTED_XML,
                           CONTENT_TYPE_SUPPORTED_XHTML,
                           CONTENT_TYPE_SUPPORTED_JSON));

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
    $id = 0;

    if (isset($_GET['id']) === true)
    {
        $id = (int)$_GET['id'];
    }

    if ($id <= 0)
    {
        http_response_code(409);
        echo "'id' is missing or corrupt.";
        exit(-1);
    }

    $vote = Database::Get()->Query("SELECT `id`,\n".
                                   "    `handle`,\n".
                                   "    `type`,\n".
                                   "    `name`,\n".
                                   "    `description`,\n".
                                   "    `datetime_created`\n".
                                   "FROM `".Database::Get()->GetPrefix()."votes`\n".
                                   "WHERE id=? AND\n".
                                   "    type=1",
                                   array($id),
                                   array(Database::TYPE_INT));

    if (is_array($vote) !== true)
    {
        http_response_code(404);
        exit(-1);
    }

    if (count($vote) < 1)
    {
        http_response_code(404);
        exit(-1);
    }

    $vote = $vote[0];

    if (CONTENT_TYPE_REQUESTED === CONTENT_TYPE_SUPPORTED_XML)
    {
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
             "<vote>\n".
             "  <id>".htmlspecialchars($vote['id'], ENT_COMPAT | ENT_XML1, "UTF-8")."</id>\n".
             "  <handle>".htmlspecialchars($vote['handle'], ENT_COMPAT | ENT_XML1, "UTF-8")."</handle>\n".
             "  <name>".htmlspecialchars($vote['name'], ENT_COMPAT | ENT_XML1, "UTF-8")."</name>\n".
             "  <description>".htmlspecialchars($vote['description'], ENT_COMPAT | ENT_XML1, "UTF-8")."</description>\n".
             "  <datetime-created>".htmlspecialchars($vote['datetime_created'], ENT_COMPAT | ENT_XML1, "UTF-8")."</datetime-created>\n".
             "  <voters xmlns:xlink=\"http://www.w3.org/1999/xlink\" xlink:type=\"simple\" xlink:href=\"".$baseURL."/voters.php?id=".$id."\"/>\n".
             "</vote>\n";
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
             "    <div class=\"vote\">\n".
             "      <span class=\"id\">".htmlspecialchars($vote['id'], ENT_COMPAT | ENT_HTML401, "UTF-8")."</span>\n".
             "      <span class=\"name\">".htmlspecialchars($vote['name'], ENT_COMPAT | ENT_HTML401, "UTF-8")."</span>\n".
             "      <span class=\"description\">".htmlspecialchars($vote['description'], ENT_COMPAT | ENT_HTML401, "UTF-8")."</span>\n".
             "      <span class=\"datetime-created\">".htmlspecialchars($vote['datetime_created'], ENT_COMPAT | ENT_HTML401, "UTF-8")."</span>\n".
             "      <a href=\"".$baseURL."/voters.php?id=".$id."\">Voters</a>\n".
             "    </div>\n".
             "  </body>\n".
             "</html>\n";
    }
    else if (CONTENT_TYPE_REQUESTED === CONTENT_TYPE_SUPPORTED_JSON)
    {
        echo "{\n".
             "  \"id\": ".$vote['id'].",\n".
             "  \"name\": ".json_encode($vote['name']).",\n".
             "  \"description\": ".json_encode($vote['description']).",\n".
             "  \"datetime_created\": ".json_encode($vote['datetime_created']).",\n".
             "  \"voters\": ".json_encode($baseURL."/voters.php?id=".$vote['id'])."\n".
             "}\n";
    }
    else
    {
        http_response_code(501);
        exit(-1);
    }
}
else
{
    http_response_code(405);
    exit(-1);
}


?>
