import React from 'react';
import ReactDOM from 'react-dom';
import shortid from 'shortid';

export default class TextField extends React.Component {
	constructor(props) {
		super(props);
		this._handleChange = this._handleChange.bind(this);
	}

	componentWillMount() {
		this.id = shortid.generate();
	}

	_handleChange(e) {

		var el = e.target;

		this.context.setValue(
			this.props.settingName,
			el.value
		)
	}

	render() {
		const size = this.props.size ? this.props.size : 'large';
		return (<div className="gravityflow-setting">
					<div>
						<label htmlFor={this.id}>{this.props.label}</label>
					</div>
					<input
						className={ 'gaddon-setting gaddon-text ' + size }
						id={this.id}
						type="text"
						name={this.props.settingName}
					   value={this.props.value}
					   onChange={this._handleChange} />
				</div>
			);
	}

}

TextField.contextTypes = {
	setValue: React.PropTypes.object.isRequired
};
