dependencies = {
	layers: [
		{
			name: "cw_dojo_build.js",
			dependencies: [
				"dijit.form.ValidationTextbox",
				"dijit.form.TextBox",
				"dijit.form.FilteringSelect",
				"dijit.form.RadioButton",
				"dojo.parser",
				"dijit.form.Button",
				"dijit.form.CheckBox",
				"dijit.Dialog",
				"dijit.layout.TabContainer",
				"dijit.layout.ContentPane",
				"dojox.regexp.emailAddress"
				]
		}
	],
	prefixes: [
		["dijit", "../dijit"],
		["dojox", "../dojox"]
	]
};