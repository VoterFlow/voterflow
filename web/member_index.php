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
 * @file $/web/member_index.php
 * @brief Login and main page for members.
 * @author Stephan Kreutzer
 * @since 2012-06-01
 */



require_once("./libraries/https.inc.php");

if (empty($_SESSION) === true)
{
    @session_start();
}

if (isset($_POST['logout']) === true &&
    isset($_SESSION['member_id']) === true)
{
    $language = null;

    if (isset($_SESSION['language']) === true)
    {
        $language = $_SESSION['language'];
    }

    $_SESSION = array();

    if ($language != null)
    {
        $_SESSION['language'] = $language;
    }
    else
    {
        if (isset($_COOKIE[session_name()]) == true)
        {
            setcookie(session_name(), '', time()-42000, '/');
        }
    }
}



require_once("./libraries/languagelib.inc.php");
require_once(getLanguageFile("member_index"));
require_once("./language_selector.inc.php");

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
     "<!DOCTYPE html\n".
     "    PUBLIC \"-//W3C//DTD XHTML 1.1//EN\"\n".
     "    \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n".
     "<html version=\"-//W3C//DTD XHTML 1.1//EN\" xmlns=\"http://www.w3.org/1999/xhtml\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://www.w3.org/1999/xhtml http://www.w3.org/MarkUp/SCHEMA/xhtml11.xsd\" xml:lang=\"".getCurrentLanguage()."\" lang=\"".getCurrentLanguage()."\">\n".
     "    <head>\n".
     "        <meta http-equiv=\"content-type\" content=\"application/xhtml+xml; charset=UTF-8\"/>\n".
     "        <title>".LANG_PAGETITLE."</title>\n".
     "        <link rel=\"stylesheet\" type=\"text/css\" href=\"mainstyle.css\"/>\n".
     "    </head>\n".
     "    <body>\n";

