<?php
/* Copyright (C) 2013-2017  Christian Huke, Stephan Kreutzer
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
 * @file $/web/install/install.php
 * @brief Installation routine to set up the system.
 * @author Christian Huke, Stephan Kreutzer
 * @since 2013-09-13
 */



require_once("../libraries/https.inc.php");

require_once("../libraries/languagelib.inc.php");
require_once(getLanguageFile("install"));



echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
     "<!DOCTYPE html\n".
     "    PUBLIC \"-//W3C//DTD XHTML 1.1//EN\"\n".
     "    \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n".
     "<html version=\"-//W3C//DTD XHTML 1.1//EN\" xmlns=\"http://www.w3.org/1999/xhtml\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://w3.org/MarkUp/SCHEMA/xhtml11.xsd\" xml:lang=\"".getCurrentLanguage()."\" lang=\"".getCurrentLanguage()."\">\n".
     "  <head>\n".
     "    <title>".LANG_PAGETITLE."</title>\n".
     "    <link rel=\"stylesheet\" type=\"text/css\" href=\"../mainstyle.css\"/>\n".
     "    <link rel=\"stylesheet\" type=\"text/css\" href=\"install.css\"/>\n".
     "    <meta http-equiv=\"content-type\" content=\"application/xhtml+xml; charset=UTF-8\"/>\n".
     "  </head>\n".
     "  <body>\n";


$step = 0;

if (isset($_POST['step']) === true)
{
    if (is_numeric($_POST['step']) === true)
    {
        $step = (int)$_POST['step'];

/*
        if ($step == 3 &&
            isset($_POST['retry']) === true)
        {
            // Special handling for step 2 (retry other database connection
            // settings after one connection was already established successfully).
            $step = 2;
        }
*/

        if ($step == 4 &&
            isset($_POST['init']) === true)
        {
            // Special handling for step 3 (redo database initialization after
            // initialization was already completed successfully).
            $step = 3;
        }
        else if ($step == 5 &&
                 isset($_POST['save']) === true)
        {
            // Special handling for step 4 (redo save of the configuration after
            // saving was already completed successfully).
            $step = 4;
        }
    }
}

if (isset($_GET['stepjump']) === true)
{
    if (is_numeric($_GET['stepjump']) === true)
    {
        $step = (int)$_GET['stepjump'];
    }
}


