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
/**
 * @file $/web/api/libraries/negotiation.inc.php
 * @author Stephan Kreutzer
 * @since 2016-09-23
 */



spl_autoload_register(function($class_name) {
    $path = dirname(__FILE__)."/".$class_name.".php";
    $path = str_replace("\\", "/", $path);
    require $path;
});

define("CONTENT_TYPE_SUPPORTED_XML", "application/xml; charset=UTF-8; q=1.0");
define("CONTENT_TYPE_SUPPORTED_XHTML", "application/xhtml+xml; charset=UTF-8; q=0.35");
define("CONTENT_TYPE_SUPPORTED_JSONCOLLECTION", "application/vnd.collection+json; charset=UTF-8; q=0.1");
define("CONTENT_TYPE_SUPPORTED_JSON", "application/json; charset=UTF-8; q=0.1");

function NegotiateContentType($supportedContentTypes)
{
    $acceptHeaderSuggestion = "";

    foreach ($supportedContentTypes as $acceptHeader)
    {
        if (!empty($acceptHeaderSuggestion))
        {
            $acceptHeaderSuggestion .= ",";
        }

        $acceptHeaderSuggestion .= $acceptHeader;
    }

    define("CONTENT_TYPE_SUPPORTED_ACCEPTHEADERSUGGESTION", $acceptHeaderSuggestion);

    $requestedContentTypes = "";

    if (isset($_GET['format']) === true)
    {
        switch ($_GET['format'])
        {
        case "xml":
            $requestedContentTypes = CONTENT_TYPE_SUPPORTED_XML;
            break;
        case "xhtml":
            $requestedContentTypes = CONTENT_TYPE_SUPPORTED_XHTML;
            break;
        case "json":
            $requestedContentTypes = CONTENT_TYPE_SUPPORTED_JSONCOLLECTION.",".CONTENT_TYPE_SUPPORTED_JSON;
            break;
        }
    }
    else
    {
        if (isset($_SERVER['HTTP_ACCEPT']) === true)
        {
            $requestedContentTypes = $_SERVER['HTTP_ACCEPT'];
        }
    }

    $mediaType = null;

    if (empty($requestedContentTypes) === false)
    {
        $negotiator = new \Negotiation\Negotiator();

        $mediaType = $negotiator->getBest($requestedContentTypes, $supportedContentTypes);

        if ($mediaType != null)
        {
            $mediaType = $mediaType->getValue();

        }
    }

    if ($mediaType == null)
    {
        http_response_code(406);
        echo CONTENT_TYPE_SUPPORTED_ACCEPTHEADERSUGGESTION;
        exit(-1);
    }

    define("CONTENT_TYPE_REQUESTED", $mediaType);
    header("Content-Type: ".CONTENT_TYPE_REQUESTED);
}



?>
