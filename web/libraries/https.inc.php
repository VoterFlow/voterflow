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
 * @file $/web/libraries/https.inc.php
 * @author Stephan Kreutzer
 * @since 2016-10-23
 */



$https = false;

if (isset($_SERVER['HTTPS']) === true)
{
    if ($_SERVER['HTTPS'] === "on")
    {
        $https = true;
    }
}

if ($https !== true)
{
    header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], true, 302);
    exit(-1);
}



?>