if ($step == 0)
{
    // Language selection only for the first step.
    require_once("../language_selector.inc.php");
    echo getHTMLLanguageSelector("install.php");

    echo "    <div class=\"mainbox\">\n".
         "      <div class=\"mainbox_header\">\n".
         "        <h1 class=\"mainbox_header_h1\">".LANG_STEP0_HEADER."</h1>\n".
         "      </div>\n".
         "      <div class=\"mainbox_body\">\n".
         "        <div>\n".
         "          ".LANG_STEP0_INTROTEXT."\n".
         "        </div>\n".
         "        <div>\n".
         "          <form action=\"install.php\" method=\"post\">\n".
         "            <fieldset>\n".
         "              <input type=\"hidden\" name=\"step\" value=\"1\"/>\n".
         "              <input type=\"submit\" value=\"".LANG_STEP0_PROCEEDTEXT."\" class=\"mainbox_proceed\"/>\n".
         "            </fieldset>\n".
         "          </form>\n".
         "        </div>\n".
         "      </div>\n".
         "    </div>\n";
}
else if ($step == 1)
{
    echo "    <div class=\"mainbox\">\n".
         "      <div class=\"mainbox_header\">\n".
         "        <h1 class=\"mainbox_header_h1\">".LANG_STEP1_HEADER."</h1>\n".
         "      </div>\n".
         "      <div class=\"mainbox_body\">\n";

    require_once("../license.inc.php");
    echo getHTMLLicenseNotification("license");
    echo "<hr/>\n";
    echo getHTMLLicenseFull("license");

    echo "        <div>\n".
         "          <form action=\"install.php\" method=\"post\">\n".
         "            <fieldset>\n".
         "              <input type=\"hidden\" name=\"step\" value=\"2\"/>\n".
         "              <input type=\"submit\" value=\"".LANG_STEP1_PROCEEDTEXT."\" class=\"mainbox_proceed\"/>\n".
         "            </fieldset>\n".
         "          </form>\n".
         "        </div>\n".
         "      </div>\n".
         "    </div>\n";
}
else if ($step == 2)
{
    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "voterflow";
    $prefix = "";

    if (isset($_POST['host']) === true)
    {
        $host = $_POST['host'];
    }

    if (isset($_POST['username']) === true)
    {
        $username = $_POST['username'];
    }

    if (isset($_POST['password']) === true)
    {
        $password = $_POST['password'];
    }

    if (isset($_POST['database']) === true)
    {
        $database = $_POST['database'];
    }

    if (isset($_POST['prefix']) === true)
    {
        $prefix = $_POST['prefix'];
    }

    echo "    <div class=\"mainbox\">\n".
         "      <div class=\"mainbox_header\">\n".
         "        <h1 class=\"mainbox_header_h1\">".LANG_STEP2_HEADER."</h1>\n".
         "      </div>\n".
         "      <div class=\"mainbox_body\">\n".
         "        <p>\n".
         "          ".LANG_STEP2_REQUIREMENTS."\n".
         "        </p>\n";

    if (file_exists("../libraries/database_connect.inc.php") !== true)
    {
        $file = @fopen("../libraries/database_connect.inc.php", "w");

        if ($file != false)
        {
            @fclose($file);
        }
        else
        {
            echo "        <p>\n".
                 "          <span class=\"error\">".LANG_STEP2_DATABASECONNECTFILECREATEFAILED."</span>\n".
                 "        </p>\n";
        }
    }

    if (is_writable("../libraries/database_connect.inc.php") === true)
    {
        echo "        <p>\n".
             "          <span class=\"success\">".LANG_STEP2_DATABASECONNECTFILEISWRITABLE."</span>\n".
             "        </p>\n";

        /**
         * @todo Make sure that strings in $host, $database, ... don't contain characters
         *     that break PHP or SQL.
         */

        $php_code = "<?php\n".
                    "// This file was automatically generated by the installation routine.\n".
                    "\n".
                    "\$pdo = false;\n".
                    "\$db_table_prefix = \"$prefix\"; // Prefix for database tables.\n".
                    "\$exceptionConnectFailure = NULL;\n".
                    "\n".
                    "\n".
                    "try\n".
                    "{\n".
                    "    \$pdo = @new PDO('mysql:host=".$host.";dbname=".$database.";charset=utf8', \"".$username."\", \"".$password."\", array(PDO::MYSQL_ATTR_INIT_COMMAND => \"SET NAMES utf8\"));\n".
                    "}\n".
                    "catch (PDOException \$ex)\n".
                    "{\n".
                    "    \$pdo = false;\n".
                    "    \$exceptionConnectFailure = \$ex;\n".
                    "}\n".
                    "\n".
                    "?>\n";

        $file = @fopen("../libraries/database_connect.inc.php", "wb");

        if ($file != false)
        {
            if (@fwrite($file, $php_code) != false)
            {
                echo "        <p>\n".
                     "          <span class=\"success\">".LANG_STEP2_DATABASECONNECTFILEWRITESUCCEEDED."</span>\n".
                     "        </p>\n";
            }
            else
            {
                echo "        <p>\n".
                     "          <span class=\"error\">".LANG_STEP2_DATABASECONNECTFILEWRITEFAILED."</span>\n".
                     "        </p>\n";
            }

            @fclose($file);
        }
        else
        {
            echo "        <p>\n".
                 "          <span class=\"error\">".LANG_STEP2_DATABASECONNECTFILEWRITABLEOPENFAILED."</span>\n".
                 "        </p>\n";
        }
    }
    else
    {
        echo "        <p>\n".
             "          <span class=\"error\">".LANG_STEP2_DATABASECONNECTFILEISNTWRITABLE."</span>\n".
             "        </p>\n";
    }


    $successConnect = false;

    clearstatcache();

    if (file_exists("../libraries/database_connect.inc.php") === true)
    {
        if (is_readable("../libraries/database_connect.inc.php") === true)
        {
            echo "        <p>\n".
                 "          <span class=\"success\">".LANG_STEP2_DATABASECONNECTFILEISREADABLE."</span>\n".
                 "        </p>\n";

            require_once("../libraries/database.inc.php");

            if (Database::Get()->IsConnected() === true)
            {
                $successConnect = true;

                echo "            <p>\n".
                     "              <span class=\"success\">".LANG_STEP2_DBCONNECTSUCCEEDED."</span>\n".
                     "            </p>\n";
            }
            else
            {
                if (strlen(Database::Get()->GetLastErrorMessage()) > 0)
                {
                    echo "        <p>\n".
                         "          <span class=\"error\">".LANG_STEP2_DBCONNECTFAILED." ".htmlspecialchars(Database::Get()->GetLastErrorMessage(), ENT_COMPAT | ENT_HTML401, "UTF-8")."</span>\n".
                         "        </p>\n";
                }
                else
                {
                    echo "        <p>\n".
                         "          <span class=\"error\">".LANG_STEP2_DBCONNECTFAILED." ".LANG_STEP2_DBCONNECTFAILEDNOERRORINFO."</span>\n".
                         "        </p>\n";
                }
            }
        }
        else
        {
            echo "        <p>\n".
                 "          <span class=\"error\">".LANG_STEP2_DATABASECONNECTFILEISNTREADABLE."</span>\n".
                 "        </p>\n";
        }
    }
    else
    {
        echo "        <p>\n".
             "          <span class=\"error\">".LANG_STEP2_DATABASECONNECTFILEDOESNTEXIST."</span>\n".
             "        </p>\n";
    }

    if (isset($_POST['save']) == false ||
        $successConnect == false)
    {
        echo "        <div>\n".
             "          <form action=\"install.php\" method=\"post\">\n".
             "            <fieldset>\n".
             "              <input type=\"hidden\" name=\"step\" value=\"2\"/>\n".
             "              <input type=\"text\" name=\"host\" value=\"".htmlspecialchars($host, ENT_COMPAT | ENT_HTML401, "UTF-8")."\"/> ".LANG_STEP2_HOSTDESCRIPTION."<br/>\n".
             "              <input type=\"text\" name=\"username\" value=\"".htmlspecialchars($username, ENT_COMPAT | ENT_HTML401, "UTF-8")."\"/> ".LANG_STEP2_USERNAMEDESCRIPTION."<br/>\n".
             "              <input type=\"password\" name=\"password\" value=\"".htmlspecialchars($password, ENT_COMPAT | ENT_HTML401, "UTF-8")."\"/> ".LANG_STEP2_PASSWORDDESCRIPTION."<br/>\n".
             "              <input type=\"text\" name=\"database\" value=\"".htmlspecialchars($database, ENT_COMPAT | ENT_HTML401, "UTF-8")."\"/> ".LANG_STEP2_DATABASENAMEDESCRIPTION."<br/>\n".
             "              <input type=\"text\" name=\"prefix\" value=\"".htmlspecialchars($prefix, ENT_COMPAT | ENT_HTML401, "UTF-8")."\"/> ".LANG_STEP2_TABLEPREFIXDESCRIPTION."<br/>\n".
             "              <input type=\"submit\" name=\"save\" value=\"".LANG_STEP2_SAVETEXT."\" class=\"mainbox_proceed\"/>\n".
             "            </fieldset>\n".
             "          </form>\n".
             "        </div>\n";
    }
    else
    {
        echo "        <div>\n".
             "          <fieldset>\n".
             "            <form action=\"install.php\" method=\"post\">\n".
             "              <input type=\"hidden\" name=\"step\" value=\"2\"/>\n".
             "              <input type=\"hidden\" name=\"host\" value=\"".htmlspecialchars($host, ENT_COMPAT | ENT_HTML401, "UTF-8")."\"/>\n".
             "              <input type=\"hidden\" name=\"username\" value=\"".htmlspecialchars($username, ENT_COMPAT | ENT_HTML401, "UTF-8")."\"/>\n".
             "              <input type=\"hidden\" name=\"password\" value=\"".htmlspecialchars($password, ENT_COMPAT | ENT_HTML401, "UTF-8")."\"/>\n".
             "              <input type=\"hidden\" name=\"database\" value=\"".htmlspecialchars($database, ENT_COMPAT | ENT_HTML401, "UTF-8")."\"/>\n".
             "              <input type=\"hidden\" name=\"prefix\" value=\"".htmlspecialchars($prefix, ENT_COMPAT | ENT_HTML401, "UTF-8")."\"/>\n".
             "              <input type=\"submit\" value=\"".LANG_STEP2_EDITTEXT."\" class=\"mainbox_proceed\"/>\n".
             "            </fieldset>\n".
             "          </form>\n".
             "        </div>\n".
             "        <div>\n".
             "          <form action=\"install.php\" method=\"post\">\n".
             "            <fieldset>\n".
             "              <input type=\"hidden\" name=\"step\" value=\"3\"/>\n".
             "              <input type=\"submit\" value=\"".LANG_STEP2_PROCEEDTEXT."\" class=\"mainbox_proceed\"/>\n".
             "            </fieldset>\n".
             "          </form>\n".
             "        </div>\n";
    }

    echo "      </div>\n".
         "    </div>\n";
}
else if ($step == 3)
{
    $dropExistingTables = false;
    $keepExistingTables = false;

    if (isset($_POST['drop_existing_tables']) === true)
    {
        $dropExistingTables = true;
    }

    if (isset($_POST['keep_existing_tables']) === true)
    {
        $keepExistingTables = true;
    }


    echo "    <div class=\"mainbox\">\n".
         "      <div class=\"mainbox_header\">\n".
         "        <h1 class=\"mainbox_header_h1\">".LANG_STEP3_HEADER."</h1>\n".
         "      </div>\n".
         "      <div class=\"mainbox_body\">\n".
         "        <p>\n".
         "          ".LANG_STEP3_INITIALIZATIONDESCRIPTION."\n".
         "        </p>\n";


    $successInit = false;

    if (isset($_POST['init']) === true)
    {
        require_once("../libraries/database.inc.php");

        if (Database::Get()->IsConnected() === true)
        {
            $success = Database::Get()->BeginTransaction();

            // Table members

            if ($success === true)
            {
                if ($dropExistingTables === true)
                {
                    if (Database::Get()->ExecuteUnsecure("DROP TABLE IF EXISTS `".Database::Get()->GetPrefix()."members`") !== true)
                    {
                        $success = false;
                    }
                }
            }

            if ($success === true)
            {
                $sql = "CREATE TABLE ";

                if ($keepExistingTables === true)
                {
                    $sql .= "IF NOT EXISTS ";
                }

                $sql .= "`".Database::Get()->GetPrefix()."members` (".
                        "  `id` int(11) NOT NULL AUTO_INCREMENT,".
                        "  `name` varchar(40) COLLATE utf8_bin NOT NULL,".
                        "  `e_mail` varchar(255) COLLATE utf8_bin NOT NULL,".
                        "  `salt` varchar(255) COLLATE utf8_bin NOT NULL,".
                        "  `password` varchar(255) COLLATE utf8_bin NOT NULL,".
                        "  `role` int(11) NOT NULL,".
                        "  `last_login` datetime,".
                        "  PRIMARY KEY (`id`),".
                        "  UNIQUE KEY `name` (`name`)".
                        ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin";

                if (Database::Get()->ExecuteUnsecure($sql) !== true)
                {
                    $success = false;
                }
            }

            // Table votes

            if ($success === true)
            {
                if ($dropExistingTables === true)
                {
                    if (Database::Get()->ExecuteUnsecure("DROP TABLE IF EXISTS `".Database::Get()->GetPrefix()."votes`") !== true)
                    {
                        $success = false;
                    }
                }
            }

            if ($success === true)
            {
                $sql = "CREATE TABLE ";

                if ($keepExistingTables === true)
                {
                    $sql .= "IF NOT EXISTS ";
                }

                $sql .= "`".Database::Get()->GetPrefix()."votes` (".
                        "  `id` int(11) NOT NULL AUTO_INCREMENT,".
                        "  `handle` varchar(255) COLLATE utf8_bin NOT NULL,".
                        "  `type` int(11) NOT NULL,".
                        "  `name` varchar(255) COLLATE utf8_bin NOT NULL,".
                        "  `description` text COLLATE utf8_bin NOT NULL,".
                        "  `datetime_created` datetime NOT NULL,".
                        "  PRIMARY KEY (`id`),".
                        "  UNIQUE KEY (`handle`)".
                        ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin";

                if (Database::Get()->ExecuteUnsecure($sql) !== true)
                {
                    $success = false;
                }
            }

            // Table voters

            if ($success === true)
            {
                if ($dropExistingTables === true)
                {
                    if (Database::Get()->ExecuteUnsecure("DROP TABLE IF EXISTS `".Database::Get()->GetPrefix()."voters`") !== true)
                    {
                        $success = false;
                    }
                }
            }

            if ($success === true)
            {
                $sql = "CREATE TABLE ";

                if ($keepExistingTables === true)
                {
                    $sql .= "IF NOT EXISTS ";
                }

                $sql .= "`".Database::Get()->GetPrefix()."voters` (".
                        "  `id` int(11) NOT NULL AUTO_INCREMENT,".
                        "  `handle` varchar(255) COLLATE utf8_bin NOT NULL,".
                        "  `name` varchar(255) COLLATE utf8_bin NOT NULL,".
                        "  `handle_vote` varchar(255) COLLATE utf8_bin NOT NULL,".
                        "  PRIMARY KEY (`id`),".
                        "  UNIQUE KEY (`handle`)".
                        ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin";

                if (Database::Get()->ExecuteUnsecure($sql) !== true)
                {
                    $success = false;
                }
            }

            // Table vote_options

            if ($success === true)
            {
                if ($dropExistingTables === true)
                {
                    if (Database::Get()->ExecuteUnsecure("DROP TABLE IF EXISTS `".Database::Get()->GetPrefix()."vote_options`") !== true)
                    {
                        $success = false;
                    }
                }
            }

            if ($success === true)
            {
                $sql = "CREATE TABLE ";

                if ($keepExistingTables === true)
                {
                    $sql .= "IF NOT EXISTS ";
                }

                $sql .= "`".Database::Get()->GetPrefix()."vote_options` (".
                        "  `id` int(11) NOT NULL AUTO_INCREMENT,".
                        "  `position` int(11) NOT NULL,".
                        "  `caption` varchar(255) COLLATE utf8_bin NOT NULL,".
                        "  `handle_vote` varchar(255) NOT NULL,".
                        "  PRIMARY KEY (`id`)".
                        ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin";

                if (Database::Get()->ExecuteUnsecure($sql) !== true)
                {
                    $success = false;
                }
            }


            // Table casts

            if ($success === true)
            {
                if ($dropExistingTables === true)
                {
                    if (Database::Get()->ExecuteUnsecure("DROP TABLE IF EXISTS `".Database::Get()->GetPrefix()."casts`") !== true)
                    {
                        $success = false;
                    }
                }
            }

            if ($success === true)
            {
                $sql = "CREATE TABLE ";

                if ($keepExistingTables === true)
                {
                    $sql .= "IF NOT EXISTS ";
                }

                $sql .= "`".Database::Get()->GetPrefix()."casts` (".
                        "  `id` int(11) NOT NULL AUTO_INCREMENT,".
                        "  `handle_vote` varchar(255) COLLATE utf8_bin NOT NULL,".
                        "  `handle_voter` varchar(255) COLLATE utf8_bin NOT NULL,".
                        "  `id_vote_option` int(11) NOT NULL,".
                        "  PRIMARY KEY (`id`)".
                        ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin";

                if (Database::Get()->ExecuteUnsecure($sql) !== true)
                {
                    $success = false;
                }
            }

            // Table logs

            if ($success === true)
            {
                if ($dropExistingTables === true)
                {
                    if (Database::Get()->ExecuteUnsecure("DROP TABLE IF EXISTS `".Database::Get()->GetPrefix()."logs`") !== true)
                    {
                        $success = false;
                    }
                }
            }

            if ($success === true)
            {
                $sql = "CREATE TABLE ";

                if ($keepExistingTables === true)
                {
                    $sql .= "IF NOT EXISTS ";
                }

                $sql .= "`".Database::Get()->GetPrefix()."logs` (".
                        "  `id` int(11) NOT NULL AUTO_INCREMENT,".
                        "  `datetime` datetime NOT NULL,".
                        "  `text` text COLLATE utf8_bin NOT NULL,".
                        "  `id_member` int(11) NOT NULL,".
                        "  PRIMARY KEY (`id`)".
                        ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin";

                if (Database::Get()->ExecuteUnsecure($sql) !== true)
                {
                    $success = false;
                }
            }


            if ($success === true)
            {
                if (Database::Get()->commitTransaction() === true)
                {
                    echo "        <p>\n".
                         "          <span class=\"success\">".LANG_STEP3_DBOPERATIONSUCCEEDED."</span>\n".
                         "        </p>\n";

                    $successInit = true;
                }
                else
                {
                    echo "        <p>\n".
                         "          <span class=\"error\">".LANG_STEP3_DBCOMMITFAILED."</span>\n".
                         "        </p>\n";
                }
            }
            else
            {
                if (strlen(Database::Get()->GetLastErrorMessage()) > 0)
                {
                    echo "        <p>\n".
                         "          <span class=\"error\">".LANG_STEP3_DBOPERATIONFAILED." ".htmlspecialchars(Database::Get()->GetLastErrorMessage(), ENT_COMPAT | ENT_HTML401, "UTF-8")."</span>\n".
                         "        </p>\n";
                }
                else
                {
                    echo "        <p>\n".
                         "          <span class=\"error\">".LANG_STEP3_DBOPERATIONFAILED." ".LANG_STEP3_DBOPERATIONFAILEDNOERRORINFO."</span>\n".
                         "        </p>\n";
                }

                Database::Get()->RollbackTransaction();
            }
        }
        else
        {
            if (strlen(Database::Get()->GetLastErrorMessage()) > 0)
            {
                echo "        <p>\n".
                     "          <span class=\"error\">".LANG_STEP3_DBCONNECTFAILED." ".htmlspecialchars(Database::Get()->GetLastErrorMessage(), ENT_COMPAT | ENT_HTML401, "UTF-8")."</span>\n".
                     "        </p>\n";
            }
            else
            {
                echo "        <p>\n".
                     "          <span class=\"error\">".LANG_STEP3_DBCONNECTFAILED." ".LANG_STEP3_DBCONNECTFAILEDNOERRORINFO."</span>\n".
                     "        </p>\n";
            }
        }
    }

    echo "        <div>\n".
         "          <form action=\"install.php\" method=\"post\">\n".
         "            <fieldset>\n";

    if ($successInit === true)
    {
        echo "              <input type=\"hidden\" name=\"step\" value=\"4\"/>\n";
    }
    else
    {
        echo "              <input type=\"hidden\" name=\"step\" value=\"3\"/>\n";
    }

    if ($dropExistingTables === true)
    {
        echo "              <input type=\"checkbox\" name=\"drop_existing_tables\" value=\"drop\" checked=\"checked\"/> ".LANG_STEP3_CHECKBOXDESCRIPTIONDROPEXISTINGTABLES."<br/>\n";
    }
    else
    {
        echo "              <input type=\"checkbox\" name=\"drop_existing_tables\" value=\"drop\"/> ".LANG_STEP3_CHECKBOXDESCRIPTIONDROPEXISTINGTABLES."<br/>\n";
    }

    if ($keepExistingTables === true)
    {
        echo "              <input type=\"checkbox\" name=\"keep_existing_tables\" value=\"keep\" checked=\"checked\"/> ".LANG_STEP3_CHECKBOXDESCRIPTIONKEEPEXISTINGTABLES."<br/>\n";
    }
    else
    {
        echo "              <input type=\"checkbox\" name=\"keep_existing_tables\" value=\"keep\"/> ".LANG_STEP3_CHECKBOXDESCRIPTIONKEEPEXISTINGTABLES."<br/>\n";
    }

    echo "              <input type=\"submit\" name=\"init\" value=\"".LANG_STEP3_INITIALIZETEXT."\" class=\"mainbox_proceed\"/>\n";

    if ($successInit === true)
    {
        echo "              <input type=\"submit\" value=\"".LANG_STEP3_COMPLETETEXT."\" class=\"mainbox_proceed\"/>\n";
    }

    echo "            </fieldset>\n".
         "          </form>\n".
         "        </div>\n".
         "      </div>\n".
         "    </div>\n";
}
else if ($step == 4)
{
    $adminName = "";
    $adminPassword = "";
    $adminEMail = "";

    if (isset($_POST['adminname']) === true)
    {
        $adminName = $_POST['adminname'];
    }

    if (isset($_POST['password']) === true)
    {
        $adminPassword = $_POST['password'];
    }

    if (isset($_POST['email']) === true)
    {
        $adminEMail = $_POST['email'];
    }

    echo "    <div class=\"mainbox\">\n".
         "      <div class=\"mainbox_header\">\n".
         "        <h1 class=\"mainbox_header_h1\">".LANG_STEP4_HEADER."</h1>\n".
         "      </div>\n".
         "      <div class=\"mainbox_body\">\n";

    $successCreate = false;

    if (isset($_POST['save']) === true &&
        !empty($adminName) &&
        !empty($adminPassword) &&
        !empty($adminEMail))
    {
        require_once(dirname(__FILE__)."/../libraries/member_management.inc.php");

        $successDelete = false;

        if (Database::Get()->IsConnected() === true)
        {
            $successDelete = Database::Get()->ExecuteUnsecure("DELETE FROM `".Database::Get()->GetPrefix()."members` WHERE 1");
        }

        if ($successDelete === true)
        {
            $id = InsertNewMember($adminName, $adminPassword, $adminEMail, MEMBER_ROLE_ADMIN);

            if ($id > 0)
            {
                $member = array("id" => $id);
                $successCreate = true;
            }
        }

        if ($successDelete != true ||
            $successCreate != true)
        {
            echo "        <p>\n".
                 "          <span class=\"error\">".LANG_STEP4_DBOPERATIONFAILED."</span>\n".
                 "        </p>\n".
                 "      </div>\n".
                 "    </div>\n".
                 "  </body>\n".
                 "</html>\n";

            exit();
        }
    }

    if ($successCreate == false)
    {
        // If $successCreate !== true because of a failed database operation, the
        // execution will be aborted above. Therefore, this part is only executed
        // if the step 4 is called without $_POST['save'] (= first time).

        echo "        <p>\n".
             "          ".LANG_STEP4_ADMININITIALIZATIONDESCRIPTION."\n".
             "        </p>\n";
    }
    else
    {
        echo "        <p>\n".
             "          <span class=\"success\">".LANG_STEP4_DBOPERATIONSUCCEEDED."</span>\n".
             "        </p>\n";
    }

    echo "        <div>\n".
         "          <form action=\"install.php\" method=\"post\">\n".
         "            <fieldset>\n".
         "              <input type=\"hidden\" name=\"step\" value=\"5\"/>\n".
         "              <input type=\"text\" name=\"adminname\" value=\"".htmlspecialchars($adminName, ENT_COMPAT | ENT_HTML401, "UTF-8")."\" size=\"20\" maxlength=\"60\"//> ".LANG_STEP4_ADMINNAMEDESCRIPTION."<br/>\n".
         "              <input type=\"password\" name=\"password\" value=\"".htmlspecialchars($adminPassword, ENT_COMPAT | ENT_HTML401, "UTF-8")."\" size=\"20\" maxlength=\"60\"/> ".LANG_STEP4_PASSWORDDESCRIPTION."<br/>\n".
         "              <input type=\"text\" name=\"email\" value=\"".htmlspecialchars($adminEMail, ENT_COMPAT | ENT_HTML401, "UTF-8")."\" size=\"20\" maxlength=\"255\"/> ".LANG_STEP4_EMAILDESCRIPTION."<br/>\n".
         "              <input type=\"submit\" name=\"save\" value=\"".LANG_STEP4_BUTTONSAVECAPTION."\" class=\"mainbox_proceed\"/>\n";

    if ($successCreate === true)
    {
        echo "              <input type=\"submit\" value=\"".LANG_STEP4_PROCEEDTEXT."\" class=\"mainbox_proceed\"/>\n";
    }

    echo "            </fieldset>\n".
         "          </form>\n".
         "        </div>\n".
         "      </div>\n".
         "    </div>\n";
}
else if ($step == 5)
{
    echo "    <div class=\"mainbox\">\n".
         "      <div class=\"mainbox_header\">\n".
         "        <h1 class=\"mainbox_header_h1\">".LANG_STEP5_HEADER."</h1>\n".
         "      </div>\n".
         "      <div class=\"mainbox_body\">\n".
         "        <p>\n".
         "          ".LANG_STEP5_COMPLETETEXT."\n".
         "        </p>\n".
         "        <div>\n".
         "          <form action=\"../member_index.php\" method=\"post\">\n".
         "            <fieldset>\n".
         "              <input type=\"hidden\" name=\"install_done\" value=\"install_done\"/>\n".
         "              <input type=\"submit\" value=\"".LANG_STEP5_EXITTEXT."\" class=\"mainbox_proceed\"/>\n".
         "            </fieldset>\n".
         "          </form>\n".
         "        </div>\n".
         "      </div>\n".
         "    </div>\n";
}

echo "  </body>\n".
     "</html>\n";



?>
