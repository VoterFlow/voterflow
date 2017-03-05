<?php
/* Copyright (C) 2016-2017  Stephan Kreutzer
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
 * @file $/web/libraries/logging.inc.php
 * @author Stephan Kreutzer
 * @since 2016-11-22
 */



function logEvent($text)
{
    $memberId = 0;

    if (isset($_SESSION['member_id']) === true)
    {
        $memberId = (int)$_SESSION['member_id'];
    }

    require_once(dirname(__FILE__)."/database.inc.php");

    if (Database::Get()->IsConnected() !== true)
    {
        return -1;
    }

    $id = Database::Get()->Insert("INSERT INTO `".Database::Get()->GetPrefix()."logs` (`id`,\n".
                                  "    `datetime`,\n".
                                  "    `text`,\n".
                                  "    `id_member`)\n".
                                  "VALUES (?, NOW(), ?, ?)\n",
                                  array(NULL, $text, $memberId),
                                  array(Database::TYPE_NULL, Database::TYPE_STRING, Database::TYPE_INT));

    if ($id <= 0)
    {
        return -2;
    }

    return 0;
}



?>
