/* Copyright (C) 2012-2017 Stephan Kreutzer
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

var xmlhttp = null;

// Mozilla
if (window.XMLHttpRequest)
{
    xmlhttp = new XMLHttpRequest();
}
// IE
else if (window.ActiveXObject)
{
    xmlhttp = new ActiveXObject('Microsoft.XMLHTTP');
}

var basePath = "";

{
    var scripts = document.getElementsByTagName("script");
    // 'scripts' contains always the last script tag that was loaded.
    var myPath = scripts[scripts.length-1].src;

    basePath = myPath.substring(0, myPath.lastIndexOf('/'));
}

// 'file://' is bad.
if (basePath.substring(0, 7) == "file://")
{
    basePath = basePath.substr(8);
    basePath = "http://" + basePath;
}

function requestVote(handle, handle_voter)
{
    if (xmlhttp == null)
    {
        return;
    }

    {
        xmlhttp.open('GET', basePath + "/../../api/vote.php?handle=" + handle, true);
        xmlhttp.setRequestHeader('Accept',
                                 'application/xml');
        xmlhttp.onreadystatechange = function() { resultVote(handle, handle_voter); };
        xmlhttp.send();
    }
}

function resultVote(handle, handle_voter)
{
    if (xmlhttp.readyState != 4)
    {
        // Waiting...
    }

    if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
    {
        if (xmlhttp.responseText == '')
        {
            return;
        }

        var dom = xmlhttp.responseXML.documentElement;
        var name = dom.getElementsByTagName('name').item(0).firstChild.data;
        var description = dom.getElementsByTagName('description').item(0).firstChild.data;

        if (name == null ||
            description == null)
        {
            return -1;
        }

        document.getElementById('vote-name').innerHTML = name;
        document.getElementById('vote-description').innerHTML = description;

        var optionsElement = dom.getElementsByTagName('vote-options').item(0);

        if (optionsElement == null)
        {
            return -1;
        }

        var optionLink = optionsElement.getAttribute("xlink:href");

        if (optionLink == null ||
            xmlhttp == null)
        {
            return;
        }

        xmlhttp.open('GET', basePath + "/../../api/vote_options.php?handle=" + handle, true);
        xmlhttp.setRequestHeader('Accept',
                                 'application/xml');
        xmlhttp.onreadystatechange = function() { resultVoteOptions(handle, handle_voter); };
        xmlhttp.send();
    }
}

function resultVoteOptions(handle, handle_voter)
{
    if (xmlhttp.readyState != 4)
    {
        // Waiting...
    }

    if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
    {
        if (xmlhttp.responseText == '')
        {
            return -1;
        }

        var dom = xmlhttp.responseXML.documentElement;
        var options = dom.getElementsByTagName('option');

        if (options == null)
        {
            return -1;
        }

        var optionsElement = document.getElementById('vote-options');

        if (optionsElement == null)
        {
            return -1;
        }

        for (var i = 0; i < options.length; i++)
        {
            optionsElement.innerHTML += "<div class=\"option\"><a href=\"#\" onclick=\"requestCast('" + handle + "', '" + handle_voter + "', " + options.item(i).getElementsByTagName('id').item(0).textContent + ");\">" + options.item(i).getElementsByTagName('caption').item(0).textContent + "</a></div>";
        }
    }
}

function requestCast(handle, handle_voter, option)
{
    if (xmlhttp == null)
    {
        return;
    }

    {
        xmlhttp.open('POST', basePath + "/../../api/casts.php?handle=" + handle, true);
        xmlhttp.setRequestHeader('Content-Type',
                                 'application/x-www-form-urlencoded');
        xmlhttp.setRequestHeader('Accept',
                                 'application/xml');
        xmlhttp.onreadystatechange = resultCast;
        xmlhttp.send('handle_voter=' + encodeURIComponent(handle_voter) + '&' +
                     'id_vote_option=' + encodeURIComponent(option));
    }
}

function resultCast(handle, handle_voter)
{
    if (xmlhttp.readyState != 4)
    {
        // Waiting...
    }

    if (xmlhttp.readyState == 4 && xmlhttp.status == 201)
    {
        var voteOptionsElement = document.getElementById('vote-options');

        if (voteOptionsElement != null)
        {
            voteOptionsElement.innerHTML = "";
        }

        var confirmedElement = document.getElementById('confirmed');

        if (confirmedElement != null)
        {
            confirmedElement.setAttribute("style", "visibility: visible;");
        }
    }
}
