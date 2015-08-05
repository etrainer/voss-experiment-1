var user = 'Erik';
var pageArray = new Array();
var startTime = new Date();
var firstLoad = true;
var lastOpenedPage = '';
var currSearchPage = 1;

var etherpadKey = '60b260425bf73409b2cb957e58d1073de5d6d8208b59ad478d62d6ed7ecbc092';
var padID = 'test';
var numEtherpadChanges = 0;
var historyArray = new Array();

function generateUUID() {
    var d = new Date().getTime();
    var uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
        var r = (d + Math.random()*16)%16 | 0;
        d = Math.floor(d/16);
        return (c=='x' ? r: (r&0x3|0x8)).toString(16);
    });
    padID = uuid;
    return uuid;
}
function getWorkerID() {
    var q_string = window.location.search.substring(1);
    var workerID = "";
    var paramPair = q_string.split('=');
    if (paramPair[0] == 'mturkworkerID') {
        if (paramPair[1] != '') {
            workerID = paramPair[1];
        }
    }
    return workerID;
 }

function getCondition() {
    return 2;

}

function createEtherpadID() {
    $.ajax({
        url: 'http://localhost:9001/api/1.2.7/createPad?',
        type: 'GET',
        dataType: 'jsonp',
        data: 'apikey=' + etherpadKey + '&padID=' + getWorkerID() + '&jsonp=?',
        success: function(data) {
            document.getElementById('etherpad-lite').src = 'http://localhost:9001/p/' + padID + '?showControls=true&showChat=false&alwaysShowChat=false&&showLineNumbers=true&useMonospaceFont=false';
        }
    });


}


function HistoryItem(time, description) {
    this.eventTime = time;
    this.description = description;

    this.getTime = function() {
        return this.time;
    }
    this.getDescription = function() {
        return this.description;
    }
}

/**
 * Cleans an Op object
 * @param {Op} object to be cleared
 */
function clearOp(op) {
    op.opcode = '';
    op.chars = 0;
    op.lines = 0;
    op.attribs = '';
}

/**
 * Creates a new Op object
 * @param optOpcode the type operation of the Op object
 */
function newOp(optOpcode) {
    return {
        opcode: (optOpcode || ''),
        chars: 0,
        lines: 0,
        attribs: ''
    };
}

/**
 * this function creates an iterator which decodes string changeset operations
 * @param opsStr {string} String encoding of the change operations to be performed 
 * @param optStartIndex {int} from where in the string should the iterator start 
 * @return {Op} type object iterator 
 */
function opIterator(opsStr, optStartIndex) {
  //print(opsStr);
    var regex = /((?:\*[0-9a-z]+)*)(?:\|([0-9a-z]+))?([-+=])([0-9a-z]+)|\?|/g;
    var startIndex = (optStartIndex || 0);
    var curIndex = startIndex;
    var prevIndex = curIndex;

    function nextRegexMatch() {
        prevIndex = curIndex;
        var result;
        regex.lastIndex = curIndex;
        result = regex.exec(opsStr);
        curIndex = regex.lastIndex;
        if (result[0] == '?') {
        console.error("Hit error opcode in op stream");
        }
        return result;
    }

    var regexResult = nextRegexMatch();
    var obj = newOp();

    function next(optObj) {
        var op = (optObj || obj);
        if (regexResult[0]) {
            op.attribs = regexResult[1];
            op.lines = parseNum(regexResult[2] || 0);
            op.opcode = regexResult[3];
            op.chars = parseNum(regexResult[4]);
            regexResult = nextRegexMatch();
        } else {
            clearOp(op);
        }
        return op;
    }

    function hasNext() {
        return !!(regexResult[0]);
    }

    function lastIndex() {
        return prevIndex;
    }
    return {
        next: next,
        hasNext: hasNext,
        lastIndex: lastIndex
    };
}

/**
 *  Returns a base 10 number from a base 36 string.
 *  @param {string} str = A base 36 string.
 */
function parseNum(str) {
	return parseInt(str, 36);
}

/**
 *  Unpacks a string encoded Changeset into a Changeset object
 *  @param {string} cs - A string encoded changeset.
 */
function unpack(cs) {
    var headerRegex = /Z:([0-9a-z]+)([><])([0-9a-z]+)|/;
    var headerMatch = headerRegex.exec(cs);
    if ((!headerMatch) || (!headerMatch[0])) {
        console.error("Not a exports: " + cs);
    }
    var oldLen = parseNum(headerMatch[1]);
    var changeSign = (headerMatch[2] == '>') ? 1 : -1;
    var changeMag = parseNum(headerMatch[3]);
    var newLen = oldLen + changeSign * changeMag;
    var opsStart = headerMatch[0].length;
    var opsEnd = cs.indexOf("$");
    if (opsEnd < 0) opsEnd = cs.length;
    return {
        oldLen: oldLen,
        newLen: newLen,
        ops: cs.substring(opsStart, opsEnd),
        charBank: cs.substring(opsEnd + 1)
    };
};

