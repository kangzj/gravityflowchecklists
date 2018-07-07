import React from 'react';
import ReactDOM from 'react-dom';
import shortid from 'shortid';

import { Repeater, NestedRepeater, RepeaterItem, RadioGroupField, CheckboxField, SelectField, TextField, TextAreaField } from 'gf-repeater';


class FormNodes extends RepeaterItem {

	constructor(props) {
		super(props);
		this.state = { isOpen : false };
		this._toggle = this._toggle.bind(this);
	}

	_toggle(){
		this.setState( { isOpen: ! this.state.isOpen });
	}

	render() {
		const strings = this.props.strings;

		var selectedFormHasWorkflow = false,
			formTitle = strings.newChecklistItem,
			nodeHeader,
			options = '',
			sequentialOptions = '';

		for (var i=0; i < this.props.forms.length; i++) {
			if (this.props.forms[i].value && this.props.forms[i].value == this.props.item.form_id) {
				formTitle = this.props.forms[i].label;
				if (this.props.forms[i].hasWorkflow) {
					selectedFormHasWorkflow = true;
				}
				break;
			}
		}

		nodeHeader = (<div className="gravityflow-node-header" onClick={this._toggle}>
			<div className="gravityflow-node-toggle-icon"><i
				className={ this.state.isOpen ? "fa fa-caret-down" : "fa fa-caret-right"}/></div>
			{formTitle}
		</div>);

		if ( this.props.checklistIsSequential && selectedFormHasWorkflow ) {
			sequentialOptions = (<CheckboxField settingName="waitForWorkflowComplete"
												 checked={this.props.item.waitForWorkflowComplete}
												 label={strings.waitForWorkflowComplete}/>)
		}


		if (this.state.isOpen) {
			options = (
				<div>
					<SelectField label={strings.form} settingName="form_id" value={this.props.item.form_id} choices={this.props.forms} />
					<CheckboxField settingName="linkToEntry"
								   checked={this.props.item.linkToEntry}
								   label={strings.linkToEntry}/>
					{sequentialOptions}
				</div>);
		}

		return (<div className="gravityflow-node-container">
				{nodeHeader}
				{options}
			</div>)
	}
}

class ChecklistSettings extends RepeaterItem {

	constructor(props) {
		super(props);
		this.state = { isOpen : false };
		this._toggle = this._toggle.bind(this);
	}

	_toggle(){
		this.setState( { isOpen: ! this.state.isOpen });
	}

	render(){
		const strings = this.props.strings;

		var checklistSettings;

		checklistSettings = (<div>
			<CheckboxField settingName="sequential" checked={this.props.item.sequential} label={strings.sequential}/>
			<NestedRepeater
				label={strings.forms}
				settingName="nodes"
				value={this.props.item.nodes}
				strings={strings}
				minItems={1}
				defaultValues={function(){
									return {
											id: shortid.generate(),
											form_id: '',
											custom_label: '',
											waitForWorkflowComplete: false,
											linkToEntry : true
											}
										}
									}
			>
				<FormNodes strings={strings} forms={strings.vars.forms} checklistIsSequential={this.props.item.sequential}/>
			</NestedRepeater>
		</div>)

		const permissionsRadioChoices = [
			{
				value: 'all',
				label: strings.allUsers
			}
			,
			{
				value: 'select',
				label: strings.selectUsers
			}
		];
		var settings = '';

		if ( this.state.isOpen ) {

			var selectUsers = '';

			if ( this.props.item.permissions == 'select' ) {
				selectUsers = <SelectField settingName="assignees" value={this.props.item.assignees} choices={this.props.strings.vars.userChoices} multiple={true}/>
			}

			settings = (<div className="gravityflow-checklist-settings">
				<TextField settingName="name" value={this.props.item.name} label={strings.checklistName} />
				{checklistSettings}<br />
				<RadioGroupField label={strings.permissions} settingName="permissions" value={this.props.item.permissions} choices={permissionsRadioChoices} horizontal={true} />
				{selectUsers}
			</div>);
		}

		return (<div className="gravityflow-checklist-settings-container" key={this.props.item.id}>
				<div className="gravityflow-settings-header" onClick={this._toggle}>
					<div className="gravityflow-toggle-icon"><i className={ this.state.isOpen ? "fa fa-caret-down" : "fa fa-caret-right"} /></div>
				{this.props.item.name}
				</div>
					{settings}
				</div>);
	}
}

jQuery(document).ready(function () {
	function _updateFieldJSON(name, items) {
		document.getElementById('checklists').value = JSON.stringify(items);
	}
	let strings = gravityflowchecklists_settings_js_strings;
	ReactDOM.render(
		<Repeater
			value={JSON.parse(document.getElementById('checklists').value)}
			defaultValues={function(){
				return {
					id: shortid.generate(),
					name: strings.defaultChecklistName,
					sequential: true,
					assignees: [],
					permissions: 'all',
					nodes:
						[
							{
								id: shortid.generate(),
								form_id: '',
								custom_label: '',
								waitForWorkflowComplete: false,
								linkToEntry : true
							}
						]
			}	}}
			onChange={_updateFieldJSON}
			strings={gravityflowchecklists_settings_js_strings}
		>
			<ChecklistSettings strings={gravityflowchecklists_settings_js_strings}/>
		</Repeater>,
		document.getElementById('gravityflowchecklists-checklists-settings-ui')
	);
});
