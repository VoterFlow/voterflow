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
define("CONTENT_TYPE_SUPPORTED_JSON", "application/vnd.collection+json; charset=UTF-8; q=0.1");

// Just because PHP doesn't have local block scope...
function NegotiateContentType()
{
    $supportedContentTypes = array(CONTENT_TYPE_SUPPORTED_XML,
                                   CONTENT_TYPE_SUPPORTED_XHTML,
                                   CONTENT_TYPE_SUPPORTED_JSON);

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

    $mediaType = null;

    if (isset($_GET['format']) === true)
    {
        switch ($_GET['format'])
        {
        case "xml":
            $mediaType = CONTENT_TYPE_SUPPORTED_XML;
            break;
        case "xhtml":
            $mediaType = CONTENT_TYPE_SUPPORTED_XHTML;
            break;
        case "json":
            $mediaType = CONTENT_TYPE_SUPPORTED_JSON;
            break;
        }
    }

    if ($mediaType == null)
    {
        $negotiator = new \Negotiation\Negotiator();
        $requestedContentTypes = "";

        if (isset($_SERVER['HTTP_ACCEPT']) === true)
        {
            $requestedContentTypes = $_SERVER['HTTP_ACCEPT'];
        }

        if (empty($requestedContentTypes) === false)
        {
            $mediaType = $negotiator->getBest($requestedContentTypes, $supportedContentTypes);

            if ($mediaType != null)
            {
                $mediaType = $mediaType->getValue();
            }
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

NegotiateContentType();



?>
