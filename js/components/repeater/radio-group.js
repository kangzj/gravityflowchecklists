import React from 'react';
import ReactDOM from 'react-dom';
import RadioField from './radio';
import shortid from 'shortid';

export default class RadioGroupField extends React.Component {
	componentWillMount() {
		this.id = shortid.generate();
	}

	render() {
		const choices = this.props.choices;

		var radioGroup = choices.map((item, index) => {
			let id = this.id + this.props.settingName + index;
			return (<RadioField
				key={id}
				name={id}
				settingName={this.props.settingName}
				value={item.value}
				label={item.label}
				checked={this.props.value == item.value}
				onChange={this.context.setValue}
				horizontal={this.props.horizontal}
			/>)
		});

		return (<div className="gravityflow-setting">
				{radioGroup}
			</div>
		);
	}

}
RadioGroupField.contextTypes = {
	setValue: React.PropTypes.object.isRequired
}
