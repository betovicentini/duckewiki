//v.3.0 build 110713

/*
Copyright DHTMLX LTD. http://www.dhtmlx.com
To use this component please contact sales@dhtmlx.com to obtain license
*/

	/**
	*     @desc: set function called after row updated
	*     @param: func - event handling function (or its name)
	*     @type: deprecated
	*     @topic: 10
	*	  @event: onAfterUpdate
	*     @eventdesc: Event raised after row updated on server side
	*     @eventparam:  ID of clicked row
	*     @eventparam:  type of command
	*     @eventparam:  new Id, for _insert_ command
	*/
	dataProcessor.prototype.setOnAfterUpdate = function(ev){
		this.attachEvent("onAfterUpdate",ev);
	}
	
	/**
	* 	@desc: enable/disable debuging
	*	@param: mode - true/false
	*   @type: deprecated
	*/
	dataProcessor.prototype.enableDebug = function(mode){
	}
	
/**
*     @desc: set function called before server request sent ( can be used for including custom client server transport system)
*     @param: func - event handling function
*     @type: public
*     @topic: 0
*     @event: onBeforeUpdate
*     @eventdesc:  Event occured in moment before data sent to server
*     @eventparam: ID of item which need to be updated
*     @eventparam: type of operation
*     @eventreturns: false to block default sending routine
*/
	dataProcessor.prototype.setOnBeforeUpdateHandler=function(func){  
		this.attachEvent("onBeforeDataSending",func);
	};