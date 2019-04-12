// This is the function which return the formatted text
// It get the string from the changelog html DOM
function formattedOutput(node)
{
    var result = '';
    
    var headerNode = node.firstChild; 
    var subHeaderNode = node.firstChild.nextSibling.childNodes[0];
    var bodyNode = node.firstChild.nextSibling.childNodes[1];

    var title = headerNode.innerText;
    var released = subHeaderNode.firstChild.innerText;
    var header = title + " (" + released + ")";

    result += header;
    result += "\n" + "=".repeat(header.length) + "\n\n";
    if(bodyNode.firstChild.className == "alert alert-warning")
    {
        // Issue Description
        result += bodyNode.firstChild.innerText + "\n\n";
    }
    
    for (var i = 0; i < bodyNode.childNodes.length; i++)
    {
        // Iterate over issues
        if(bodyNode.childNodes[i].localName == "i")
        {
            result += "- " + bodyNode.childNodes[i].nextElementSibling.innerText;
            result += " " + bodyNode.childNodes[i].nextElementSibling.nextElementSibling.innerText;
            result += " " + bodyNode.childNodes[i].nextElementSibling.nextElementSibling.nextSibling.textContent.slice(0, -2);
            result += " (" + bodyNode.childNodes[1].nextElementSibling.nextElementSibling.nextElementSibling.innerText + ")";
            result += " - " + bodyNode.childNodes[i].title + "\n";
        }
    }
    return result;
}

// Register clipboard trigger to elements with class 'clp'
// Call 'formattedOutput' to get text for clipboard
var clipboard = new ClipboardJS('.clp', {
        text: function(trigger) {
            var copiedNode = trigger.parentNode.parentNode.parentNode.parentNode;
            return formattedOutput(copiedNode);
            
        } 
    });
// On clipboard success => show tooltip
clipboard.on('success',function(e)
{
    e.clearSelection();
    $(e.trigger).tooltip({title: $(e.trigger).attr('data-tooltip-success'), trigger: "manual"}).tooltip('show');
    setTimeout( function () { $(e.trigger).tooltip('hide') }, 2000);
});
// On clipboard failed => show tooltip
clipboard.on('error',function(e)
{
    e.clearSelection();
    $(e.trigger).tooltip({title: $(e.trigger).attr('data-tooltip-failed'), trigger: "manual"}).tooltip('show');
    setTimeout( function () { $(e.trigger).tooltip('hide') }, 2000);
});