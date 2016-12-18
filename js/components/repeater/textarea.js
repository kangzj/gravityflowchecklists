import React from 'react';
import ReactDOM from 'react-dom';
import shortid from 'shortid';

export default class TextAreaField extends React.Component {
	constructor(props) {
		super(props);
		this._handleChange = this._handleChange.bind(this);
	}

	componentWillMount(){
		this.id = shortid.generate();
	}

	_handleChange(e) {

		var el = e.currentTarget;

		this.context.setValue(
			this.props.settingName,
			el.value
		)
	}

	render() {

		return (<div className="gravityflow-setting">
					<label htmlFor={this.id}>{this.props.label}</label>
					<textarea id={this.id} name={this.props.settingName} onChange={this._handleChange} value={this.props.value} />
				</div>
		);
	}

}

TextAreaField.contextTypes = {
	setValue: React.PropTypes.object.isRequired
};