function getInitialRevisions() {
    $.ajax({
        url: 'http://localhost:9001/api/1.2.7/getRevisionsCount?',
        type: 'GET',
        dataType: 'jsonp',
        data: 'apikey=' + etherpadKey + '&padID=' + padID + '&jsonp=?',
        success: function(data) {
            numEtherpadChanges = data.data.revisions;
            console.log('There are ' + numEtherpadChanges + ' initial revisions.');
        }
    });
}

function checkForNewRevisions() {
    $.ajax({
        url: 'http://localhost:9001/api/1.2.7/getRevisionsCount?',
        type: 'GET',
        dataType: 'jsonp',
        data: 'apikey=' + etherpadKey + '&padID=' + padID + '&jsonp=?',
        success: function(data) {
            if (numEtherpadChanges == data.data.revisions)
                return;
            else if (data.data.revisions > numEtherpadChanges) {
                console.log('There has been a new revision.');
                for (var i = numEtherpadChanges + 1; i <= data.data.revisions; i++) {
                    $.ajax({
                        url: 'http://localhost:9001/api/1.2.8/getRevisionChangeset?',
                        type: 'GET',
                        dataType: 'jsonp',
                        data: 'apikey=' + etherpadKey + '&padID=' + padID + '&rev=' + i + '&jsonp=?',
                        context: {rev: i},
                        success: function(data) {
                            processChangeSets(data, this.rev);
                        }
                    });
                 }
                numEtherpadChanges = data.data.revisions;
            }
        }
    });
}   

function processChangeSets(data, rev) {
    console.log('Revision ' + rev + ': ');
    var unpacked = unpack(data.data);
    //console.log(unpacked);
    var opIt = opIterator(unpacked.ops);    
    var totalAddedChars = 0;
    var totalRemovedChars = 0;
    var totalAddedLines = 0;
    var totalRemovedLines = 0;
    var addedText = "";
    var removedText = "";
    while (opIt.hasNext()) {
        var opItem = opIt.next();
        switch (opItem.opcode) {
            case '=':
               // console.log('Changed attributes of text');
                break;
            case '+':
                totalAddedChars += opItem.chars;
                totalAddedLines += opItem.lines;
               // console.log('Added ' + '\"' + unpacked.charBank + '\"' + ' (' + opItem.chars + ' chars and ' + opItem.lines + ' lines)');
                break;
            case '-':
                totalRemovedChars += opItem.chars;
                totalRemovedLines += opItem.lines;
               // console.log('Removed ' + '\"' + unpacked.charBank + '\"' + ' (' + opItem.chars + ' chars and ' + opItem.lines + ' lines)');
                break;
        }
    }
    var historyText = "";
    if (totalAddedLines > 0 && totalAddedChars > 0) {
        historyText += "<strong>Added</strong> " + totalAddedLines + " lines (" + totalAddedChars + " chars)";
    }
    else if (totalAddedLines == 0 && totalAddedChars > 0) {
        historyText += "<strong>Added</strong> " + totalAddedChars + " chars";
    }
    if (totalRemovedLines > 0 && totalRemovedChars > 0) {
        if (historyText !="")
            historyText += "<br/>";
        historyText += "<strong>Removed</strong> " + totalRemovedLines + " lines (" + totalRemovedChars + " chars)";
    }
    else if (totalRemovedLines == 0 && totalRemovedChars > 0) {
        if (historyText !="")
            historyText += "<br/>";
        historyText += "<strong>Removed</strong> " + totalRemovedChars + " chars";
    }    
    historyText +="<br/>At version " + rev;
   // console.log(historyText); 
//    $('#history ol').append('<li class="historyItem">' + historyText + '</li>');
//    var historyItem = new HistoryItem(new Date(), $(historyText).text());
//    historyArray.push(historyItem);
   // console.log('---------------------------------------');
}


/**
 *   Renders the Google Search Results on our webpage using Google's Search API
 *   response object. Also sets up click behavior for links to the pages returned.
 *   @param {object} searchResults - The JSON response object.
 */
