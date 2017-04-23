<?php
/* Copyright (C) 2017 Stephan Kreutzer
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
 * @file $/web/libraries/session.inc.php
 * @author Stephan Kreutzer
 * @since 2017-04-23
 */



if (empty($_SESSION) === true)
{
    if (@session_start() !== true)
    {
        http_response_code(403);
        exit(-1);
    }
}

if (isset($_SESSION['member_id']) !== true)
{
    http_response_code(403);
    exit(-1);
}

if (isset($_SESSION['member_role']) !== true)
{
    http_response_code(403);
    exit(-1);
}

if (isset($_SESSION['instance_path']) !== true)
{
    http_response_code(500);
    exit(-1);
}

if (dirname(__FILE__) !== $_SESSION['instance_path']."/libraries")
{
    http_response_code(403);
    exit(-1);
}



?>
