var importer = new (function(){
    var self         = this;
    var errors       = [];
    var index        = 0;
    var links        = [];
    var linkIndex    = 0;
    var importObject = [];
    /*[string]*/function getDataFromCell(/*[object jQuery]*/ td){
	var temp = null;
	if ( (temp = td.find("softmerge-inner")).length > 0 )
	    return temp.text();
	else
	    return td.text();
    }
    /*[string]*/function getCrawlSource(/*[string]*/ url){
	return url.substring(url.search("source=")).replace("source=","");
    }
    /*[bool]*/function validate(/*[object DataRow]*/ data){
	if ( data.Import_Link == "" ){
	    errors.push("Failed to import data: No Import link was specified at row " + data.Row + " for " + data.University);
	    return false;
	}
	if ( data.Direct_Link == "" ){
	    errors.push("Failed to import data: Extractor link and Import link are provided but the page to crawl was not specified at row " + data.Row + " for " + data.University);
	    return false;
	}
	return true;
    }
    /*[object null]*/function getDataFromSources(){
	if ( index < data.length ){
	    if ( validate(data[index]) === true ){
		if ( data[index].Extractor_Link != "" ){
		    console.log("Getting data from extractor:");
		    console.log(data[index].Extractor_Link);
		    getDataFromExtractor();
		    return;
		}
	    }
	}
    }
    /*[object null]*/ function getDataFromExtractor(){
	// We need to get the links to the web pages that will be crawled
	importIO.loadData(data[index].Direct_Link,[data[index].Extractor_Link],gotLinkFromExtractor,crawlLinks);
    }
    /*[object null]*/ function gotLinkFromExtractor(/*[object ImportIOResponse]*/ data){
	console.log("DATA RECEIVED");
	if ( links.length == 0 )
	    links = data;
	else
	    links = links.concat(data);
	return;
    }
    /*[object null]*/ function crawlLinks(data){
	// Now we are going to feed the links into the crawler to get the data
	if ( links[linkIndex] != null ){
	    console.log("Crawling link: " + links[linkIndex]);
	    importIO.loadData(links[linkIndex],[data[index].Import_Link],gotDataFromCrawler,exit);
	}
	else {
	    console.log("Done crawling");
	}
    }
    /*[object null]*/ function gotDataFromCrawler(/*[array [object ImportIOResponse]]*/ response){
	console.log(response);
    }
    /*[object null]*/ function exit(){
	alert("done");
	return;
    }
    /*[object null]*/ function prepareExtraction(){
	data = [];
	// Get all of the Import.IO links in the page along with the current page number
	var rows = $(".waffle tr");
	
	// Loop through each row and grab the necessary information...skip the first row
	for ( var i = 2, n = rows.length; i < n; i++ ){
	    // Grab the cells
	    var td = $(rows.get(i)).find("td");
	    // Now grab the information
	    data.push({
		University : getDataFromCell($(td[0])),
		School : getDataFromCell($(td[1])),
		Department : getDataFromCell($(td[2])),
		Resource_Type : getDataFromCell($(td[3])),
		Direct_Link : getDataFromCell($(td[4])),
		Import_Link : getCrawlSource(getDataFromCell($(td[5]))),
		Extractor_Link : getCrawlSource(getDataFromCell($(td[6]))),
		Row : i
	    });
	}
	getDataFromSources(data);
	return;
    }
    $(document).ready(prepareExtraction);
    return self;
})();