function displayResults(searchResults) {
    //Clear the previously displayed results
    $('#results').empty();
    
    //Loop through the search results and render each
    for (var i = 0; i < searchResults.items.length; i++) {
        var item = searchResults.items[i];
        var displayedResult = $('<p>');
        var displayedResultLink = $('<a href="#">' + item.htmlTitle + '</a>');
        (function outer(item) {
            displayedResultLink.click(function(event) {
                event.preventDefault();
                console.log("Clicked link: " + item.link);
                $('#history ol').append('<li class="historyItem"><strong>Navigated</strong> to: <span>\"' + item.link + '\"</span></li>');
                var historyItem = new HistoryItem(new Date(), 'Navigated to ' + '\"' + item.link + '\"');
                historyArray.push(historyItem);
                /*  We were just on the search page.
                 *  Record the current time, calculate the time spent on the last page, add the search page
                 *  to the history, and restart the start date.
                 */
                lastVisitedSite = new VisitedPage("Search Page");
                lastVisitedSite.setTimeSpent(new Date() - startTime);
                pageArray.push(lastVisitedSite);
                startTime = new Date();
                //window.open(item.link, '_new');
                lastOpenedPage = item.link;
                firstLoad = false;


                var form = document.createElement("form");
                form.setAttribute("method", "post");
                form.setAttribute("action", "result.php");

                form.setAttribute("target", "viewer");

                var hiddenField = document.createElement("input");
                hiddenField.setAttribute("type", "hidden");
                hiddenField.setAttribute("name", "url");
                hiddenField.setAttribute("value", item.link);
                form.appendChild(hiddenField);
                document.body.appendChild(form);

                window.open('', 'viewer');

                form.submit();
    

            });
        })(item);
        //The green text link and page desciption below the clickable search result
        var displayedResultText = '<br/>' + '<span style="color: green">' + item.link + '</span><br/>' + item.htmlSnippet;
        
        //Append the result link, text, and closing paragraph tag
        displayedResult.append(displayedResultLink);
        displayedResult.append(displayedResultText);
        displayedResult.append('</p>');

        if (currSearchPage > 1)
            $('#back').show();
        else
            $('#back').hide();

        if (currSearchPage < 10)
            $('#forward').show();
        else
            $('#forward').hide();

        //Add everything to the results div
        $('#results').append(displayedResult);
    }
}

/**
 *  Sets up the BACK button to display the previous 10 results of the last executed 
 *  search when clicked.
 *  @param {event} event - The click event.
 */
function setUpBackLink(event) {
    if (currSearchPage > 1) {
        var newStartResult = ((currSearchPage - 1) * 10)-9;
        console.log("Going backward 10 results for " + event.data.searchQuery + ". New starting search result is: " + newStartResult);
        $.ajax({
            url: 'https://www.googleapis.com/customsearch/v1?',
            type: 'GET',
            data: 'key=AIzaSyAiMArje8ddyaoOFGsM5zVkn_3e2MtvUOo&cx=012722058526817753006:rly8elip0ya&q=' + event.data.searchQuery + '&start=' + newStartResult,
            success: function(data) {
                //Decrement the current page marker
                currSearchPage -= 1;
                displayResults(data);
            }
        });
    }
    else {
        alert('You are already on the first page');
    }
}

/* 
 *  Sets up the FORWARD button to display the next
 *  10 results of the last executed search when clicked.
 *  @param {event} event - The click event.
 */
function setUpForwardLink(event) {
    if (currSearchPage < 10) {
        var newStartResult = (currSearchPage * 10) + 1;
        console.log("Going forward 10 results for " + event.data.searchQuery + ". New starting search result is: " + newStartResult);
        $.ajax( {
            url: 'https://www.googleapis.com/customsearch/v1?',
            type: 'GET',
            data: 'key=AIzaSyAiMArje8ddyaoOFGsM5zVkn_3e2MtvUOo&cx=012722058526817753006:rly8elip0ya&q=' + event.data.searchQuery + '&start=' + newStartResult,
            success: function(data) {
                //Increment the current page marker
                currSearchPage += 1;
                displayResults(data);
            }
        });
    }
    else {
        alert('You are already on the last page');
    }
}

