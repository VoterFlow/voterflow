/* Copyright (C) 2012-2018 Stephan Kreutzer
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

var data = {
    casts: null,
    voters: null,
    options: null
};

function loadData(handle)
{
    requestCasts(handle);
}

function requestCasts(handle)
{
    if (xmlhttp == null)
    {
        return;
    }

    {
        xmlhttp.open('GET', basePath + "/../voterflow/api/casts.php?handle=" + handle, true);
        xmlhttp.setRequestHeader('Accept',
                                 'application/xml');
        xmlhttp.onreadystatechange = function() { resultCasts(handle); };
        xmlhttp.send();
    }
}

function resultCasts(handle)
{
    var result = [];

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
        var casts = dom.getElementsByTagName('cast');

        for (var i = 0; i < casts.length; i++)
        {
            var cast = casts.item(i);

            var voter = cast.getElementsByTagName("handle-voter").item(0).textContent;
            var submitted = cast.getElementsByTagName("datetime-submitted").item(0).textContent;
            var option = cast.getElementsByTagName("id-vote-option").item(0).textContent;

            result.push({ "voter": voter, "submitted": submitted, "option": option });
        }

        data.casts = result;
        requestVoters(handle);
    }
}

function requestVoters(handle)
{
    if (xmlhttp == null)
    {
        return null;
    }

    {
        xmlhttp.open('GET', basePath + "/../voterflow/api/voters.php?handle=" + handle, true);
        xmlhttp.setRequestHeader('Accept',
                                 'application/xml');
        xmlhttp.onreadystatechange = function() { resultVoters(handle); };
        xmlhttp.send();
    }
}

function resultVoters(handle)
{
    var result = new Map();

    if (xmlhttp.readyState != 4)
    {
        // Waiting...
    }

    if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
    {
        if (xmlhttp.responseText == '')
        {
            return null;
        }

        var dom = xmlhttp.responseXML.documentElement;
        var voters = dom.getElementsByTagName('voter');

        for (var i = 0; i < voters.length; i++)
        {
            var voter = voters.item(i);

            var name = voter.getElementsByTagName("name").item(0).textContent;
            var handleVoter = voter.getElementsByTagName("handle").item(0).textContent;

            result.set(handleVoter, name);
        }

        data.voters = result;
        requestOptions(handle);
    }
}

function requestOptions(handle)
{
    if (xmlhttp == null)
    {
        return null;
    }

    {
        xmlhttp.open('GET', basePath + "/../voterflow/api/vote_options.php?handle=" + handle, true);
        xmlhttp.setRequestHeader('Accept',
                                 'application/xml');
        xmlhttp.onreadystatechange = function() { resultOptions(handle); };
        xmlhttp.send();
    }
}

function resultOptions(handle)
{
    var result = new Map();

    if (xmlhttp.readyState != 4)
    {
        // Waiting...
    }

    if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
    {
        if (xmlhttp.responseText == '')
        {
            return null;
        }

        var dom = xmlhttp.responseXML.documentElement;
        var options = dom.getElementsByTagName('option');

        for (var i = 0; i < options.length; i++)
        {
            var option = options.item(i);

            var id = option.getElementsByTagName("id").item(0).textContent;
            var caption = option.getElementsByTagName("caption").item(0).textContent;

            result.set(id, caption);
        }

        data.options = result;
        applyResult();
    }
}

function applyResult()
{
    if (data.casts == null ||
        data.voters == null ||
        data.options == null)
    {
        return;
    }
  
    for (var i = 0; i < data.casts.length; i++)
    {
        var cast = data.casts[i];

        document.getElementById('vote-casts').innerHTML += "<div>" + data.voters.get(cast.voter) + ", " + cast.submitted + ", " + data.options.get(cast.option) + "</div>";
    }
    
    data.casts = null;
    data.voters = null;
    data.options = null;
}