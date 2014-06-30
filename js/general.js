/***
 * This file contains "boilerplate" code or code that can be reused in many applications.
 ***/
var toType = function(obj) {
    return ({}).toString.call(obj).match(/\s([a-zA-Z]+)/)[1].toLowerCase()
}