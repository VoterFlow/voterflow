<?php
/* Copyright (C) 2012-2017  Stephan Kreutzer
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
 * @file $/web/libraries/member_management.inc.php
 * @author Stephan Kreutzer
 * @since 2012-06-02
 */



require_once(dirname(__FILE__)."/database.inc.php");
require_once(dirname(__FILE__)."/member_defines.inc.php");



function InsertNewMember($name, $password, $email, $role)
{
    /** @todo Check for empty $name, $password, $email or $role. */

    if (Database::Get()->IsConnected() !== true)
    {
        return -1;
    }

    /** @todo Why is this needed? Only one statement in transaction? */
    if (Database::Get()->BeginTransaction() !== true)
    {
        return -2;
    }

    $salt = md5(uniqid(rand(), true));
    $password = hash('sha512', $salt.$password);

    $id = Database::Get()->Insert("INSERT INTO `".Database::Get()->GetPrefix()."members` (`id`,\n".
                                  "    `name`,\n".
                                  "    `e_mail`,\n".
                                  "    `salt`,\n".
                                  "    `password`,\n".
                                  "    `role`,\n".
                                  "    `last_login`)\n".
                                  "VALUES (?, ?, ?, ?, ?, ?, ?)\n",
                                  array(NULL, $name, $email, $salt, $password, $role, NULL),
                                  array(Database::TYPE_NULL, Database::TYPE_STRING, Database::TYPE_STRING, Database::TYPE_STRING, Database::TYPE_STRING, Database::TYPE_INT, Database::TYPE_NULL));

    if ($id <= 0)
    {
        Database::Get()->RollbackTransaction();
        return -4;
    }

    if (Database::Get()->CommitTransaction() === true)
    {
        return $id;
    }

    return -7;
}

function GetMemberByName($name)
{
    /** @todo Check for empty $name, $password, $email or $role. */

    if (Database::Get()->IsConnected() !== true)
    {
        return -1;
    }

    $member = Database::Get()->Query("SELECT `id`,\n".
                                     "    `salt`,\n".
                                     "    `password`,\n".
                                     "    `role`,\n".
                                     "    `last_login`\n".
                                     "FROM `".Database::Get()->GetPrefix()."members`\n".
                                     "WHERE `name` LIKE ?\n",
                                     array($name),
                                     array(Database::TYPE_STRING));

    if (is_array($member) !== true)
    {
        return -2;
    }

    return $member;
}

function GetMembers()
{
    /** @todo Check for empty $name, $password, $email or $role. */

    if (Database::Get()->IsConnected() !== true)
    {
        return -1;
    }

    $members = Database::Get()->QueryUnsecure("SELECT `id`,\n".
                                              "    `name`,\n".
                                              "    `role`,\n".
                                              "    `last_login`\n".
                                              "FROM `".Database::Get()->GetPrefix()."members`\n".
                                              "WHERE 1\n");

    if (is_array($members) !== true)
    {
        return -2;
    }

    return $members;
}



?>