$(document).ready(function() {
    if (getWorkerID() =='') {
        document.write("Invalid worker ID detected! Please return to the HIT page and start again.");
        return;
    }
    createEtherpadID();
    window.lastFocusStatus = document.hasFocus();
    check();
    setInterval(check, 200);
    getInitialRevisions();
    setInterval(checkForNewRevisions, 10000);
    //Set up the html for the forward and backward buttons
    var goBackLink = $('<a href="#">&laquo; previous 10</a>');
    var goForwardLink = $('<a href="#">next 10 &raquo;</a>');

    $('#back').append(goBackLink);
    $('#forward').append(goForwardLink);

    $('#back').hide();
    $('#forward').hide();

    //Execute a google search using what has been entered in the input box
    $('.status-box').keydown(function(e) {
        if (e.which == 13 || e.keyCode == 13) {
	        e.preventDefault();
            var query = $('.status-box').val();
            $.ajax({
                url: 'https://www.googleapis.com/customsearch/v1?',
                type: 'GET',
                context: {sq: query},
                data: 'key=AIzaSyAiMArje8ddyaoOFGsM5zVkn_3e2MtvUOo&cx=012722058526817753006:rly8elip0ya&q=' + query,
                success: function(data) {
                    //Set the page marker to the first page
                    currSearchPage = 1;
               

                    //Remove any previous click handlers
                    goBackLink.off('click');
                    goForwardLink.off('click');
 
                    //Create link behavior for the backward and forward buttons
                    goBackLink.click({searchQuery: this.sq}, setUpBackLink);
                    goForwardLink.click({searchQuery: this.sq}, setUpForwardLink);
               
                    //show the forward button
                    //$('#back').show();
                    $('#forward').show();
 
                    //Add the executed search to the history div
                    $('#history ol').append("<li class='historyItem'><strong>Searched</strong> for: " + "<span>\"" + query + "\"" +  "</span></li>");

                    var historyItem = new HistoryItem(new Date(), 'Searched for ' + '\"' + query + '\"');
                    historyArray.push(historyItem);              
 
                    //Display the first 10 results
                    displayResults(data);
                }
            });
        }     
    });

    $('#submit').click(function() {
        saveHistory();
    });
});

function VisitedPage(pageURL) {
    this.pageURL = pageURL;
    //time in milliseconds
    this.timeSpent = 0;
    this.setTimeSpent = function(time) {
        this.timeSpent = time;
    };
}

function check() {
    if (document.hasFocus() == lastFocusStatus) {
        return;
    }
    //It didn't have focus before
    if (document.hasFocus() && firstLoad == false) {
        var historyItem = new HistoryItem(new Date(), "Got focus");
        historyArray.push(historyItem);
    }
    else if (!document.hasFocus() && firstLoad == false) {
        var historyItem = new HistoryItem(new Date(), "Lost focus");
        historyArray.push(historyItem);
    }
    lastFocusStatus = !lastFocusStatus;
}

function reviewHistory() {
    var form = document.createElement("form");
    form.setAttribute("method", "post");
    form.setAttribute("action", "review.php");

    form.setAttribute("target", "view");

    var hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "history");
    hiddenField.setAttribute("value", JSON.stringify(historyArray));
    form.appendChild(hiddenField);
    document.body.appendChild(form);

    window.open('', 'view');

    form.submit();


}

function saveHistory() {
    var answer = confirm('Are you finished with your descriptions?\n\nOnly click \"OK\" when you are done with your descriptions. Click \"Cancel\" to keep working.');
    if (answer) {
        $.ajax({
            url: 'http://localhost:9001/api/1.2.8/getHTML?',
            type: 'GET',
            dataType: 'jsonp',
            data: 'apikey=' + etherpadKey + '&padID=' + padID + '&jsonp=?',
            success: function(data) {
            
                var form = document.createElement("form");
                form.setAttribute("method", "post");
                form.setAttribute("action", "submit.php");
    
                //form.setAttribute("target", "submit");

                var hiddenField = document.createElement("input");
                hiddenField.setAttribute("type", "hidden");
                hiddenField.setAttribute("name", "history");
                hiddenField.setAttribute("value", JSON.stringify(historyArray));
                form.appendChild(hiddenField);

                var hiddenFieldID = document.createElement("input");
                hiddenFieldID.setAttribute("type", "hidden");
                hiddenFieldID.setAttribute("name", "ID");
                hiddenFieldID.setAttribute("value", getWorkerID());
                form.appendChild(hiddenFieldID);

                var hiddenFieldCondition = document.createElement("input");
                hiddenFieldCondition.setAttribute("type", "hidden");
                hiddenFieldCondition.setAttribute("name", "condition");
                hiddenFieldCondition.setAttribute("value", getCondition());
                form.appendChild(hiddenFieldCondition);

                var hiddenFieldContent = document.createElement("input");
                hiddenFieldContent.setAttribute("type", "hidden");
                hiddenFieldContent.setAttribute("name", "description");
                hiddenFieldContent.setAttribute("value", data.data.html);
                form.appendChild(hiddenFieldContent);

                document.body.appendChild(form);
                //window.open('', 'submit');
    
                form.submit();
            }
        });
    }
}
