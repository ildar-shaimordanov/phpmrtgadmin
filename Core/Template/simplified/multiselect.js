function MultiSelect() { this.__construct(); }
MultiSelect.prototype = {

	group: [],
	selector: null,
	exclusiveSelector: null,
	exclusiveMode: false,

	/************************************************************/

	addGroupSelector: function (groupSelector, groupValues)
	{
		if ( ! groupSelector ) {
			return;
		}
		if ( ! groupValues ) {
			groupValues = [];
		}
		this.group[this.group.length] = {checkbox: groupSelector, values: groupValues};
	},

	addSelector: function (selector)
	{
		this.selector = selector;
	},

	addExclusiveSelector: function (exclusiveSelector)
	{
		this.exclusiveSelector = exclusiveSelector;
		this.exclusiveMode = exclusiveSelector.checked;
	},

	/************************************************************/

	changeGroup: function (clicker)
	{
		var clicker_checked = clicker.checked;
		if (this.exclusiveMode) {
			this._clearCheckboxes(clicker);
			this._clearSelect(clicker);
		}
		clicker.checked = clicker_checked;

		var found = this._search(clicker);
		if (found && found.values && found.values.length) {
			for (var i = 0; i < found.values.length; i++) {
				var j = found.values[i];
				this.selector.options[j].selected = clicker_checked;
			}
			return;
		}
	},

	changeItem: function (clicker)
	{
		this._clearCheckboxes(clicker);
	},

	changeMode: function (clicker)
	{
		if (clicker == this.exclusiveSelector) {
			this._clearCheckboxes(clicker);
			this._clearSelect(clicker);
		}
		this.exclusiveMode = this.exclusiveSelector.checked;
		//this.exclusiveMode = (clicker == this.exclusiveSelector);
	},

	/************************************************************/

	_clearCheckboxes: function (clicker)
	{
		for (var i = 0; i < this.group.length; i++) {
			this.group[i].checkbox.checked = false;
		}
	},

	_clearSelect: function (clicker)
	{
		for (var i = 0; i < this.selector.options.length; i++) {
			this.selector.options[i].selected = false;
		}
	},

	_clickedSelect: function (clicker)
	{
	},

	_search: function (checkbox)
	{
		for (var i = 0; i < this.group.length; i++) {
			if (checkbox == this.group[i].checkbox) {
				return this.group[i];
			}
		}
		return false;
	},

	/************************************************************/

	__construct: function () { ; }

}

