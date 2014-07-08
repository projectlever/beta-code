var importIO = new (function(){
    var self             = this;
    var attempt          = 5;
    var attemptsMade     = 0;
    var waitTime         = 60000; // In milliseconds
    var connectionStatus = "";

    // Import.io specific information
    var apiKey      = "byd3h2MCJmxug2fwp5JFLXJrdrhSOye4GMGDIEJeYc1EgWEjPKFUQtL5CB7ZF7xO/zWNGwBep2CKd/Ra1zLPsQ==";
    var userGuid    = "c7893eff-9080-463b-9815-defb79810e04";
    
    // Public methods
    this.loadData       = function(url,source,downloadComplete,importCallComplete){
        console.log("Contacting " + url + " using scraper(s): ");
	console.log(source);
	if ( !downloadComplete )
	    console.log("[Warning] No downloadComplete callback specified.");
	if ( !importCallComplete )
	    console.log("[Warning] No importCallComplete callback specified.")
        
	var query = {
            "input" : {
                "webpage/url" : url
            },
            "connectorGuids" : source // This is an array of GUIDs
	};
	var callbacks = {
            "data" : downloadComplete,
	    "done" : importCallComplete
	};
	// Make the call to Import.IO
	importio.query(query, callbacks).fail(function(err){
	    console.log(err);
	})
        return;
    }
    function onstatuschange(status){
	var text = status.data.type;
	if ( connectionStatus == "SUBSCRIBED" ){
	    if ( text == "CONNECTION_BROKEN" || text == "CONNECTION_CLOSED" ){
		// Request failed
		attempt++;
		if ( attempt == maxAttempts ){
		    
		}
	    }
	}
    }
    function __load(){
	importio.addConnectionCallback(onstatuschange);
        // First, initialize the import.io object
        importio.init({
            "auth": {
                "userGuid": userGuid, 
                "apiKey": apiKey, // Also given by import.io => Constant str
            },
            "host": "import.io"
        });
	// Create the connection status element
	$("body").append("<div class='connection-status'></div>").append("<style type='text/css'> + 
                         .connection-status {position:absolute;top:0;left:0;background-color:#fff;}</style>");
        return self;
    }
    
    return __load();
})();