if (isset($_POST['name']) !== true ||
    isset($_POST['passwort']) !== true)
{
    require_once("./language_selector.inc.php");
    echo getHTMLLanguageSelector("member_index.php");

    echo "        <div class=\"mainbox\">\n".
         "          <div class=\"mainbox_header\">\n".
         "            <h1 class=\"mainbox_header_h1\">".LANG_HEADER."</h1>\n".
         "          </div>\n".
         "          <div class=\"mainbox_body\">\n";

    if (isset($_POST['install_done']) == true)
    {
        if (@unlink(dirname(__FILE__)."/install/install.php") === true)
        {
            clearstatcache();
        }
        else
        {
            echo "            <p class=\"error\">\n".
                 "              ".LANG_INSTALLDELETEFAILED."\n".
                 "            </p>\n";
        }
    }

    if (file_exists("./install/install.php") === true &&
        isset($_GET['skipinstall']) != true)
    {
        echo "            <form action=\"install/install.php\" method=\"post\" class=\"installbutton_form\">\n".
             "              <fieldset>\n".
             "                <input type=\"submit\" value=\"".LANG_INSTALLBUTTON."\"/><br/>\n".
             "              </fieldset>\n".
             "            </form>\n";

        require_once("./license.inc.php");
        echo getHTMLLicenseNotification("license");
    }
    else
    {
        require_once("./libraries/member_management.inc.php");

        if (isset($_SESSION['member_id']) === true)
        {
            /** @todo Links. */

            echo "            <form action=\"member_index.php\" method=\"post\">\n".
                 "              <fieldset>\n".
                 "                <input type=\"submit\" name=\"logout\" value=\"".LANG_BUTTON_LOGOUT."\"/><br/>\n".
                 "              </fieldset>\n".
                 "            </form>\n";
        }
        else
        {
            echo "            <p>\n".
                 "              ".LANG_WELCOMETEXT."\n".
                 "            </p>\n".
                 "            <p>\n".
                 "              ".LANG_LOGINDESCRIPTION."\n".
                 "            </p>\n".
                 "            <form action=\"member_index.php\" method=\"post\">\n".
                 "              <fieldset>\n".
                 "                <input name=\"name\" type=\"text\" size=\"20\" maxlength=\"60\"/> ".LANG_NAMEFIELD_CAPTION."<br />\n".
                 "                <input name=\"passwort\" type=\"password\" size=\"20\" maxlength=\"60\"/> ".LANG_PASSWORDFIELD_CAPTION."<br />\n".
                 "                <input type=\"submit\" value=\"".LANG_SUBMITBUTTON."\"/><br/>\n".
                 "              </fieldset>\n".
                 "            </form>\n";

            require_once("./license.inc.php");
            echo getHTMLLicenseNotification("license");
        }
    }

    echo "          </div>\n".
         "        </div>\n".
         "        <div class=\"footerbox\">\n".
         "          <a href=\"license.php\" class=\"footerbox_link\">".LANG_LICENSE."</a>\n".
         "        </div>\n".
         "    </body>\n".
         "</html>\n".
         "\n";
}
else
{
    require_once("./libraries/member_management.inc.php");

    $member = NULL;

    $result = GetMemberByName($_POST['name']);

    if (is_array($result) !== true)
    {
        echo "        <div class=\"mainbox\">\n".
             "          <div class=\"mainbox_body\">\n".
             "            <p class=\"error\">\n".
             "              ".LANG_DBCONNECTFAILED."\n".
             "            </p>\n".
             "          </div>\n".
             "        </div>\n".
             "        <div class=\"footerbox\">\n".
             "          <a href=\"license.php\" class=\"footerbox_link\">".LANG_LICENSE."</a>\n".
             "        </div>\n".
             "    </body>\n".
             "</html>\n";

        exit(-1);
    }


    if (count($result) === 0)
    {
        echo "        <div class=\"mainbox\">\n".
             "          <div class=\"mainbox_body\">\n".
             "            <p class=\"error\">\n".
             "              ".LANG_LOGINFAILED."\n".
             "            </p>\n".
             "            <a href=\"member_index.php\">".LANG_LINKCAPTION_RETRYLOGIN."</a>\n".
             "          </div>\n".
             "        </div>\n".
             "        <div class=\"footerbox\">\n".
             "          <a href=\"license.php\" class=\"footerbox_link\">".LANG_LICENSE."</a>\n".
             "        </div>\n".
             "    </body>\n".
             "</html>\n";

        exit(0);
    }
    else
    {
        // The member does exist, he wants to log in.

        if ($result[0]['password'] === hash('sha512', $result[0]['salt'].$_POST['passwort']))
        {
            $member = array("id" => (int)$result[0]['id'],
                            "role" => (int)$result[0]['role'],
                            "last_login" => $result[0]['last_login']);
        }
        else
        {
            echo "        <div class=\"mainbox\">\n".
                 "          <div class=\"mainbox_body\">\n".
                 "            <p class=\"error\">\n".
                 "              ".LANG_LOGINFAILED."\n".
                 "            </p>\n".
                 "            <a href=\"member_index.php\">".LANG_LINKCAPTION_RETRYLOGIN."</a>\n".
                 "          </div>\n".
                 "        </div>\n".
                 "        <div class=\"footerbox\">\n".
                 "          <a href=\"license.php\" class=\"footerbox_link\">".LANG_LICENSE."</a>\n".
                 "        </div>\n".
                 "    </body>\n".
                 "</html>\n";

            exit(0);
        }
    }

    if (is_array($member) === true)
    {
        $language = null;

        if (isset($_SESSION['language']) === true)
        {
            $language = $_SESSION['language'];
        }

        $_SESSION = array();

        if ($language != null)
        {
            $_SESSION['language'] = $language;
        }

        $_SESSION['instance_path'] = dirname(__FILE__);
        $_SESSION['member_id'] = $member['id'];
        $_SESSION['member_name'] = $_POST['name'];
        $_SESSION['member_role'] = $member['role'];

        require_once("./libraries/database.inc.php");

        if (Database::Get()->IsConnected() === true)
        {
            Database::Get()->ExecuteUnsecure("UPDATE `".Database::Get()->GetPrefix()."members`\n".
                                             "SET `last_login`=NOW()\n".
                                             "WHERE `id`=".$member['id']);
        }

        echo "        <div class=\"mainbox\">\n".
             "          <div class=\"mainbox_body\">\n".
             "            <p class=\"success\">\n".
             "              ".LANG_LOGINSUCCESS."\n".
             "            </p>\n";

        if (!($member['last_login'] == null))
        {
            echo "            <p>\n".
                 "              ".LANG_LASTLOGIN_PRE.$member['last_login'].LANG_LASTLOGIN_POST."\n".
                 "            </p>\n";
        }

        echo "            <a href=\"member_index.php\">".LANG_LINKCAPTION_CONTINUE."</a>\n".
             "          </div>\n".
             "        </div>\n".
             "        <div class=\"footerbox\">\n".
             "          <a href=\"license.php\" class=\"footerbox_link\">".LANG_LICENSE."</a>\n".
             "        </div>\n";
    }

    echo "    </body>\n".
         "</html>\n";
}


?>
